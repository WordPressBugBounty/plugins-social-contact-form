<?php
/**
 * Google Sheets Token Manager.
 *
 * Handles automatic token refresh to prevent refresh_token revocation.
 * Refresh tokens get revoked if unused for 6 months, so we refresh every 3 days.
 *
 * @package FormyChat
 * @since 1.0.0
 */

namespace FormyChat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Sheets Token Manager class.
 */
class Google_Sheets_Token extends Base {

	/**
	 * Transient name for tracking last refresh.
	 */
	const REFRESH_TRANSIENT = 'formychat_google_sheets_last_refresh';

	/**
	 * Refresh interval in seconds (3 days).
	 */
	const REFRESH_INTERVAL = 3 * DAY_IN_SECONDS;

	/**
	 * Auth server refresh endpoint.
	 */
	const REFRESH_ENDPOINT = 'https://auth-staging.wppool.dev/refresh/google/formychat-sheet-access';

	/**
	 * Actions.
	 */
	public function actions() {
		add_action( 'admin_init', [ $this, 'maybe_refresh_token' ] );
	}

	/**
	 * Check if token needs refresh and refresh if needed.
	 */
	public function maybe_refresh_token() {
		// Only run for users who can manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if refresh is needed (transient expired).
		if ( false !== get_transient( self::REFRESH_TRANSIENT ) ) {
			return;
		}

		// Get Google Sheets data.
		$data = get_option( 'formychat_google_sheets', [] );

		// Skip if not connected or no refresh token.
		if ( empty( $data['connected'] ) || empty( $data['refresh_token'] ) ) {
			return;
		}

		// Skip if already revoked.
		if ( ! empty( $data['revoked'] ) ) {
			return;
		}

		// Refresh the token.
		$this->refresh_token( $data );
	}

	/**
	 * Refresh the access token.
	 *
	 * @param array $data Current Google Sheets data.
	 * @return bool True on success, false on failure.
	 */
	public function refresh_token( $data ) {
		$refresh_token = $data['refresh_token'] ?? '';

		if ( empty( $refresh_token ) ) {
			return false;
		}

		$response = wp_remote_post( self::REFRESH_ENDPOINT, [
			'timeout' => 30,
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			],
			'body'    => wp_json_encode( [
				'refresh_token' => $refresh_token,
			] ),
		] );

		if ( is_wp_error( $response ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'FormyChat: Failed to refresh Google Sheets token - ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$result      = json_decode( $body, true );

		// Handle revoked/invalid refresh token.
		if ( 401 === $status_code || 400 === $status_code ) {
			$this->mark_as_revoked( $data );
			return false;
		}

		if ( 200 !== $status_code || empty( $result['access_token'] ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'FormyChat: Failed to refresh Google Sheets token - Status: ' . $status_code );
			return false;
		}

		// Update access token.
		$data['access_token']  = sanitize_text_field( $result['access_token'] );
		$data['token_expires'] = time() + ( $result['expires_in'] ?? 3600 );

		// Clear revoked flag if it was set.
		unset( $data['revoked'] );

		update_option( 'formychat_google_sheets', $data );

		// Set transient to prevent refresh for 3 days.
		set_transient( self::REFRESH_TRANSIENT, time(), self::REFRESH_INTERVAL );

		return true;
	}

	/**
	 * Mark the connection as revoked.
	 *
	 * @param array $data Current Google Sheets data.
	 */
	private function mark_as_revoked( $data ) {
		$data['revoked']    = true;
		$data['revoked_at'] = time();

		update_option( 'formychat_google_sheets', $data );

		// Clear refresh transient so we don't keep trying.
		delete_transient( self::REFRESH_TRANSIENT );

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'FormyChat: Google Sheets token has been revoked. User needs to reconnect.' );
	}

	/**
	 * Force refresh token (for manual trigger).
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function force_refresh() {
		$data = get_option( 'formychat_google_sheets', [] );

		if ( empty( $data['refresh_token'] ) ) {
			return false;
		}

		$instance = new self();
		return $instance->refresh_token( $data );
	}
}

Google_Sheets_Token::init();
