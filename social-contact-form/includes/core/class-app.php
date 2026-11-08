<?php
/**
 * App class.
 *
 * @package FormyChat
 * @since 1.0.0
 */
// Namespace .
namespace FormyChat;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // phpcs:ignore Universal.PHP.RequireExitDieParentheses.Missing

if ( ! class_exists( __NAMESPACE__ . '\App' ) ) {
	/**
	 * App class.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class App {

		/**
		 * Current widget config schema version.
		 *
		 * 1 (or missing) = WhatsApp-only config. 2 = channels (WhatsApp, Telegram).
		 *
		 * @since 2.16.0
		 */
		const CONFIG_VERSION = 2;

		/**
		 * Channels a widget can use.
		 *
		 * @since 2.16.0
		 */
		const CHANNELS = [ 'whatsapp', 'telegram' ];

		/**
		 * Channel list styles a widget may save. FormyChat shows `stack`;
		 * other styles are shown by code that registers them, otherwise `stack` shows.
		 *
		 * @since 2.16.0
		 */
		const LIST_STYLES = [ 'stack', 'card', 'row', 'chat', 'grid', 'bubble', 'menu', 'corner' ];

		/**
		 * Launcher color saved by default before each launcher icon had its own color.
		 * A saved launcher with this color shows the icon's own color.
		 *
		 * @since 2.16.0
		 */
		const LEGACY_LAUNCHER_COLOR = '#4F46E5';

		/**
		 * Returns the default fonts.
		 *
		 * @return array
		 */
		public static function fonts() {
			$fonts = [
				'sans-serif'       => __( 'Default', 'social-contact-form' ),
				'Arial'            => __( 'Arial', 'social-contact-form' ),
				'Arial Black'      => __( 'Arial Black', 'social-contact-form' ),
				'Comic Sans'       => __( 'Comic Sans MS', 'social-contact-form' ),
				'Courier New'      => __( 'Courier New', 'social-contact-form' ),
				'Georgia'          => __( 'Georgia', 'social-contact-form' ),
				'Lucida Console'   => __( 'Lucida Console', 'social-contact-form' ),
				'Lucida Sans'      => __( 'Lucida Sans', 'social-contact-form' ),
				'Tahoma'           => __( 'Tahoma', 'social-contact-form' ),
				'Times New Roman'  => __( 'Times New Roman', 'social-contact-form' ),
				'Trebuchet'        => __( 'Trebuchet', 'social-contact-form' ),
				'Verdana'          => __( 'Verdana', 'social-contact-form' ),
				'Ubuntu'           => __( 'Ubuntu', 'social-contact-form' ),

				'Roboto'           => __( 'Roboto', 'social-contact-form' ),
				'Roboto Condensed' => __( 'Roboto Condensed', 'social-contact-form' ),
				'Open Sans'        => __( 'Open Sans', 'social-contact-form' ),
				'Lato'             => __( 'Lato', 'social-contact-form' ),
				'Montserrat'       => __( 'Montserrat', 'social-contact-form' ),
				'Raleway'          => __( 'Raleway', 'social-contact-form' ),
				'PT Sans'          => __( 'PT Sans', 'social-contact-form' ),
				'Roboto Slab'      => __( 'Roboto Slab', 'social-contact-form' ),
				'Merriweather'     => __( 'Merriweather', 'social-contact-form' ),
				'Playfair Display' => __( 'Playfair Display', 'social-contact-form' ),
				'Source Sans Pro'  => __( 'Source Sans Pro', 'social-contact-form' ),
				'Noto Sans'        => __( 'Noto Sans', 'social-contact-form' ),
				'Noto Serif'       => __( 'Noto Serif', 'social-contact-form' ),
				'Roboto Mono'      => __( 'Roboto Mono', 'social-contact-form' ),
				'Nunito'           => __( 'Nunito', 'social-contact-form' ),
				'Poppins'          => __( 'Poppins', 'social-contact-form' ),
				'Rubik'            => __( 'Rubik', 'social-contact-form' ),

			];

			return apply_filters( 'formychat_fonts', $fonts );
		}

		/**
		 * Resolve the admin's dial code from their IP, for use as a default.
		 *
		 * Runs server side because browsers can no longer call a geolocation
		 * API directly: ipwhois.app answers cross origin requests with HTTP 403
		 * ("CORS is not supported on the Free plan"). WordPress is not subject
		 * to CORS, so the lookup happens here and is cached in a transient.
		 *
		 * A code saved explicitly through the settings always wins; this is only
		 * consulted when nothing has been chosen yet.
		 *
		 * @param string $fallback Returned when detection is unavailable.
		 * @return string Dial code without the leading plus.
		 */
		public static function default_country_code( $fallback = '44' ) {
			$saved = get_option( 'formychat_country_code', '' );

			if ( ! empty( $saved ) ) {
				return (string) $saved;
			}

			$geo = self::geolocate();

			return ! empty( $geo['dial_code'] ) ? $geo['dial_code'] : $fallback;
		}

		/**
		 * Geolocate the current request and return the location payload.
		 *
		 * Cached per IP: a day on success, ten minutes on failure so a transient
		 * outage does not pin an empty answer for a whole day.
		 *
		 * @return array Location payload; fields are empty when unresolved.
		 */
		public static function geolocate() {
			$ip = self::client_ip();

			$cache_key = 'formychat_geo_' . md5( $ip );
			$cached    = get_transient( $cache_key );

			if ( false !== $cached ) {
				return $cached;
			}

			$payload = [
				'country_code' => '',
				'country'      => '',
				'dial_code'    => '',
				'city'         => '',
				'region'       => '',
				'timezone'     => '',
				'currency'     => '',
				'isp'          => '',
				'org'          => '',
				'latitude'     => 0,
				'longitude'    => 0,
				'ip'           => $ip,
			];

			$is_public_ip = ! empty( $ip ) && filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );

			// On a local or LAN install the caller's address is a loopback or
			// private one, which carries no location. Querying with no IP makes
			// the API geolocate the request's own source address, which on such
			// a setup is the developer's real connection. Remote installs keep
			// using the visitor's address.
			$lookup_ip = $is_public_ip ? rawurlencode( $ip ) : '';

			$fields = 'status,country,countryCode,region,regionName,city,timezone,currency,isp,org,lat,lon';

			$response = wp_remote_get(
				'http://ip-api.com/json/' . $lookup_ip . '?fields=' . $fields,
				[
					'timeout' => 5,
				]
			);

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( is_array( $body ) && isset( $body['status'] ) && 'success' === $body['status'] ) {
					$country_code = isset( $body['countryCode'] ) ? sanitize_text_field( $body['countryCode'] ) : '';
					$country_name = isset( $body['country'] ) ? sanitize_text_field( $body['country'] ) : '';

					$payload['country_code'] = $country_code;
					$payload['country']      = $country_name;
					$payload['dial_code']    = self::dial_code( $country_name, $country_code );
					$payload['city']         = isset( $body['city'] ) ? sanitize_text_field( $body['city'] ) : '';
					$payload['region']       = isset( $body['regionName'] ) ? sanitize_text_field( $body['regionName'] ) : '';
					$payload['timezone']     = isset( $body['timezone'] ) ? sanitize_text_field( $body['timezone'] ) : '';
					$payload['currency']     = isset( $body['currency'] ) ? sanitize_text_field( $body['currency'] ) : '';
					$payload['isp']          = isset( $body['isp'] ) ? sanitize_text_field( $body['isp'] ) : '';
					$payload['org']          = isset( $body['org'] ) ? sanitize_text_field( $body['org'] ) : '';
					$payload['latitude']     = isset( $body['lat'] ) ? (float) $body['lat'] : 0;
					$payload['longitude']    = isset( $body['lon'] ) ? (float) $body['lon'] : 0;
				}
			}

			set_transient( $cache_key, $payload, empty( $payload['dial_code'] ) ? 10 * MINUTE_IN_SECONDS : DAY_IN_SECONDS );

			return $payload;
		}

		/**
		 * Resolve the client IP, honouring common proxy headers.
		 *
		 * @return string IP address, or an empty string when unavailable.
		 */
		public static function client_ip() {
			$headers = [
				'HTTP_CF_CONNECTING_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_REAL_IP',
				'REMOTE_ADDR',
			];

			foreach ( $headers as $header ) {
				if ( empty( $_SERVER[ $header ] ) ) {
					continue;
				}

				$value = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );

				// X-Forwarded-For may hold a comma separated chain; the client
				// is the first entry.
				if ( false !== strpos( $value, ',' ) ) {
					$parts = explode( ',', $value );
					$value = trim( $parts[0] );
				}

				if ( filter_var( $value, FILTER_VALIDATE_IP ) ) {
					return $value;
				}
			}

			return '';
		}

		/**
		 * Map a geolocated country to its dial code.
		 *
		 * Reuses the country table below, so there is one source of truth. That
		 * table carries no ISO codes and merges some territories into a single
		 * row (for example "United States / Canada"), so matching falls back
		 * through a slash split and a small alias map for names the geo API
		 * spells differently.
		 *
		 * @param string $country_name Country name as returned by the geo API.
		 * @param string $country_code ISO 3166-1 alpha-2 code, used for aliases.
		 * @return string Dial code without the leading plus, or an empty string.
		 */
		public static function dial_code( $country_name, $country_code = '' ) {
			if ( empty( $country_name ) && empty( $country_code ) ) {
				return '';
			}

			$aliases = [
				'US' => '1',
				'CA' => '1',
				'GB' => '44',
				'RU' => '7',
				'KR' => '82',
				'KP' => '850',
				'VN' => '84',
				'IR' => '98',
				'SY' => '963',
				'LA' => '856',
				'TZ' => '255',
				'VE' => '58',
				'BO' => '591',
				'MD' => '373',
				'CZ' => '420',
				'MK' => '389',
				'PS' => '970',
				'CD' => '243',
				'CG' => '242',
				'CI' => '225',
				'CV' => '238',
				'TL' => '670',
				'BN' => '673',
			];

			$iso = strtoupper( trim( $country_code ) );

			if ( ! empty( $iso ) && isset( $aliases[ $iso ] ) ) {
				return $aliases[ $iso ];
			}

			$countries = self::countries();
			$needle    = strtolower( trim( $country_name ) );

			if ( empty( $needle ) ) {
				return '';
			}

			foreach ( $countries as $country ) {
				if ( ! isset( $country['name'], $country['code'] ) ) {
					continue;
				}

				if ( strtolower( $country['name'] ) === $needle ) {
					return ltrim( (string) $country['code'], '+' );
				}
			}

			// Combined rows such as "United States / Canada": compare each half.
			foreach ( $countries as $country ) {
				if ( ! isset( $country['name'], $country['code'] ) || false === strpos( $country['name'], '/' ) ) {
					continue;
				}

				foreach ( explode( '/', $country['name'] ) as $part ) {
					if ( strtolower( trim( $part ) ) === $needle ) {
						return ltrim( (string) $country['code'], '+' );
					}
				}
			}

			return '';
		}

		/**
		 * Returns the default countries.
		 *
		 * @return array
		 */
		public static function countries() {
			$countries = [
				[
					'name' => 'Afghanistan',
					'code' => '93',
					'flag' => '🇦🇫',
				],
				[
					'name' => 'Aland Islands',
					'code' => '35818',
					'flag' => '🇦🇽',
				],
				[
					'name' => 'Albania',
					'code' => '355',
					'flag' => '🇦🇱',
				],
				[
					'name' => 'Algeria',
					'code' => '213',
					'flag' => '🇩🇿',
				],
				[
					'name' => 'American Samoa',
					'code' => '1684',
					'flag' => '🇦🇸',
				],
				[
					'name' => 'Andorra',
					'code' => '376',
					'flag' => '🇦🇩',
				],
				[
					'name' => 'Angola',
					'code' => '244',
					'flag' => '🇦🇴',
				],
				[
					'name' => 'Anguilla',
					'code' => '1264',
					'flag' => '🇦🇮',
				],
				[
					'name' => 'Antarctica',
					'code' => '672',
					'flag' => '🇦🇶',
				],
				[
					'name' => 'Antigua And Barbuda',
					'code' => '1268',
					'flag' => '🇦🇬',
				],
				[
					'name' => 'Argentina',
					'code' => '54',
					'flag' => '🇦🇷',
				],
				[
					'name' => 'Armenia',
					'code' => '374',
					'flag' => '🇦🇲',
				],
				[
					'name' => 'Aruba',
					'code' => '297',
					'flag' => '🇦🇼',
				],
				[
					'name' => 'Australia',
					'code' => '61',
					'flag' => '🇦🇺',
				],
				[
					'name' => 'Austria',
					'code' => '43',
					'flag' => '🇦🇹',
				],
				[
					'name' => 'Azerbaijan',
					'code' => '994',
					'flag' => '🇦🇿',
				],
				[
					'name' => 'Bahrain',
					'code' => '973',
					'flag' => '🇧🇭',
				],
				[
					'name' => 'Bangladesh',
					'code' => '880',
					'flag' => '🇧🇩',
				],
				[
					'name' => 'Barbados',
					'code' => '1246',
					'flag' => '🇧🇧',
				],
				[
					'name' => 'Belarus',
					'code' => '375',
					'flag' => '🇧🇾',
				],
				[
					'name' => 'Belgium',
					'code' => '32',
					'flag' => '🇧🇪',
				],
				[
					'name' => 'Belize',
					'code' => '501',
					'flag' => '🇧🇿',
				],
				[
					'name' => 'Benin',
					'code' => '229',
					'flag' => '🇧🇯',
				],
				[
					'name' => 'Bermuda',
					'code' => '1441',
					'flag' => '🇧🇲',
				],
				[
					'name' => 'Bhutan',
					'code' => '975',
					'flag' => '🇧🇹',
				],
				[
					'name' => 'Bolivia',
					'code' => '591',
					'flag' => '🇧🇴',
				],
				[
					'name' => 'Bonaire, Sint Eustatius and Saba',
					'code' => '599',
					'flag' => '🇧🇶',
				],
				[
					'name' => 'Bosnia and Herzegovina',
					'code' => '387',
					'flag' => '🇧🇦',
				],
				[
					'name' => 'Botswana',
					'code' => '267',
					'flag' => '🇧🇼',
				],
				[
					'name' => 'Bouvet Island',
					'code' => '0055',
					'flag' => '🇧🇻',
				],
				[
					'name' => 'Brazil',
					'code' => '55',
					'flag' => '🇧🇷',
				],
				[
					'name' => 'British Indian Ocean Territory',
					'code' => '246',
					'flag' => '🇮🇴',
				],
				[
					'name' => 'Brunei',
					'code' => '673',
					'flag' => '🇧🇳',
				],
				[
					'name' => 'Bulgaria',
					'code' => '359',
					'flag' => '🇧🇬',
				],
				[
					'name' => 'Burkina Faso',
					'code' => '226',
					'flag' => '🇧🇫',
				],
				[
					'name' => 'Burundi',
					'code' => '257',
					'flag' => '🇧🇮',
				],
				[
					'name' => 'Cambodia',
					'code' => '855',
					'flag' => '🇰🇭',
				],
				[
					'name' => 'Cameroon',
					'code' => '237',
					'flag' => '🇨🇲',
				],
				[
					'name' => 'Cape Verde',
					'code' => '238',
					'flag' => '🇨🇻',
				],
				[
					'name' => 'Cayman Islands',
					'code' => '1345',
					'flag' => '🇰🇾',
				],
				[
					'name' => 'Central African Republic',
					'code' => '236',
					'flag' => '🇨🇫',
				],
				[
					'name' => 'Chad',
					'code' => '235',
					'flag' => '🇹🇩',
				],
				[
					'name' => 'Chile',
					'code' => '56',
					'flag' => '🇨🇱',
				],
				[
					'name' => 'China',
					'code' => '86',
					'flag' => '🇨🇳',
				],
				[
					'name' => 'Christmas Island',
					'code' => '61',
					'flag' => '🇨🇽',
				],
				[
					'name' => 'Cocos (Keeling) Islands',
					'code' => '61',
					'flag' => '🇨🇨',
				],
				[
					'name' => 'Colombia',
					'code' => '57',
					'flag' => '🇨🇴',
				],
				[
					'name' => 'Comoros',
					'code' => '269',
					'flag' => '🇰🇲',
				],
				[
					'name' => 'Congo',
					'code' => '242',
					'flag' => '🇨🇬',
				],
				[
					'name' => 'Cook Islands',
					'code' => '682',
					'flag' => '🇨🇰',
				],
				[
					'name' => 'Costa Rica',
					'code' => '506',
					'flag' => '🇨🇷',
				],
				[
					'name' => 'Cote D\'Ivoire (Ivory Coast)',
					'code' => '225',
					'flag' => '🇨🇮',
				],
				[
					'name' => 'Croatia',
					'code' => '385',
					'flag' => '🇭🇷',
				],
				[
					'name' => 'Cuba',
					'code' => '53',
					'flag' => '🇨🇺',
				],
				[
					'name' => 'Curaçao',
					'code' => '599',
					'flag' => '🇨🇼',
				],
				[
					'name' => 'Cyprus',
					'code' => '357',
					'flag' => '🇨🇾',
				],
				[
					'name' => 'Czech Republic',
					'code' => '420',
					'flag' => '🇨🇿',
				],
				[
					'name' => 'Democratic Republic of the Congo',
					'code' => '243',
					'flag' => '🇨🇩',
				],
				[
					'name' => 'Denmark',
					'code' => '45',
					'flag' => '🇩🇰',
				],
				[
					'name' => 'Djibouti',
					'code' => '253',
					'flag' => '🇩🇯',
				],
				[
					'name' => 'Dominica',
					'code' => '1767',
					'flag' => '🇩🇲',
				],
				[
					'name' => 'Dominican Republic',
					'code' => '1809',
					'flag' => '🇩🇴',
				],
				[
					'name' => 'Dominican Republic',
					'code' => '1829',
					'flag' => '🇩🇴',
				],
				[
					'name' => 'East Timor',
					'code' => '670',
					'flag' => '🇹🇱',
				],
				[
					'name' => 'Ecuador',
					'code' => '593',
					'flag' => '🇪🇨',
				],
				[
					'name' => 'Egypt',
					'code' => '20',
					'flag' => '🇪🇬',
				],
				[
					'name' => 'El Salvador',
					'code' => '503',
					'flag' => '🇸🇻',
				],
				[
					'name' => 'Equatorial Guinea',
					'code' => '240',
					'flag' => '🇬🇶',
				],
				[
					'name' => 'Eritrea',
					'code' => '291',
					'flag' => '🇪🇷',
				],
				[
					'name' => 'Estonia',
					'code' => '372',
					'flag' => '🇪🇪',
				],
				[
					'name' => 'Ethiopia',
					'code' => '251',
					'flag' => '🇪🇹',
				],
				[
					'name' => 'Falkland Islands',
					'code' => '500',
					'flag' => '🇫🇰',
				],
				[
					'name' => 'Faroe Islands',
					'code' => '298',
					'flag' => '🇫🇴',
				],
				[
					'name' => 'Fiji Islands',
					'code' => '679',
					'flag' => '🇫🇯',
				],
				[
					'name' => 'Finland',
					'code' => '358',
					'flag' => '🇫🇮',
				],
				[
					'name' => 'France',
					'code' => '33',
					'flag' => '🇫🇷',
				],
				[
					'name' => 'French Guiana',
					'code' => '594',
					'flag' => '🇬🇫',
				],
				[
					'name' => 'French Polynesia',
					'code' => '689',
					'flag' => '🇵🇫',
				],
				[
					'name' => 'French Southern Territories',
					'code' => '262',
					'flag' => '🇹🇫',
				],
				[
					'name' => 'Gabon',
					'code' => '241',
					'flag' => '🇬🇦',
				],
				[
					'name' => 'Gambia The',
					'code' => '220',
					'flag' => '🇬🇲',
				],
				[
					'name' => 'Georgia',
					'code' => '995',
					'flag' => '🇬🇪',
				],
				[
					'name' => 'Germany',
					'code' => '49',
					'flag' => '🇩🇪',
				],
				[
					'name' => 'Ghana',
					'code' => '233',
					'flag' => '🇬🇭',
				],
				[
					'name' => 'Gibraltar',
					'code' => '350',
					'flag' => '🇬🇮',
				],
				[
					'name' => 'Greece',
					'code' => '30',
					'flag' => '🇬🇷',
				],
				[
					'name' => 'Greenland',
					'code' => '299',
					'flag' => '🇬🇱',
				],
				[
					'name' => 'Grenada',
					'code' => '1473',
					'flag' => '🇬🇩',
				],
				[
					'name' => 'Guadeloupe',
					'code' => '590',
					'flag' => '🇬🇵',
				],
				[
					'name' => 'Guam',
					'code' => '1671',
					'flag' => '🇬🇺',
				],
				[
					'name' => 'Guatemala',
					'code' => '502',
					'flag' => '🇬🇹',
				],
				[
					'name' => 'Guernsey and Alderney',
					'code' => '441481',
					'flag' => '🇬🇬',
				],
				[
					'name' => 'Guinea',
					'code' => '224',
					'flag' => '🇬🇳',
				],
				[
					'name' => 'GuineaBissau',
					'code' => '245',
					'flag' => '🇬🇼',
				],
				[
					'name' => 'Guyana',
					'code' => '592',
					'flag' => '🇬🇾',
				],
				[
					'name' => 'Haiti',
					'code' => '509',
					'flag' => '🇭🇹',
				],
				[
					'name' => 'Heard Island and McDonald Islands',
					'code' => '672',
					'flag' => '🇭🇲',
				],
				[
					'name' => 'Honduras',
					'code' => '504',
					'flag' => '🇭🇳',
				],
				[
					'name' => 'Hong Kong S.A.R.',
					'code' => '852',
					'flag' => '🇭🇰',
				],
				[
					'name' => 'Hungary',
					'code' => '36',
					'flag' => '🇭🇺',
				],
				[
					'name' => 'Iceland',
					'code' => '354',
					'flag' => '🇮🇸',
				],
				[
					'name' => 'India',
					'code' => '91',
					'flag' => '🇮🇳',
				],
				[
					'name' => 'Indonesia',
					'code' => '62',
					'flag' => '🇮🇩',
				],
				[
					'name' => 'Iran',
					'code' => '98',
					'flag' => '🇮🇷',
				],
				[
					'name' => 'Iraq',
					'code' => '964',
					'flag' => '🇮🇶',
				],
				[
					'name' => 'Ireland',
					'code' => '353',
					'flag' => '🇮🇪',
				],
				[
					'name' => 'Israel',
					'code' => '972',
					'flag' => '🇮🇱',
				],
				[
					'name' => 'Italy',
					'code' => '39',
					'flag' => '🇮🇹',
				],
				[
					'name' => 'Jamaica',
					'code' => '1876',
					'flag' => '🇯🇲',
				],
				[
					'name' => 'Japan',
					'code' => '81',
					'flag' => '🇯🇵',
				],
				[
					'name' => 'Jersey',
					'code' => '441534',
					'flag' => '🇯🇪',
				],
				[
					'name' => 'Jordan',
					'code' => '962',
					'flag' => '🇯🇴',
				],
				[
					'name' => 'Kazakhstan',
					'code' => '7',
					'flag' => '🇰🇿',
				],
				[
					'name' => 'Kenya',
					'code' => '254',
					'flag' => '🇰🇪',
				],
				[
					'name' => 'Kiribati',
					'code' => '686',
					'flag' => '🇰🇮',
				],
				[
					'name' => 'Kosovo',
					'code' => '383',
					'flag' => '🇽🇰',
				],
				[
					'name' => 'Kuwait',
					'code' => '965',
					'flag' => '🇰🇼',
				],
				[
					'name' => 'Kyrgyzstan',
					'code' => '996',
					'flag' => '🇰🇬',
				],
				[
					'name' => 'Laos',
					'code' => '856',
					'flag' => '🇱🇦',
				],
				[
					'name' => 'Latvia',
					'code' => '371',
					'flag' => '🇱🇻',
				],
				[
					'name' => 'Lebanon',
					'code' => '961',
					'flag' => '🇱🇧',
				],
				[
					'name' => 'Lesotho',
					'code' => '266',
					'flag' => '🇱🇸',
				],
				[
					'name' => 'Liberia',
					'code' => '231',
					'flag' => '🇱🇷',
				],
				[
					'name' => 'Libya',
					'code' => '218',
					'flag' => '🇱🇾',
				],
				[
					'name' => 'Liechtenstein',
					'code' => '423',
					'flag' => '🇱🇮',
				],
				[
					'name' => 'Lithuania',
					'code' => '370',
					'flag' => '🇱🇹',
				],
				[
					'name' => 'Luxembourg',
					'code' => '352',
					'flag' => '🇱🇺',
				],
				[
					'name' => 'Macau S.A.R.',
					'code' => '853',
					'flag' => '🇲🇴',
				],
				[
					'name' => 'Macedonia',
					'code' => '389',
					'flag' => '🇲🇰',
				],
				[
					'name' => 'Madagascar',
					'code' => '261',
					'flag' => '🇲🇬',
				],
				[
					'name' => 'Malawi',
					'code' => '265',
					'flag' => '🇲🇼',
				],
				[
					'name' => 'Malaysia',
					'code' => '60',
					'flag' => '🇲🇾',
				],
				[
					'name' => 'Maldives',
					'code' => '960',
					'flag' => '🇲🇻',
				],
				[
					'name' => 'Mali',
					'code' => '223',
					'flag' => '🇲🇱',
				],
				[
					'name' => 'Malta',
					'code' => '356',
					'flag' => '🇲🇹',
				],
				[
					'name' => 'Man (Isle of)',
					'code' => '441624',
					'flag' => '🇮🇲',
				],
				[
					'name' => 'Marshall Islands',
					'code' => '692',
					'flag' => '🇲🇭',
				],
				[
					'name' => 'Martinique',
					'code' => '596',
					'flag' => '🇲🇶',
				],
				[
					'name' => 'Mauritania',
					'code' => '222',
					'flag' => '🇲🇷',
				],
				[
					'name' => 'Mauritius',
					'code' => '230',
					'flag' => '🇲🇺',
				],
				[
					'name' => 'Mayotte',
					'code' => '262',
					'flag' => '🇾🇹',
				],
				[
					'name' => 'Mexico',
					'code' => '52',
					'flag' => '🇲🇽',
				],
				[
					'name' => 'Micronesia',
					'code' => '691',
					'flag' => '🇫🇲',
				],
				[
					'name' => 'Moldova',
					'code' => '373',
					'flag' => '🇲🇩',
				],
				[
					'name' => 'Monaco',
					'code' => '377',
					'flag' => '🇲🇨',
				],
				[
					'name' => 'Mongolia',
					'code' => '976',
					'flag' => '🇲🇳',
				],
				[
					'name' => 'Montenegro',
					'code' => '382',
					'flag' => '🇲🇪',
				],
				[
					'name' => 'Montserrat',
					'code' => '1664',
					'flag' => '🇲🇸',
				],
				[
					'name' => 'Morocco',
					'code' => '212',
					'flag' => '🇲🇦',
				],
				[
					'name' => 'Mozambique',
					'code' => '258',
					'flag' => '🇲🇿',
				],
				[
					'name' => 'Myanmar',
					'code' => '95',
					'flag' => '🇲🇲',
				],
				[
					'name' => 'Namibia',
					'code' => '264',
					'flag' => '🇳🇦',
				],
				[
					'name' => 'Nauru',
					'code' => '674',
					'flag' => '🇳🇷',
				],
				[
					'name' => 'Nepal',
					'code' => '977',
					'flag' => '🇳🇵',
				],
				[
					'name' => 'Netherlands',
					'code' => '31',
					'flag' => '🇳🇱',
				],
				[
					'name' => 'New Caledonia',
					'code' => '687',
					'flag' => '🇳🇨',
				],
				[
					'name' => 'New Zealand',
					'code' => '64',
					'flag' => '🇳🇿',
				],
				[
					'name' => 'Nicaragua',
					'code' => '505',
					'flag' => '🇳🇮',
				],
				[
					'name' => 'Niger',
					'code' => '227',
					'flag' => '🇳🇪',
				],
				[
					'name' => 'Nigeria',
					'code' => '234',
					'flag' => '🇳🇬',
				],
				[
					'name' => 'Niue',
					'code' => '683',
					'flag' => '🇳🇺',
				],
				[
					'name' => 'Norfolk Island',
					'code' => '672',
					'flag' => '🇳🇫',
				],
				[
					'name' => 'North Korea',
					'code' => '850',
					'flag' => '🇰🇵',
				],
				[
					'name' => 'Northern Mariana Islands',
					'code' => '1670',
					'flag' => '🇲🇵',
				],
				[
					'name' => 'Norway',
					'code' => '47',
					'flag' => '🇳🇴',
				],
				[
					'name' => 'Oman',
					'code' => '968',
					'flag' => '🇴🇲',
				],
				[
					'name' => 'Pakistan',
					'code' => '92',
					'flag' => '🇵🇰',
				],
				[
					'name' => 'Palau',
					'code' => '680',
					'flag' => '🇵🇼',
				],
				[
					'name' => 'Palestinian Territory Occupied',
					'code' => '970',
					'flag' => '🇵🇸',
				],
				[
					'name' => 'Panama',
					'code' => '507',
					'flag' => '🇵🇦',
				],
				[
					'name' => 'Papua new Guinea',
					'code' => '675',
					'flag' => '🇵🇬',
				],
				[
					'name' => 'Paraguay',
					'code' => '595',
					'flag' => '🇵🇾',
				],
				[
					'name' => 'Peru',
					'code' => '51',
					'flag' => '🇵🇪',
				],
				[
					'name' => 'Philippines',
					'code' => '63',
					'flag' => '🇵🇭',
				],
				[
					'name' => 'Pitcairn Island',
					'code' => '870',
					'flag' => '🇵🇳',
				],
				[
					'name' => 'Poland',
					'code' => '48',
					'flag' => '🇵🇱',
				],
				[
					'name' => 'Portugal',
					'code' => '351',
					'flag' => '🇵🇹',
				],
				[
					'name' => 'Puerto Rico',
					'code' => '1787',
					'flag' => '🇵🇷',
				],
				[
					'name' => 'Puerto Rico',
					'code' => '1939',
					'flag' => '🇵🇷',
				],
				[
					'name' => 'Qatar',
					'code' => '974',
					'flag' => '🇶🇦',
				],
				[
					'name' => 'Reunion',
					'code' => '262',
					'flag' => '🇷🇪',
				],
				[
					'name' => 'Romania',
					'code' => '40',
					'flag' => '🇷🇴',
				],
				[
					'name' => 'Russia',
					'code' => '7',
					'flag' => '🇷🇺',
				],
				[
					'name' => 'Rwanda',
					'code' => '250',
					'flag' => '🇷🇼',
				],
				[
					'name' => 'Saint Helena',
					'code' => '290',
					'flag' => '🇸🇭',
				],
				[
					'name' => 'Saint Kitts And Nevis',
					'code' => '1869',
					'flag' => '🇰🇳',
				],
				[
					'name' => 'Saint Lucia',
					'code' => '1758',
					'flag' => '🇱🇨',
				],
				[
					'name' => 'Saint Pierre and Miquelon',
					'code' => '508',
					'flag' => '🇵🇲',
				],
				[
					'name' => 'Saint Vincent And The Grenadines',
					'code' => '1784',
					'flag' => '🇻🇨',
				],
				[
					'name' => 'SaintBarthelemy',
					'code' => '590',
					'flag' => '🇧🇱',
				],
				[
					'name' => 'SaintMartin (French part)',
					'code' => '590',
					'flag' => '🇲🇫',
				],
				[
					'name' => 'Samoa',
					'code' => '685',
					'flag' => '🇼🇸',
				],
				[
					'name' => 'San Marino',
					'code' => '378',
					'flag' => '🇸🇲',
				],
				[
					'name' => 'Sao Tome and Principe',
					'code' => '239',
					'flag' => '🇸🇹',
				],
				[
					'name' => 'Saudi Arabia',
					'code' => '966',
					'flag' => '🇸🇦',
				],
				[
					'name' => 'Senegal',
					'code' => '221',
					'flag' => '🇸🇳',
				],
				[
					'name' => 'Serbia',
					'code' => '381',
					'flag' => '🇷🇸',
				],
				[
					'name' => 'Seychelles',
					'code' => '248',
					'flag' => '🇸🇨',
				],
				[
					'name' => 'Sierra Leone',
					'code' => '232',
					'flag' => '🇸🇱',
				],
				[
					'name' => 'Singapore',
					'code' => '65',
					'flag' => '🇸🇬',
				],
				[
					'name' => 'Sint Maarten (Dutch part)',
					'code' => '1721',
					'flag' => '🇸🇽',
				],
				[
					'name' => 'Slovakia',
					'code' => '421',
					'flag' => '🇸🇰',
				],
				[
					'name' => 'Slovenia',
					'code' => '386',
					'flag' => '🇸🇮',
				],
				[
					'name' => 'Solomon Islands',
					'code' => '677',
					'flag' => '🇸🇧',
				],
				[
					'name' => 'Somalia',
					'code' => '252',
					'flag' => '🇸🇴',
				],
				[
					'name' => 'South Africa',
					'code' => '27',
					'flag' => '🇿🇦',
				],
				[
					'name' => 'South Georgia',
					'code' => '500',
					'flag' => '🇬🇸',
				],
				[
					'name' => 'South Korea',
					'code' => '82',
					'flag' => '🇰🇷',
				],
				[
					'name' => 'South Sudan',
					'code' => '211',
					'flag' => '🇸🇸',
				],
				[
					'name' => 'Spain',
					'code' => '34',
					'flag' => '🇪🇸',
				],
				[
					'name' => 'Sri Lanka',
					'code' => '94',
					'flag' => '🇱🇰',
				],
				[
					'name' => 'Sudan',
					'code' => '249',
					'flag' => '🇸🇩',
				],
				[
					'name' => 'Suriname',
					'code' => '597',
					'flag' => '🇸🇷',
				],
				[
					'name' => 'Svalbard And Jan Mayen Islands',
					'code' => '47',
					'flag' => '🇸🇯',
				],
				[
					'name' => 'Swaziland',
					'code' => '268',
					'flag' => '🇸🇿',
				],
				[
					'name' => 'Sweden',
					'code' => '46',
					'flag' => '🇸🇪',
				],
				[
					'name' => 'Switzerland',
					'code' => '41',
					'flag' => '🇨🇭',
				],
				[
					'name' => 'Syria',
					'code' => '963',
					'flag' => '🇸🇾',
				],
				[
					'name' => 'Taiwan',
					'code' => '886',
					'flag' => '🇹🇼',
				],
				[
					'name' => 'Tajikistan',
					'code' => '992',
					'flag' => '🇹🇯',
				],
				[
					'name' => 'Tanzania',
					'code' => '255',
					'flag' => '🇹🇿',
				],
				[
					'name' => 'Thailand',
					'code' => '66',
					'flag' => '🇹🇭',
				],
				[
					'name' => 'The Bahamas',
					'code' => '1242',
					'flag' => '🇧🇸',
				],
				[
					'name' => 'Togo',
					'code' => '228',
					'flag' => '🇹🇬',
				],
				[
					'name' => 'Tokelau',
					'code' => '690',
					'flag' => '🇹🇰',
				],
				[
					'name' => 'Tonga',
					'code' => '676',
					'flag' => '🇹🇴',
				],
				[
					'name' => 'Trinidad And Tobago',
					'code' => '1868',
					'flag' => '🇹🇹',
				],
				[
					'name' => 'Tunisia',
					'code' => '216',
					'flag' => '🇹🇳',
				],
				[
					'name' => 'Turkey',
					'code' => '90',
					'flag' => '🇹🇷',
				],
				[
					'name' => 'Turkmenistan',
					'code' => '993',
					'flag' => '🇹🇲',
				],
				[
					'name' => 'Turks And Caicos Islands',
					'code' => '1649',
					'flag' => '🇹🇨',
				],
				[
					'name' => 'Tuvalu',
					'code' => '688',
					'flag' => '🇹🇻',
				],
				[
					'name' => 'Uganda',
					'code' => '256',
					'flag' => '🇺🇬',
				],
				[
					'name' => 'Ukraine',
					'code' => '380',
					'flag' => '🇺🇦',
				],
				[
					'name' => 'United Arab Emirates',
					'code' => '971',
					'flag' => '🇦🇪',
				],
				[
					'name' => 'United Kingdom',
					'code' => '44',
					'flag' => '🇬🇧',
				],
				[
					'name' => 'United States / Canada',
					'code' => '1',
					'flag' => '🇺🇸',
				],
				[
					'name' => 'Uruguay',
					'code' => '598',
					'flag' => '🇺🇾',
				],
				[
					'name' => 'Uzbekistan',
					'code' => '998',
					'flag' => '🇺🇿',
				],
				[
					'name' => 'Vanuatu',
					'code' => '678',
					'flag' => '🇻🇺',
				],
				[
					'name' => 'Vatican City State (Holy See)',
					'code' => '379',
					'flag' => '🇻🇦',
				],
				[
					'name' => 'Venezuela',
					'code' => '58',
					'flag' => '🇻🇪',
				],
				[
					'name' => 'Vietnam',
					'code' => '84',
					'flag' => '🇻🇳',
				],
				[
					'name' => 'Virgin Islands (British)',
					'code' => '1284',
					'flag' => '🇻🇬',
				],
				[
					'name' => 'Virgin Islands (US)',
					'code' => '1340',
					'flag' => '🇻🇮',
				],
				[
					'name' => 'Wallis And Futuna Islands',
					'code' => '681',
					'flag' => '🇼🇫',
				],
				[
					'name' => 'Western Sahara',
					'code' => '212',
					'flag' => '🇪🇭',
				],
				[
					'name' => 'Yemen',
					'code' => '967',
					'flag' => '🇾🇪',
				],
				[
					'name' => 'Zambia',
					'code' => '260',
					'flag' => '🇿🇲',
				],
				[
					'name' => 'Zimbabwe',
					'code' => '263',
					'flag' => '🇿🇼',
				],
			];

			// map only name, phone_code, emoji.
			$countries = array_map( function ( $country ) {
				return [
					'name' => isset( $country['name'] ) ? $country['name'] : '',
					'code'  => isset( $country['code'] ) ? $country['code'] : '',
					'flag'  => isset( $country['flag'] ) ? $country['flag'] : '',
				];
			}, $countries );

			return apply_filters( 'formychat_countries', $countries );
		}

		/**
		 * Embed fonts.
		 *
		 * @since 1.0.0
		 * @return mixed
		 */
		public static function embed_fonts() {
			$fonts = self::fonts();

			$css = '';

			if ( $fonts && ! empty( $fonts ) ) {
				foreach ( $fonts as $font_family => $label ) {
					$path = FORMYCHAT_PUBLIC . '/fonts/' . str_replace( ' ', '+', $font_family );
					$css .= "@font-face {
						font-family: '{$font_family}';
						src: url('{$path}.ttf') format('truetype');
					}";
				}
			}

			$css = apply_filters( 'formychat_fonts_css', $css );
			return $css;
		}

		/**
		 * Widget Configuration blueprint.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public static function widget_config() {
			$configuration = [
				// Schema version of this config. Widgets saved before channels existed have no version (1).
				'config_version' => self::CONFIG_VERSION,

				// Channels shown in the widget, in display order.
				'channels' => [
					'enabled'  => [ 'whatsapp' ],
					'launcher' => [
						'style'    => 'stack',
						'title'    => 'How would you like to reach us?',
						'show_sub' => true,
						'icon'     => 'chat',
						'color'    => '#4F46E5',
						// 2.16.0 additions; empty keeps today's look.
						'message'      => '',
						'header_color' => '',
						'icon_url'     => '',
						// Color of the launcher symbol; '' = white (today's look).
						'glyph_color'  => '',
					],
				],

				// What happens after the form is submitted, for every channel.
				'delivery' => [
					'redirect_after_submit' => true,
				],

				'whatsapp' => [
					'web_version' => true,
					'country_code' => '',
					'number' => '',
					'message_template' =>
						'Name: {name}
Email: {email}
Phone: {phone}
Message:
{message}
Dropdown: {dropdown}',
					'cf7_message_template' => '',
					'fluentform_message_template' => '',
					'gravity_message_template' => '',
					'wpforms_message_template' => '',
					'formidable_message_template' => '',
					'ninja_message_template' => '',

					'new_tab' => true,
					'agent_mode' => 'single',
					'agents' => [],
					'on_click_agent' => 'show_form',
					'destination_type' => 'phone',
					'group_invite_code' => '',
					'send_message_to_whatsapp' => true,

					'show_on_desktop' => true,
					'show_on_mobile' => true,
					'list_label' => 'WhatsApp',
					'list_sub' => 'Chat on WhatsApp',
					// Channel icon color (2.16.0); empty = brand color.
					'icon_color' => '',
					// Channel logo glyph color (2.16.0); empty = white.
					'glyph_color' => '',
				],
				'telegram' => [
					'mode' => 'username',
					'username' => '',
					'country_code' => '',
					'phone' => '',
					'new_tab' => true,
					'web_fallback' => true,
					'show_on_desktop' => true,
					'show_on_mobile' => true,
					'list_label' => 'Telegram',
					'list_sub' => 'Message us on Telegram',
					'form_title' => 'Contact via Telegram',
					'form_submit' => 'Send on Telegram',
					// Channel icon (2.16.0); empty = brand color / Telegram logo.
					'icon_color' => '',
					// Logo glyph color (2.16.0); empty = white.
					'glyph_color' => '',
					'icon_url' => '',
				],
				'icon' => [
					'has_delay' => false,
					'delay' => 3,
					'image_url' => FORMYCHAT_PUBLIC . '/images/whatsapp.svg',
					'size' => 'medium',
					'size_custom' => 80,
					'position' => 'right',
					'position_custom' => [
						'top' => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],

					'show_after_scroll' => false,
					'scroll_to' => 25,
					'show_on_exit' => false,
				],
				'cta' => [
					'enabled' => true,
					'text' => 'Contact us',
					'size' => 'medium',
					'size_custom' => 20,
					'color' => '#555555',
					'background_color' => '#FFFFFF',
				],
				'form' => [
					'mode' => 'formychat',
					'title' => 'Contact via WhatsApp',
					'subtitle' => '',
					'footer' => '',
					'submit' => 'Send on WhatsApp',
					'show_country_code_field' => false,
					'country_code' => '44',
					'size' => 'medium',
					'size_custom' => '',
					'font_family' => 'sans-serif',
					'text_color' => '#ffffff',
					'background_color' => '#09816D',
					'open_by_default' => false,
					'close_on_submit' => true,
					'recaptcha_enabled' => false,

					// Third party forms.
					'cf7_id' => 0,
					'gravity_id' => 0,
					'wpforms_id' => 0,
					'fluentform_id' => 0,
					'forminator_id' => 0,
					'formidable_id' => 0,
					'ninja_id' => 0,

					'google_analytics' => false,

					'custom_dropdown' => [
						'enabled'       => true,
						'label'         => 'Dropdown',
						'name'          => 'dropdown',
						'required'      => false,
						'placeholder'   => '- Select -',
						'position'      => 'before_message',
						'default_value' => '',
						'options'       => [
							[
								'value' => 'option_1',
								'label' => 'Option 1',
							],
							[
								'value' => 'option_2',
								'label' => 'Option 2',
							],
						],
					],
				],
				'email' => [
					'enabled' => false,
					'address' => '',
					'admin_email' => true,
				],
				'target' => [
					'exclude_pages'            => [],
					'exclude_all_pages'        => false,
					'exclude_all_pages_except' => [],
					'exclude_posts'            => [],
					'exclude_all_posts'        => false,
					'exclude_all_posts_except' => [],
				],
				'greetings' => [
					'enabled' => false,
					'template' => 'simple',
					'style' => 1,
					'on_click' => 'show_form',
					'templates' => [
						'simple' => [
							'heading' => '👋 Hi! Need any help?',
							'heading_size' => 'medium',
							'heading_size_custom' => 16,
							'heading_color' => '#111827',
							'message' => "We're here if you have any questions.",
							'message_size' => 'medium',
							'message_size_custom' => 16,
							'message_color' => '#6B7280',
							'background_color' => '#FFFFFF',
							'font_family' => 'sans-serif',
						],
						'wave' => [
							'show_icon' => true,
							'icon_url' => FORMYCHAT_PUBLIC . '/images/greetings/hand-wave.svg',
							'icon_url_custom' => '',
							'icon_position' => 'before_heading',
							'heading' => 'What are you looking for?',
							'heading_size' => 'medium',
							'heading_size_custom' => 16,
							'heading_color' => '#4F4F4F',
							'message' => 'Feel free to ask your questions here. We are always ready to assist you all the time whenever you need',
							'message_size' => 'medium',
							'message_size_custom' => 16,
							'message_color' => '#4F4F4F',
							'background_color' => '#FFFFFF',
							'font_family' => 'sans-serif',
							'show_cta' => true,
							'cta_text' => 'Ask your question',
							'cta_heading' => 'What are you looking for?',
							'cta_message' => 'Feel free to ask your questions here. We are always ready to assist you all the time whenever you need',
							'cta_icon_url' => FORMYCHAT_PUBLIC . '/images/greetings/vector.svg',
							'cta_icon_url_custom' => '',
							'cta_text_color' => '#2F80ED',
							'cta_background_color' => '#F5F7F8',
							'cta_heading_color' => '#2F80ED',
							'cta_message_color' => '#4F4F4F',
							'cta_heading_size' => 'medium',
							'cta_heading_size_custom' => 16,
							'cta_message_size' => 'medium',
							'cta_message_size_custom' => 16,
						],
					],
				],

				// Integrations.
				'integrations' => [
					'fluent-crm' => [
						'enabled' => false,
						'lists' => get_option( 'formychat_integration_fluent-crm_lists', [] ),
						'tags' => get_option( 'formychat_integration_fluent-crm_tags', [] ),
						'fields_mapping' => get_option( 'formychat_integration_fluent-crm_fields_mapping', [] ),
					],
				],
			];

			return apply_filters( 'formychat_widget_configuration', $configuration );
		}

		/**
		 * Custom Tags.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public static function custom_tags() {
			$tags = [
				'site_name' => get_bloginfo( 'name' ),
				'site_url' => get_site_url(),
				'user_id' => get_current_user_id(),
				'page_url' => '',
				'page_id' => get_the_ID(),
			];

			return apply_filters( 'formychat_custom_tags', $tags );
		}

		/**
		 * Get all forms.
		 *
		 * @return array
		 */
		public static function get_forms() {
			$forms = [
				'formychat' => [
					'label' => 'FormyChat',
					'logo' => FORMYCHAT_PUBLIC . '/images/forms/formychat.png',
				],
				'cf7' => [
					'label' => 'Contact Form 7',
					'logo' => FORMYCHAT_PUBLIC . '/images/forms/contact-form-7.png',
				],
				'wpforms' => [
					'label' => 'WP Forms',
					'logo' => FORMYCHAT_PUBLIC . '/images/forms/wp-forms.png',
				],
				'gravity' => [
					'label' => 'Gravity Forms',
					'logo' => FORMYCHAT_PUBLIC . '/images/forms/gravity-forms.png',
					'locked' => true,
				],
				'fluentform' => [
					'label' => 'Fluent Forms',
					'logo' => FORMYCHAT_PUBLIC . '/images/forms/fluent-form.png',
					'locked' => true,
				],
				'forminator' => [
					'label' => 'Forminator',
					'logo' => FORMYCHAT_PUBLIC . '/images/forms/forminator.png',
					'locked' => true,
				],
				'formidable' => [
					'label' => 'Formidable',
					'logo' => FORMYCHAT_PUBLIC . '/images/forms/formidable.png',
					'locked' => true,
				],

				'ninja' => [
					'label' => 'Ninja Forms',
					'logo' => FORMYCHAT_PUBLIC . '/images/forms/ninja-forms.png',
					'locked' => true,
				],
			];

			return apply_filters( 'formychat_forms', $forms );
		}



		/**
		 * FormyChat Form Fields.
		 *
		 * @since 1.0.0
		 */
		public static function form_fields() {

			$user = wp_get_current_user();
			$default_values = [];

			if ( $user->exists() ) {
				$default_values['name'] = $user->display_name;
				$default_values['email'] = $user->user_email;
				$default_values['phone'] = get_user_meta( $user->ID, 'billing_phone', true );
			}

			$fields = [
				[
					'name' => 'name',
					'type' => 'text',
					'default' => array_key_exists( 'name', $default_values ) ? $default_values['name'] : '',
					'help_text' => '',
					'attributes' => [
						'placeholder' => __( 'Enter your Name', 'social-contact-form' ),
						'required' => true,
					],
				],
				[
					'name' => 'email',
					'type' => 'email',
					'default' => array_key_exists( 'email', $default_values ) ? $default_values['email'] : '',
					'help_text' => '',
					'attributes' => [
						'placeholder' => __( 'Enter your Email', 'social-contact-form' ),
						'required' => true,
					],
					'condition' => 'name != ""',
				],
				[
					'name' => 'phone',
					'type' => 'phone',
					'default' => array_key_exists( 'phone', $default_values ) ? $default_values['phone'] : '',
					'help_text' => '',
					'attributes' => [
						'placeholder' => __( 'Enter your Phone', 'social-contact-form' ),
						'required' => true,
						'minlength' => 7,
						'maxlength' => 15,
						'min' => '100000',
						'max' => '999999999999999',
					],
				],
				[
					'name' => 'message',
					'type' => 'textarea',
					'help_text' => '',
					'attributes' => [
						'placeholder' => __( 'Enter your Message', 'social-contact-form' ),
						'required' => false,
					],
				],
			];

			return apply_filters( 'formychat_form_fields', $fields );
		}

		/**
		 * Sanitize FormyChat custom dropdown config (per-widget).
		 *
		 * @param mixed  $dropdown Raw config.
		 * @param string $form_mode  Form mode; only applied when formychat.
		 * @return array
		 */
		public static function sanitize_custom_dropdown( $dropdown, $form_mode = 'formychat' ) {
			$defaults = [
				'enabled'       => false,
				'label'         => '',
				'name'          => 'dropdown',
				'required'      => false,
				'placeholder'   => '',
				'position'      => 'before_message',
				'default_value' => '',
				'options'       => [],
			];

			if ( 'formychat' !== $form_mode ) {
				return $defaults;
			}

			if ( ! is_array( $dropdown ) ) {
				return $defaults;
			}

			$enabled     = wp_validate_boolean( isset( $dropdown['enabled'] ) ? $dropdown['enabled'] : false );
			$label       = sanitize_text_field( isset( $dropdown['label'] ) ? $dropdown['label'] : '' );
			$name        = sanitize_key( isset( $dropdown['name'] ) ? $dropdown['name'] : 'dropdown' );
			$name        = substr( $name, 0, 40 );
			$reserved    = [ 'name', 'email', 'phone', 'message', 'country_code', 'phone_number' ];
			if ( '' === $name || in_array( $name, $reserved, true ) ) {
				$name = 'dropdown';
			}
			$required    = wp_validate_boolean( isset( $dropdown['required'] ) ? $dropdown['required'] : false );
			$placeholder = sanitize_text_field( isset( $dropdown['placeholder'] ) ? $dropdown['placeholder'] : '' );
			$position    = sanitize_key( isset( $dropdown['position'] ) ? $dropdown['position'] : 'before_message' );
			$allowed_pos = [ 'before_message', 'after_phone', 'after_email', 'after_name' ];
			if ( ! in_array( $position, $allowed_pos, true ) ) {
				$position = 'before_message';
			}

			$raw_options = isset( $dropdown['options'] ) && is_array( $dropdown['options'] ) ? $dropdown['options'] : [];
			$options     = [];
			$max_opts    = 50;

			foreach ( $raw_options as $opt ) {
				if ( count( $options ) >= $max_opts ) {
					break;
				}
				if ( ! is_array( $opt ) ) {
					continue;
				}
				$val = isset( $opt['value'] ) ? sanitize_text_field( (string) $opt['value'] ) : '';
				$lab = isset( $opt['label'] ) ? sanitize_text_field( (string) $opt['label'] ) : $val;
				if ( '' === $val && '' === $lab ) {
					continue;
				}
				$options[] = [
					'value' => $val,
					'label' => '' !== $lab ? $lab : $val,
				];
			}

			if ( $enabled && empty( $options ) ) {
				$enabled = false;
			}

			$default_value  = sanitize_text_field( isset( $dropdown['default_value'] ) ? $dropdown['default_value'] : '' );
			$option_values  = array_column( $options, 'value' );
			if ( '' !== $default_value && ! in_array( $default_value, $option_values, true ) ) {
				$default_value = '';
			}

			return [
				'enabled'       => $enabled,
				'label'         => $label,
				'name'          => $name,
				'required'      => $required,
				'placeholder'   => $placeholder,
				'position'      => $position,
				'default_value' => $default_value,
				'options'       => $options,
			];
		}

		/**
		 * Clamps "custom" style options in a widget config back to their defaults.
		 *
		 * Free supports only the preset style options (icon size/position, form
		 * size, colors, greeting styles); "custom" values and free-form colors
		 * are a FormyChat Ultimate feature. Registered on `formychat_widget_config`,
		 * so it only shapes what the frontend receives; the saved config is never
		 * changed. FormyChat Ultimate removes this callback.
		 *
		 * @since 2.16.0
		 * @param  array $config Widget config.
		 * @return array
		 */
		public static function clamp_widget_config( $config ) {
			if ( ! is_array($config) ) {
				return $config;
			}

			$default = self::widget_config();

			// Free always opens the chat app after submit and uses the default message.
			if ( isset($config['delivery']) && is_array($config['delivery']) ) {
				$config['delivery']['redirect_after_submit'] = true;
			}
			if ( isset($config['whatsapp']) && is_array($config['whatsapp']) ) {
				$config['whatsapp']['send_message_to_whatsapp'] = true;
			}
			foreach ( self::CHANNELS as $channel ) {
				if ( isset($config[ $channel ]) && is_array($config[ $channel ]) ) {
					$config[ $channel ]['redirect_after_submit'] = true;
				}
			}

			// An uploaded Telegram icon is a FormyChat Ultimate feature; the icon color stays free.
			if ( isset($config['telegram']['icon_url']) ) {
				$config['telegram']['icon_url'] = '';
			}

			// Launcher: free icons. The color is worked out by resolve_launcher_color().
			if ( isset($config['channels']['launcher']) && is_array($config['channels']['launcher']) ) {
				$launcher_icon = isset($config['channels']['launcher']['icon']) ? $config['channels']['launcher']['icon'] : '';
				if ( ! in_array($launcher_icon, [ 'chat', 'dots', 'round' ], true) ) {
					$config['channels']['launcher']['icon'] = $default['channels']['launcher']['icon'];
				}

				// Launcher upload, Message and Header color are FormyChat Ultimate features.
				foreach ( [ 'message', 'header_color', 'icon_url' ] as $ultimate_key ) {
					if ( isset($config['channels']['launcher'][ $ultimate_key ]) ) {
						$config['channels']['launcher'][ $ultimate_key ] = '';
					}
				}

				// Symbol color: a preset is free; any other color is a FormyChat Ultimate feature.
				if ( isset($config['channels']['launcher']['glyph_color']) ) {
					$glyph_color = strtoupper( (string) sanitize_hex_color( (string) $config['channels']['launcher']['glyph_color'] ) );
					$config['channels']['launcher']['glyph_color'] = in_array( $glyph_color, self::launcher_glyph_colors(), true ) ? $glyph_color : '';
				}

				// FormyChat shows the Buttons list; other styles (Card, Icon row, ...) show as Buttons.
				$list_style = isset($config['channels']['launcher']['style']) ? $config['channels']['launcher']['style'] : '';
				if ( 'stack' !== $list_style ) {
					$config['channels']['launcher']['style'] = 'stack';
				}
			}

			// Form sources other than FormyChat, Contact Form 7 and WPForms come with FormyChat Ultimate.
			if ( isset($config['form']['mode']) && ! in_array($config['form']['mode'], [ 'formychat', 'cf7', 'wpforms' ], true) ) {
				$config['form']['mode'] = $default['form']['mode'];
			}

			if ( isset($config['whatsapp']) ) {
				$config['whatsapp']['web_version'] = $default['whatsapp']['web_version'];
				$config['whatsapp']['message_template'] = $default['whatsapp']['message_template'];
				// Multi-agent display is a FormyChat Ultimate feature.
				$config['whatsapp']['agent_mode'] = $default['whatsapp']['agent_mode'];
				// Group destination is a FormyChat Ultimate feature; never send
				// 'group' (or its invite code) to a non-Ultimate site, regardless
				// of what's stored - re-applies the moment Ultimate is activated.
				$config['whatsapp']['destination_type'] = $default['whatsapp']['destination_type'];
				$config['whatsapp']['group_invite_code'] = $default['whatsapp']['group_invite_code'];
			}

			if ( isset($config['target']) ) {
				// Page/post targeting (Exclude Pages, Exclude Posts, and both
				// "Show Only" allow-lists) is a FormyChat Ultimate feature.
				$config['target'] = $default['target'];
			}

			if ( isset($config['icon']) ) {
				$config['icon']['image_url'] = $default['icon']['image_url'];
				if ( isset($config['icon']['size']) && 'custom' === $config['icon']['size'] ) {
					$config['icon']['size'] = $default['icon']['size'];
				}
				if ( isset($config['icon']['position']) && 'custom' === $config['icon']['position'] ) {
					$config['icon']['position'] = $default['icon']['position'];
				}
			}

			if ( isset($config['cta']) ) {
				$config['cta']['color'] = $default['cta']['color'];
				$config['cta']['background_color'] = $default['cta']['background_color'];
				if ( isset($config['cta']['size']) && 'custom' === $config['cta']['size'] ) {
					$config['cta']['size'] = $default['cta']['size'];
				}
			}

			if ( isset($config['form']) ) {
				if ( isset($config['form']['size']) && 'custom' === $config['form']['size'] ) {
					$config['form']['size'] = $default['form']['size'];
				}
				$config['form']['show_country_code_field'] = $default['form']['show_country_code_field'];
				$config['form']['text_color'] = $default['form']['text_color'];
				$config['form']['background_color'] = $default['form']['background_color'];
			}

			if ( isset($config['greetings']) ) {
				$config['greetings']['style'] = $default['greetings']['style'];
				// "Go straight to the app" is a FormyChat Ultimate feature; the form and the channel list are free.
				if ( ! isset($config['greetings']['on_click']) || ! in_array($config['greetings']['on_click'], [ 'show_form', 'list' ], true) ) {
					$config['greetings']['on_click'] = $default['greetings']['on_click'];
				}

				// Wave greeting template is a FormyChat Ultimate feature.
				if ( isset($config['greetings']['template']) && 'wave' === $config['greetings']['template'] ) {
					$config['greetings']['template'] = $default['greetings']['template'];
				}

				if ( isset($config['greetings']['templates']['simple']) ) {
					$simple = &$config['greetings']['templates']['simple'];
					$default_simple = $default['greetings']['templates']['simple'];
					if ( isset($simple['heading_size']) && 'custom' === $simple['heading_size'] ) {
						$simple['heading_size'] = $default_simple['heading_size'];
					}
					if ( isset($simple['message_size']) && 'custom' === $simple['message_size'] ) {
						$simple['message_size'] = $default_simple['message_size'];
					}
					// Greeting colors (row 53) are a FormyChat Ultimate feature.
					$simple['heading_color'] = $default_simple['heading_color'];
					$simple['message_color'] = $default_simple['message_color'];
					$simple['background_color'] = $default_simple['background_color'];
					unset($simple);
				}

				if ( isset($config['greetings']['templates']['wave']) ) {
					$wave = &$config['greetings']['templates']['wave'];
					$default_wave = $default['greetings']['templates']['wave'];
					$wave['icon_url'] = $default_wave['icon_url'];
					$wave['icon_position'] = $default_wave['icon_position'];
					if ( isset($wave['heading_size']) && 'custom' === $wave['heading_size'] ) {
						$wave['heading_size'] = $default_wave['heading_size'];
					}
					if ( isset($wave['message_size']) && 'custom' === $wave['message_size'] ) {
						$wave['message_size'] = $default_wave['message_size'];
					}
					$wave['message_color'] = $default_wave['message_color'];
					$wave['background_color'] = $default_wave['background_color'];
					$wave['cta_icon_url'] = $default_wave['cta_icon_url'];
					$wave['cta_text_color'] = $default_wave['cta_text_color'];
					$wave['cta_background_color'] = $default_wave['cta_background_color'];
					$wave['cta_heading_color'] = $default_wave['cta_heading_color'];
					$wave['cta_message_color'] = $default_wave['cta_message_color'];
					if ( isset($wave['cta_heading_size']) && 'custom' === $wave['cta_heading_size'] ) {
						$wave['cta_heading_size'] = $default_wave['cta_heading_size'];
					}
					if ( isset($wave['cta_message_size']) && 'custom' === $wave['cta_message_size'] ) {
						$wave['cta_message_size'] = $default_wave['cta_message_size'];
					}
					unset($wave);
				}
			}

			return $config;
		}

		/**
		 * Strip legacy dropdown placeholders from WhatsApp template fields in widget config.
		 *
		 * @param  array $config Widget config.
		 * @return array
		 */
		public static function sanitize_whatsapp_message_templates( $config ) {
			if ( ! is_array($config) || empty($config['whatsapp']) || ! is_array($config['whatsapp']) ) {
				return $config;
			}

			foreach ( $config['whatsapp'] as $key => $val ) {
				if ( ! is_string($val) ) {
					continue;
				}
				if ( 'message_template' === $key || ( is_string($key) && preg_match('/_message_template$/', $key) ) ) {
					$config['whatsapp'][ $key ] = self::strip_legacy_dropdown_template_lines($val);
				}
			}

			return $config;
		}

		/**
		 * Strip legacy dropdown placeholder lines from a message template string.
		 *
		 * @param  string $template Raw message template.
		 * @return string
		 */
		public static function strip_legacy_dropdown_template_lines( $text ) {
			if ( ! is_string($text) || '' === $text ) {
				return $text;
			}
			$lines = preg_split('/\R/u', $text);
			$out   = [];
			foreach ( $lines as $line ) {
				if ( preg_match('/^\s*Dropdown:\s*\{[^}]+\}\s*$/u', $line) ) {
					continue;
				}
				if ( preg_match('/^\s*\{custom_select\}\s*$/u', $line) ) {
					continue;
				}
				$out[] = $line;
			}
			$joined = implode("\n", $out);
			$joined = preg_replace('/\{custom_select\}/u', '', $joined);
			return preg_replace("/\n{3,}/u", "\n\n", $joined);
		}

		/**
		 * Normalize custom_dropdown inside widget config before save.
		 *
		 * @param array $config Widget config array.
		 * @return array
		 */
		public static function prepare_config_custom_dropdown( $config ) {
			if ( ! is_array( $config ) ) {
				return $config;
			}
			if ( ! isset( $config['form'] ) || ! is_array( $config['form'] ) ) {
				$config['form'] = [];
			}
			$mode = isset( $config['form']['mode'] ) ? $config['form']['mode'] : 'formychat';

			// Only sanitize when the built-in FormyChat form is active.
			// For other form modes (CF7, WPForms, etc.) leave the existing
			// custom_dropdown data untouched so it is not wiped on save.
			if ( 'formychat' !== $mode ) {
				return $config;
			}

			$raw  = isset( $config['form']['custom_dropdown'] ) ? $config['form']['custom_dropdown'] : [];
			$config['form']['custom_dropdown'] = self::sanitize_custom_dropdown( $raw, $mode );
			return $config;
		}

		/**
		 * Upgrade a stored widget config to the current schema, in memory.
		 *
		 * Additive only: every saved value is kept (including keys this version
		 * does not know, e.g. FormyChat Ultimate keys), missing keys are filled
		 * from the defaults, and nothing is written to the database here.
		 *
		 * @since 2.16.0
		 * @param  mixed $config Stored widget config.
		 * @return array
		 */
		public static function normalize_widget_config( $config ) {
			if ( ! is_array( $config ) ) {
				$config = [];
			}

			$version = isset( $config['config_version'] ) ? absint( $config['config_version'] ) : 1;

			// Very old widgets were saved flat: {"phone":"…","form":"…","message":"…"}.
			// Copy those values into the nested keys; the original keys stay untouched.
			if ( ! isset( $config['whatsapp'] ) && ( isset( $config['phone'] ) || isset( $config['message'] ) ) ) {
				$config['whatsapp'] = [];

				if ( isset( $config['phone'] ) && is_scalar( $config['phone'] ) ) {
					$config['whatsapp']['number'] = preg_replace( '/\D/', '', (string) $config['phone'] );
				}

				if ( isset( $config['message'] ) && is_string( $config['message'] ) && '' !== $config['message'] ) {
					$config['whatsapp']['message_template'] = str_replace( '\n', "\n", $config['message'] );
				}

				if ( isset( $config['form'] ) && is_string( $config['form'] ) ) {
					$config['legacy_form_mode'] = $config['form'];
					$config['form']             = [ 'mode' => sanitize_key( $config['form'] ) ];
				}
			}

			// Only the keys added in 2.16.0 are filled in; every other missing key stays missing, as before.
			$config = self::merge_config_defaults( self::channel_defaults(), $config );

			if ( $version < self::CONFIG_VERSION ) {
				// Existing widgets keep working exactly as before: WhatsApp only, same after-submit behaviour.
				$config['channels']['enabled']                = [ 'whatsapp' ];
				$config['delivery']['redirect_after_submit'] = isset( $config['whatsapp']['send_message_to_whatsapp'] )
					? wp_validate_boolean( $config['whatsapp']['send_message_to_whatsapp'] )
					: true;
				$config['config_version']                     = self::CONFIG_VERSION;
			}

			$config = self::sanitize_channels_config( $config );

			// Keep the old WhatsApp-only key in sync for FormyChat Ultimate and cached scripts.
			$config['whatsapp']['send_message_to_whatsapp'] = (bool) $config['whatsapp']['redirect_after_submit'];

			return $config;
		}

		/**
		 * Defaults for the config keys added in 2.16.0 (channels, delivery, Telegram, new WhatsApp keys).
		 *
		 * @since 2.16.0
		 * @return array
		 */
		private static function channel_defaults() {
			$all = self::widget_config();

			return [
				'config_version' => $all['config_version'],
				'channels'       => $all['channels'],
				'delivery'       => $all['delivery'],
				'telegram'       => $all['telegram'],
				'whatsapp'       => array_intersect_key(
					$all['whatsapp'],
					array_flip( [ 'show_on_desktop', 'show_on_mobile', 'list_label', 'list_sub', 'icon_color', 'glyph_color' ] )
				),
			];
		}

		/**
		 * Fill keys missing from a saved config with their defaults.
		 *
		 * Saved values always win. Lists (agents, dropdown options, enabled
		 * channels) are kept as saved and never merged by index.
		 *
		 * @since 2.16.0
		 * @param  array $defaults Default config.
		 * @param  mixed $saved    Saved config.
		 * @return array
		 */
		public static function merge_config_defaults( $defaults, $saved ) {
			if ( ! is_array( $saved ) ) {
				return $defaults;
			}

			foreach ( $defaults as $key => $default_value ) {
				if ( ! array_key_exists( $key, $saved ) ) {
					$saved[ $key ] = $default_value;
					continue;
				}

				$default_is_map = is_array( $default_value ) && ! self::is_list_array( $default_value );
				$saved_is_map   = is_array( $saved[ $key ] ) && ( [] === $saved[ $key ] || ! self::is_list_array( $saved[ $key ] ) );

				if ( $default_is_map && $saved_is_map ) {
					$saved[ $key ] = self::merge_config_defaults( $default_value, $saved[ $key ] );
				}
			}

			return $saved;
		}

		/**
		 * Whether an array is a list (sequential 0..n-1 keys).
		 *
		 * @since 2.16.0
		 * @param  mixed $value Value.
		 * @return bool
		 */
		private static function is_list_array( $value ) {
			if ( ! is_array( $value ) ) {
				return false;
			}

			if ( [] === $value ) {
				return true;
			}

			return array_keys( $value ) === range( 0, count( $value ) - 1 );
		}

		/**
		 * Sanitize the channel parts of a widget config (channels, delivery, telegram, new WhatsApp keys).
		 *
		 * @since 2.16.0
		 * @param  array $config Widget config with defaults merged.
		 * @return array
		 */
		public static function sanitize_channels_config( $config ) {
			$defaults = self::channel_defaults();

			foreach ( [ 'channels', 'delivery', 'telegram', 'whatsapp' ] as $key ) {
				if ( ! isset( $config[ $key ] ) || ! is_array( $config[ $key ] ) ) {
					$config[ $key ] = $defaults[ $key ];
				}
			}

			if ( ! isset( $config['channels']['launcher'] ) || ! is_array( $config['channels']['launcher'] ) ) {
				$config['channels']['launcher'] = $defaults['channels']['launcher'];
			}

			$config['channels']['launcher'] = self::merge_config_defaults( $defaults['channels']['launcher'], $config['channels']['launcher'] );
			$config['telegram']             = self::merge_config_defaults( $defaults['telegram'], $config['telegram'] );
			$config['whatsapp']             = self::merge_config_defaults( $defaults['whatsapp'], $config['whatsapp'] );
			$config['delivery']             = self::merge_config_defaults( $defaults['delivery'], $config['delivery'] );

			$enabled = isset( $config['channels']['enabled'] ) && is_array( $config['channels']['enabled'] ) ? $config['channels']['enabled'] : [];
			$clean   = [];

			foreach ( $enabled as $channel ) {
				$channel = is_string( $channel ) ? sanitize_key( $channel ) : '';
				if ( in_array( $channel, self::CHANNELS, true ) && ! in_array( $channel, $clean, true ) ) {
					$clean[] = $channel;
				}
			}

			$config['channels']['enabled'] = empty( $clean ) ? [ 'whatsapp' ] : $clean;

			$launcher                          = $config['channels']['launcher'];
			$config['channels']['launcher'] = [
				'style'    => in_array( $launcher['style'], self::LIST_STYLES, true ) ? $launcher['style'] : 'stack',
				'title'    => sanitize_text_field( (string) $launcher['title'] ),
				'show_sub' => wp_validate_boolean( $launcher['show_sub'] ),
				'icon'     => sanitize_key( (string) $launcher['icon'] ),
				'color'    => sanitize_hex_color( (string) $launcher['color'] ) ? sanitize_hex_color( (string) $launcher['color'] ) : '#4F46E5',
				// Set when a color was picked on purpose (FormyChat Ultimate), so even the old default color is kept.
				'color_custom' => isset( $launcher['color_custom'] ) && wp_validate_boolean( $launcher['color_custom'] ),
				// 2.16.0 additions (rendered by FormyChat Ultimate); empty keeps today's look.
				'message'      => sanitize_text_field( (string) $launcher['message'] ),
				'header_color' => (string) sanitize_hex_color( (string) $launcher['header_color'] ),
				'icon_url'     => esc_url_raw( (string) $launcher['icon_url'] ),
				'glyph_color'  => (string) sanitize_hex_color( (string) $launcher['glyph_color'] ),
			] + $launcher;

			$config['delivery']['redirect_after_submit'] = wp_validate_boolean( $config['delivery']['redirect_after_submit'] );

			// Per-channel "open the chat app" (2.16.0). A channel without its own value
			// inherits the widget-wide one, so older widgets behave exactly as before.
			foreach ( self::CHANNELS as $channel ) {
				$config[ $channel ]['redirect_after_submit'] = array_key_exists( 'redirect_after_submit', $config[ $channel ] )
					? wp_validate_boolean( $config[ $channel ]['redirect_after_submit'] )
					: $config['delivery']['redirect_after_submit'];

				// Channel name shown next to the icon (2.16.0). Saved as false only when the admin
				// clears the name; a widget without the key keeps showing its name, as before.
				if ( array_key_exists( 'show_label', $config[ $channel ] ) ) {
					$config[ $channel ]['show_label'] = wp_validate_boolean( $config[ $channel ]['show_label'] );
				}
			}

			$telegram             = $config['telegram'];
			$telegram['mode']     = in_array( $telegram['mode'], [ 'username', 'phone' ], true ) ? $telegram['mode'] : 'username';
			$telegram['username'] = self::clean_telegram_username( $telegram['username'] );
			foreach ( [ 'country_code', 'phone' ] as $key ) {
				$telegram[ $key ] = preg_replace( '/\D/', '', (string) $telegram[ $key ] );
			}
			foreach ( [ 'new_tab', 'web_fallback', 'show_on_desktop', 'show_on_mobile' ] as $key ) {
				$telegram[ $key ] = wp_validate_boolean( $telegram[ $key ] );
			}
			foreach ( [ 'list_label', 'list_sub', 'form_title', 'form_submit' ] as $key ) {
				$telegram[ $key ] = sanitize_text_field( (string) $telegram[ $key ] );
			}
			$telegram['icon_color']  = (string) sanitize_hex_color( (string) $telegram['icon_color'] );
			$telegram['glyph_color'] = (string) sanitize_hex_color( (string) $telegram['glyph_color'] );
			$telegram['icon_url']    = esc_url_raw( (string) $telegram['icon_url'] );
			$config['telegram'] = $telegram;

			foreach ( [ 'show_on_desktop', 'show_on_mobile' ] as $key ) {
				$config['whatsapp'][ $key ] = wp_validate_boolean( $config['whatsapp'][ $key ] );
			}
			foreach ( [ 'list_label', 'list_sub' ] as $key ) {
				$config['whatsapp'][ $key ] = sanitize_text_field( (string) $config['whatsapp'][ $key ] );
			}
			$config['whatsapp']['icon_color']  = (string) sanitize_hex_color( (string) $config['whatsapp']['icon_color'] );
			$config['whatsapp']['glyph_color'] = (string) sanitize_hex_color( (string) $config['whatsapp']['glyph_color'] );

			return $config;
		}

		/**
		 * Icons the launcher can show when a widget has more than one channel.
		 *
		 * @since 2.16.0
		 * @return array Icon key => [ 'label' => string, 'url' => string, 'shape' => string, 'text' => string, 'color' => string ].
		 */
		public static function launcher_icons() {
			$icons = [
				'chat'  => [
					'label' => __( 'Chat bubble', 'social-contact-form' ),
					'url'   => FORMYCHAT_PUBLIC . '/images/launcher/chat.svg',
					// Same as the old launcher default, so widgets on default settings look the same.
					'color' => self::LEGACY_LAUNCHER_COLOR,
				],
				'dots'  => [
					'label' => __( 'Chat bubble with dots', 'social-contact-form' ),
					'url'   => FORMYCHAT_PUBLIC . '/images/launcher/dots.svg',
					'color' => '#0284C7',
				],
				'round' => [
					'label' => __( 'Round bubble', 'social-contact-form' ),
					'url'   => FORMYCHAT_PUBLIC . '/images/launcher/round.svg',
					'color' => '#F43F5E',
				],
			];

			/**
			 * Filters the launcher icons offered for widgets with several channels.
			 *
			 * @since 2.16.0
			 * @param array $icons Icon key => [ 'label' => string, 'url' => string, 'shape' => 'circle'|'squircle'|'pill', 'text' => string, 'color' => string, 'box' => int ].
			 *                     `shape`, `text`, `color` and `box` are optional; `text` is shown beside the icon on a pill launcher,
			 *                     `color` is the icon's own background color, `box` is the icon's size in % of the launcher (default 46).
			 */
			$icons = apply_filters( 'formychat_launcher_icons', $icons );

			$clean = [];
			foreach ( (array) $icons as $key => $icon ) {
				if ( ! is_array( $icon ) || empty( $icon['url'] ) ) {
					continue;
				}
				$shape = isset( $icon['shape'] ) && in_array( $icon['shape'], [ 'circle', 'squircle', 'pill' ], true ) ? $icon['shape'] : 'circle';

				$clean[ sanitize_key( $key ) ] = [
					'label' => isset( $icon['label'] ) ? sanitize_text_field( $icon['label'] ) : '',
					'url'   => esc_url_raw( $icon['url'] ),
					'shape' => $shape,
					'text'  => 'pill' === $shape && isset( $icon['text'] ) ? sanitize_text_field( $icon['text'] ) : '',
					'color' => isset( $icon['color'] ) && sanitize_hex_color( (string) $icon['color'] ) ? strtoupper( sanitize_hex_color( (string) $icon['color'] ) ) : self::LEGACY_LAUNCHER_COLOR,
					'box'   => isset( $icon['box'] ) ? max( 30, min( 80, absint( $icon['box'] ) ) ) : 46,
				];
			}

			return $clean;
		}

		/**
		 * Channel list styles this site offers in the widget editor.
		 *
		 * @since 2.16.0
		 * @return array Style key => [ 'label' => string ].
		 */
		public static function channel_list_styles() {
			$styles = [
				'stack' => [
					'label' => __( 'Buttons', 'social-contact-form' ),
				],
			];

			/**
			 * Filters the channel list styles offered in the widget editor.
			 *
			 * Keys must be listed in App::LIST_STYLES. A style other than `stack`
			 * needs a renderer in `window.formychat_list_styles` on the site.
			 *
			 * @since 2.16.0
			 * @param array $styles Style key => [ 'label' => string ].
			 */
			$styles = apply_filters( 'formychat_channel_list_styles', $styles );

			$clean = [];
			foreach ( (array) $styles as $key => $style ) {
				$key = sanitize_key( $key );
				if ( ! in_array( $key, self::LIST_STYLES, true ) ) {
					continue;
				}
				$clean[ $key ] = [
					'label' => is_array( $style ) && isset( $style['label'] ) ? sanitize_text_field( $style['label'] ) : '',
				];
			}

			return $clean;
		}

		/**
		 * Work out the launcher color the frontend shows.
		 *
		 * Each launcher icon has its own color. The saved color is kept where
		 * FormyChat showed it before: a preset other than the old default
		 * keeps showing; any other custom color keeps showing the old default,
		 * as it did. The saved config is never changed, only what the frontend
		 * receives.
		 *
		 * @since 2.16.0
		 * @param  array $config Widget config (already passed through `formychat_widget_config`).
		 * @return array
		 */
		public static function resolve_launcher_color( $config ) {
			if ( ! is_array( $config ) || ! isset( $config['channels']['launcher'] ) || ! is_array( $config['channels']['launcher'] ) ) {
				return $config;
			}

			$launcher = $config['channels']['launcher'];
			$icons    = self::launcher_icons();
			$icon     = isset( $launcher['icon'] ) && isset( $icons[ $launcher['icon'] ] ) ? $launcher['icon'] : 'chat';
			$default  = isset( $icons[ $icon ]['color'] ) ? $icons[ $icon ]['color'] : self::LEGACY_LAUNCHER_COLOR;
			$saved    = isset( $launcher['color'] ) ? strtoupper( (string) sanitize_hex_color( (string) $launcher['color'] ) ) : '';
			$color    = $default;

			$picked = ! empty( $launcher['color_custom'] ) && wp_validate_boolean( $launcher['color_custom'] );

			if ( '' !== $saved && in_array( $saved, self::launcher_colors(), true ) && ( $picked || self::LEGACY_LAUNCHER_COLOR !== $saved ) ) {
				// A preset color: free in FormyChat.
				$color = $saved;
			} elseif ( '' !== $saved && self::LEGACY_LAUNCHER_COLOR !== $saved ) {
				// Any other color keeps showing the old default, as before (FormyChat Ultimate shows it).
				$color = self::LEGACY_LAUNCHER_COLOR;
			}

			/**
			 * Filters the launcher color shown on the site.
			 *
			 * @since 2.16.0
			 * @param string $color    Color FormyChat shows: the icon's own color, or a color saved before icons had their own.
			 * @param array  $launcher Saved launcher settings (`icon`, `color`, `color_custom`, ...).
			 */
			$color = sanitize_hex_color( (string) apply_filters( 'formychat_launcher_color', $color, $launcher ) );

			$config['channels']['launcher']['color'] = $color ? $color : $default;

			return $config;
		}

		/**
		 * Icons the site shows for a widget's channels and launcher.
		 *
		 * FormyChat draws each channel's brand glyph on its color. Uploaded
		 * images (channel icons, the launcher icon for several channels) are a
		 * FormyChat Ultimate feature and come in through the filters below.
		 * The saved config is never changed; this only shapes what the site
		 * receives in `widget.resolved`.
		 *
		 * @since 2.16.0
		 * @param  array $config Widget config (already passed through `formychat_widget_config`).
		 * @return array { channel_icons: array<string,string>, channel_colors: array<string,string>, glyph_colors: array<string,string>, launcher_icon_url: string, launcher_glyph_color: string }
		 */
		public static function resolve_widget_icons( $config ) {
			$config   = is_array( $config ) ? $config : [];
			$launcher = isset( $config['channels']['launcher'] ) && is_array( $config['channels']['launcher'] ) ? $config['channels']['launcher'] : [];
			$icons    = [];
			$colors   = [];
			$glyphs   = [];

			foreach ( self::CHANNELS as $channel ) {
				/**
				 * Filters the image shown for a channel in place of its brand glyph.
				 *
				 * @since 2.16.0
				 * @param string $url     Image URL. FormyChat default: '' (the brand glyph on the channel color).
				 * @param string $channel Channel key: whatsapp | telegram.
				 * @param array  $config  Widget config.
				 */
				$url = apply_filters( 'formychat_channel_icon_url', '', $channel, $config );

				$icons[ $channel ] = is_string( $url ) ? esc_url_raw( $url ) : '';

				$saved              = isset( $config[ $channel ]['icon_color'] ) ? sanitize_hex_color( (string) $config[ $channel ]['icon_color'] ) : '';
				$colors[ $channel ] = $saved ? $saved : '';

				// Logo glyph color; '' = white.
				$glyph              = isset( $config[ $channel ]['glyph_color'] ) ? sanitize_hex_color( (string) $config[ $channel ]['glyph_color'] ) : '';
				$glyphs[ $channel ] = $glyph ? $glyph : '';
			}

			/**
			 * Filters the launcher image shown for widgets with several channels, in place of the preset icon.
			 *
			 * @since 2.16.0
			 * @param string $url      Image URL. FormyChat default: '' (the preset launcher icon).
			 * @param array  $launcher Saved launcher settings (`style`, `icon`, `color`, `icon_url`, ...).
			 * @param array  $config   Widget config.
			 */
			$launcher_url = apply_filters( 'formychat_launcher_icon_url', '', $launcher, $config );

			// A preset symbol color is free; any other color comes from FormyChat Ultimate.
			$saved_glyph  = isset( $launcher['glyph_color'] ) ? strtoupper( (string) sanitize_hex_color( (string) $launcher['glyph_color'] ) ) : '';
			$preset_glyph = in_array( $saved_glyph, self::launcher_glyph_colors(), true ) ? $saved_glyph : '';

			/**
			 * Filters the color of the launcher symbol for widgets with several channels.
			 *
			 * @since 2.16.0
			 * @param string $color    Hex color. FormyChat default: the saved preset color, else '' (white).
			 * @param array  $launcher Saved launcher settings (`style`, `icon`, `color`, `glyph_color`, ...).
			 * @param array  $config   Widget config.
			 */
			$launcher_glyph = apply_filters( 'formychat_launcher_glyph_color', $preset_glyph, $launcher, $config );
			$launcher_glyph = is_string( $launcher_glyph ) ? sanitize_hex_color( $launcher_glyph ) : '';

			return [
				'channel_icons'        => $icons,
				'channel_colors'       => $colors,
				'glyph_colors'         => $glyphs,
				'launcher_icon_url'    => is_string( $launcher_url ) ? esc_url_raw( $launcher_url ) : '',
				'launcher_glyph_color' => $launcher_glyph ? $launcher_glyph : '',
			];
		}

		/**
		 * Launcher colors offered as presets.
		 *
		 * @since 2.16.0
		 * @return string[]
		 */
		public static function launcher_colors() {
			return [ '#4F46E5', '#0F172A', '#09816D', '#229ED9', '#E11D48', '#F59E0B' ];
		}

		/**
		 * Launcher symbol colors offered as presets.
		 *
		 * @since 2.16.0
		 * @return string[]
		 */
		public static function launcher_glyph_colors() {
			return [ '#111827', '#4F46E5', '#E11D48', '#F59E0B' ];
		}

		/**
		 * Turn whatever the admin pasted (@name, t.me/name, https://telegram.me/name/) into a bare username.
		 *
		 * @since 2.16.0
		 * @param  mixed $value Raw value.
		 * @return string
		 */
		public static function clean_telegram_username( $value ) {
			$value = trim( is_scalar( $value ) ? (string) $value : '' );
			$value = preg_replace( '#^https?://#i', '', $value );
			$value = preg_replace( '#^(www\.)?(t|telegram)\.me/#i', '', $value );
			$value = ltrim( $value, '@' );
			$parts = preg_split( '#[/?\#]#', $value );

			return preg_replace( '/[^A-Za-z0-9_]/', '', (string) $parts[0] );
		}

		/**
		 * Whether a Telegram username is valid (5-32 chars, letters, digits, underscores, starts with a letter).
		 *
		 * @since 2.16.0
		 * @param  string $username Clean username.
		 * @return bool
		 */
		public static function is_valid_telegram_username( $username ) {
			return 1 === preg_match( '/^[A-Za-z][A-Za-z0-9_]{3,30}[A-Za-z0-9]$/', (string) $username );
		}
	}
}



// If function formychat() doesn't exist, create it.
if ( ! function_exists( '\formychat' ) ) {
	/**
	 * FormyChat function.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	function formychat() {
		// Get the instance of the FormyChat class.
		return new \FormyChat\App();
	}
}
