<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use GeoIp2\Database\Reader;

class WC_Geolocation_Based_Products_Geolocate {

	/** URL to the geolocation database we're using */
	const GEOLITE2_CITY_DB = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz';

	/** @var array API endpoints for looking up user IP address */
	private $ip_lookup_apis = array(
		'icanhazip'         => 'http://icanhazip.com',
		'ipify'             => 'http://api.ipify.org/',
		'ipecho'            => 'http://ipecho.net/plain',
		'ident'             => 'http://ident.me',
		'whatismyipaddress' => 'http://bot.whatismyipaddress.com',
		'ip.appspot'        => 'http://ip.appspot.com'
	);

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.4.0
	 * @version 1.4.0
	 * @return bool
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_weekly_cron_schedule' ) );
		add_action( 'wc_glbp_db_update', array( $this, 'update_database' ) );

		if ( ! wp_next_scheduled( 'wc_glbp_db_update' ) ) {
			wp_schedule_event( time(), 'weekly', 'wc_glbp_db_update' );
		}

		if ( ! file_exists( self::get_local_city_database_path() ) ) {
			$this->update_database();
		}

		return true;
	}

	/**
	 * Adds custom weekly schedule to cron.
	 *
	 * @access public
	 * @since 1.4.0
	 * @version 1.4.0
	 * @param array $schedules
	 * @return array $schedules
	 */
	public function add_weekly_cron_schedule( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once Weekly', 'woocommerce-geolocation-based-products' ),
		);

		return $schedules;
	}

	/**
	 * Get current user IP Address.
	 * @return string
	 */
	public function get_ip_address() {
		if ( isset( $_SERVER['X-Real-IP'] ) ) {
			return $_SERVER['X-Real-IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
			// Make sure we always only send through the first IP in the list which should always be the client IP.
			return trim( current( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return $_SERVER['REMOTE_ADDR'];
		}
		return '';
	}

	/**
	 * Get user IP Address using a service.
	 * @return string
	 */
	public function get_external_ip_address() {
		$transient_name      = 'external_ip_address_' . $this->get_ip_address();
		$external_ip_address = get_transient( $transient_name );

		if ( false === $external_ip_address ) {
			$external_ip_address     = '0.0.0.0';
			$ip_lookup_services      = apply_filters( 'woocommerce_geolocation_ip_lookup_apis', $this->ip_lookup_apis );
			$ip_lookup_services_keys = array_keys( $ip_lookup_services );
			shuffle( $ip_lookup_services_keys );

			foreach ( $ip_lookup_services_keys as $service_name ) {
				$service_endpoint = $ip_lookup_services[ $service_name ];
				$response         = wp_safe_remote_get( $service_endpoint, array( 'timeout' => 2 ) );

				if ( ! is_wp_error( $response ) && $response['body'] ) {
					$external_ip_address = apply_filters( 'woocommerce_geolocation_ip_lookup_api_response', wc_clean( $response['body'] ), $service_name );
					break;
				}
			}

			set_transient( $transient_name, $external_ip_address, WEEK_IN_SECONDS );
		}

		return $external_ip_address;
	}

	/**
	 * Geolocate an IP address.
	 * @param  string $ip_address
	 * @param  bool   $fallback
	 * @return array
	 */
	public function geolocate_ip( $ip_address = '', $fallback = true ) {
		$ip_address  = $ip_address ? $ip_address : self::get_ip_address();
		$city_reader = new Reader( self::get_local_city_database_path() );
		
		if ( ( '::1' === $ip_address || '127:0:0:1' === $ip_address ) && $fallback ) {
			// May be a local environment - find external IP
			return $this->geolocate_ip( $this->get_external_ip_address(), false );
		}

		$city_record = $city_reader->city( $ip_address );
		
		$country     = sanitize_text_field( strtoupper( $city_record->country->isoCode ) );
		$city        = sanitize_text_field( strtoupper( $city_record->city->name ) );
		$region      = sanitize_text_field( strtoupper( $city_record->mostSpecificSubdivision->isoCode) );

		return array( 'country_code' => $country, 'city' => $city, 'region_code' => $region );
	}

	/**
	 * Path to our local db.
	 * @return string
	 */
	private function get_local_city_database_path() {
		$upload_dir = wp_upload_dir();

		return $upload_dir['basedir'] . '/geoip_city.dat';
	}

	/**
	 * Update geoip database. Adapted from https://wordpress.org/plugins/geoip-detect/.
	 */
	public function update_database() {
		$logger = new WC_Logger();

		if ( ! is_callable( 'gzopen' ) ) {
			$logger->add( 'wc_geolocation_based_products', 'Server does not support gzopen' );
			return;
		}

		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$tmp_databases = array(
			'city' => download_url( self::GEOLITE2_CITY_DB )
		);

		foreach ( $tmp_databases as $tmp_database_path ) {
			if ( ! is_wp_error( $tmp_database_path ) ) {
				$gzhandle = @gzopen( $tmp_database_path, 'r' );

				$handle = @fopen( self::get_local_city_database_path(), 'w' );

				if ( $gzhandle && $handle ) {
					while ( $string = gzread( $gzhandle, 4096 ) ) {
						fwrite( $handle, $string, strlen( $string ) );
					}
					gzclose( $gzhandle );
					fclose( $handle );
				} else {
					$logger->add( 'wc_geolocation_based_products', 'Unable to open database file' );
				}
				@unlink( $tmp_database_path );
			} else {
				$logger->add( 'wc_geolocation_based_products', 'Unable to download GeoIP Database: ' . $tmp_database_path->get_error_message() );
			}
		}
	}
}
