<?php

/**
 * FormyChat Integrations.
 */

namespace FormyChat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Integrations extends Base {
	/**
	 * Actions.
	 */
	public function actions() {
		// Register REST API Endpoint.
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );

		// Handle Google Sheets OAuth callback.
		add_action( 'admin_init', [ $this, 'handle_google_sheets_oauth_callback' ] );
	}

	/**
	 * Handle Google Sheets OAuth callback.
	 */
	public function handle_google_sheets_oauth_callback() {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'FormyChat: OAuth callback handler triggered' );

		if ( ! isset( $_GET['formychat-action'] ) || 'authenticated' !== $_GET['formychat-action'] ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'FormyChat: formychat-action=authenticated detected' );

		if ( ! isset( $_GET['integration'] ) || 'googlesheets' !== $_GET['integration'] ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'FormyChat: integration=googlesheets detected' );

		if ( ! current_user_can( 'manage_options' ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'FormyChat: User does not have manage_options capability' );
			return;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'FormyChat: User has manage_options capability' );

		// Debug: Log all GET params to see what the auth service returns.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
		error_log( 'FormyChat Google Sheets OAuth params: ' . print_r( $_GET, true ) );

		$access_token  = isset( $_GET['access_token'] ) ? sanitize_text_field( wp_unslash( $_GET['access_token'] ) ) : '';
		$refresh_token = isset( $_GET['refresh_token'] ) ? sanitize_text_field( wp_unslash( $_GET['refresh_token'] ) ) : '';
		$expires_in    = isset( $_GET['expires_in'] ) ? intval( $_GET['expires_in'] ) : 0;
		$email         = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'FormyChat: Parsed tokens - access_token: ' . ( $access_token ? 'present (' . strlen( $access_token ) . ' chars)' : 'empty' ) . ', refresh_token: ' . ( $refresh_token ? 'present (' . strlen( $refresh_token ) . ' chars)' : 'empty' ) . ', email: ' . $email );

		if ( $access_token && $refresh_token ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'FormyChat: Tokens present, saving to database...' );

			$data = [
				'access_token'  => $access_token,
				'refresh_token' => $refresh_token,
				'token_expires' => time() + $expires_in,
				'email'         => $email,
				'connected'     => true,
			];

			$saved = update_option( 'formychat_google_sheets', $data );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'FormyChat: update_option formychat_google_sheets result: ' . ( $saved ? 'true' : 'false' ) );

			// Enable the integration.
			$enabled = update_option( 'formychat_integration_google_sheets', true );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'FormyChat: update_option formychat_integration_google_sheets result: ' . ( $enabled ? 'true' : 'false' ) );

			// Set transient to show success message in frontend.
			set_transient( 'formychat_google_sheets_just_connected', true, 60 );

			// Verify saved data.
			$verify = get_option( 'formychat_google_sheets' );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
			error_log( 'FormyChat: Verified saved data: ' . print_r( $verify, true ) );

			// Redirect to clean URL.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'FormyChat: Redirecting to clean URL...' );
			wp_safe_redirect( admin_url( 'admin.php?page=formychat-integrations' ) );
			exit;
		} else {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'FormyChat: Missing tokens - access_token empty: ' . ( empty( $access_token ) ? 'yes' : 'no' ) . ', refresh_token empty: ' . ( empty( $refresh_token ) ? 'yes' : 'no' ) );
		}
	}

	/**
	 * Register REST API Endpoint.
	 */
	public function register_routes() {
		// Get integrations.
		register_rest_route( 'formychat', '/integrations', [
			'methods' => 'GET',
			'callback' => [ $this, 'get_integrations' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Update integration.
		register_rest_route( 'formychat', '/integrations', [
			'methods' => 'POST',
			'callback' => [ $this, 'update_integration' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Install plugin.
		register_rest_route( 'formychat', '/integrations/install', [
			'methods' => 'POST',
			'callback' => [ $this, 'install_integration' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets status.
		register_rest_route( 'formychat', '/integrations/google-sheets/status', [
			'methods' => 'GET',
			'callback' => [ $this, 'get_google_sheets_status' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets disconnect.
		register_rest_route( 'formychat', '/integrations/google-sheets/disconnect', [
			'methods' => 'POST',
			'callback' => [ $this, 'disconnect_google_sheets' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets save user info.
		register_rest_route( 'formychat', '/integrations/google-sheets/userinfo', [
			'methods' => 'POST',
			'callback' => [ $this, 'save_google_sheets_userinfo' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets save new access token.
		register_rest_route( 'formychat', '/integrations/google-sheets/token', [
			'methods' => 'POST',
			'callback' => [ $this, 'save_google_sheets_token' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets - List spreadsheets.
		register_rest_route( 'formychat', '/integrations/google-sheets/spreadsheets', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'list_google_spreadsheets' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets - Create spreadsheet.
		register_rest_route( 'formychat', '/integrations/google-sheets/spreadsheets', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'create_google_spreadsheet' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets - Get sync settings.
		register_rest_route( 'formychat', '/integrations/google-sheets/sync-settings', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_google_sheets_sync_settings' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets - Update sync settings.
		register_rest_route( 'formychat', '/integrations/google-sheets/sync-settings', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'update_google_sheets_sync_settings' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets - Trigger sync.
		register_rest_route( 'formychat', '/integrations/google-sheets/sync', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'trigger_google_sheets_sync' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets - Full resync.
		register_rest_route( 'formychat', '/integrations/google-sheets/resync', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'trigger_google_sheets_resync' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );

		// Google Sheets - Get sync status/stats.
		register_rest_route( 'formychat', '/integrations/google-sheets/sync-status', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_google_sheets_sync_status' ],
			'permission_callback' => [ $this, 'permission_callback' ],
		] );
	}

	/**
	 * Get Google Sheets connection status.
	 */
	public function get_google_sheets_status() {
		$data = get_option( 'formychat_google_sheets', [] );

		if ( empty( $data ) || empty( $data['connected'] ) ) {
			return rest_ensure_response( [
				'connected' => false,
			] );
		}

		// Check if token has been revoked.
		if ( ! empty( $data['revoked'] ) ) {
			return rest_ensure_response( [
				'connected' => false,
				'revoked'   => true,
				'email'     => $data['email'] ?? '',
				'picture'   => $data['picture'] ?? '',
			] );
		}

		return rest_ensure_response( [
			'connected'     => true,
			'email'         => $data['email'] ?? '',
			'picture'       => $data['picture'] ?? '',
			'access_token'  => $data['access_token'] ?? '',
			'refresh_token' => $data['refresh_token'] ?? '',
		] );
	}

	/**
	 * Disconnect Google Sheets.
	 */
	public function disconnect_google_sheets() {
		delete_option( 'formychat_google_sheets' );
		update_option( 'formychat_integration_google_sheets', false );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'Google Sheets disconnected successfully.', 'social-contact-form' ),
		] );
	}

	/**
	 * Save Google Sheets user info.
	 */
	public function save_google_sheets_userinfo( $request ) {
		$email   = $request->get_param( 'email' );
		$picture = $request->get_param( 'picture' );

		$data = get_option( 'formychat_google_sheets', [] );

		if ( empty( $data ) || empty( $data['connected'] ) ) {
			return new \WP_Error( 'not_connected', __( 'Google Sheets is not connected.', 'social-contact-form' ), [ 'status' => 400 ] );
		}

		if ( $email ) {
			$data['email'] = sanitize_email( $email );
		}
		if ( $picture ) {
			$data['picture'] = esc_url_raw( $picture );
		}

		update_option( 'formychat_google_sheets', $data );

		return rest_ensure_response( [
			'success' => true,
			'email'   => $data['email'] ?? '',
			'picture' => $data['picture'] ?? '',
		] );
	}

	/**
	 * Save Google Sheets access token (after refresh).
	 */
	public function save_google_sheets_token( $request ) {
		$access_token = $request->get_param( 'access_token' );

		$data = get_option( 'formychat_google_sheets', [] );

		if ( empty( $data ) || empty( $data['connected'] ) ) {
			return new \WP_Error( 'not_connected', __( 'Google Sheets is not connected.', 'social-contact-form' ), [ 'status' => 400 ] );
		}

		if ( $access_token ) {
			$data['access_token'] = sanitize_text_field( $access_token );
			update_option( 'formychat_google_sheets', $data );
		}

		return rest_ensure_response( [
			'success' => true,
		] );
	}

	/**
	 * List user's Google spreadsheets.
	 */
	public function list_google_spreadsheets() {
		$api    = new Google_Sheets_API();
		$result = $api->list_spreadsheets();

		if ( is_wp_error( $result ) ) {
			error_log( 'FormyChat: list_spreadsheets error - ' . $result->get_error_message() );
			return $result;
		}

		// Transform to expected format.
		$spreadsheets = [];
		if ( is_array( $result ) ) {
			foreach ( $result as $file ) {
				$spreadsheets[] = [
					'id'   => $file['id'] ?? '',
					'name' => $file['name'] ?? '',
				];
			}
		}

		return rest_ensure_response( [
			'success'      => true,
			'spreadsheets' => $spreadsheets,
		] );
	}

	/**
	 * Create a new Google spreadsheet.
	 */
	public function create_google_spreadsheet( $request ) {
		$title = $request->get_param( 'title' );

		if ( empty( $title ) ) {
			return new \WP_Error( 'missing_title', __( 'Spreadsheet title is required.', 'social-contact-form' ), [ 'status' => 400 ] );
		}

		$api    = new Google_Sheets_API();
		$result = $api->create_spreadsheet( sanitize_text_field( $title ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$sync = Google_Sheets_Sync::get_instance();

		// Setup the sheet with headers and filters.
		$sync->setup_spreadsheet( $result['id'] );

		// Save as active spreadsheet.
		Google_Sheets_Sync::update_settings( [
			'spreadsheet_id'   => $result['id'],
			'spreadsheet_name' => $result['name'],
		] );

		// Reset all leads sync status and perform full sync.
		Models\Lead::reset_sync_status();
		$sync_result = $sync->sync_leads( true );

		return rest_ensure_response( [
			'success'     => true,
			'spreadsheet' => $result,
			'synced'      => $sync_result['synced'] ?? 0,
			'stats'       => $sync->get_sync_stats(),
		] );
	}

	/**
	 * Get Google Sheets sync settings.
	 */
	public function get_google_sheets_sync_settings() {
		$settings = Google_Sheets_Sync::get_settings();

		return rest_ensure_response( [
			'success'  => true,
			'settings' => $settings,
		] );
	}

	/**
	 * Update Google Sheets sync settings.
	 */
	public function update_google_sheets_sync_settings( $request ) {
		$spreadsheet_id   = $request->get_param( 'spreadsheet_id' );
		$spreadsheet_name = $request->get_param( 'spreadsheet_name' );
		$sync_mode        = $request->get_param( 'sync_mode' );
		$sync_interval    = $request->get_param( 'sync_interval' );
		$save_only        = $request->get_param( 'save_only' );

		$settings = [];

		if ( null !== $spreadsheet_id ) {
			$settings['spreadsheet_id'] = sanitize_text_field( $spreadsheet_id );
		}
		if ( null !== $spreadsheet_name ) {
			$settings['spreadsheet_name'] = sanitize_text_field( $spreadsheet_name );
		}
		if ( null !== $sync_mode && in_array( $sync_mode, [ 'manual', 'scheduled', 'realtime' ], true ) ) {
			$settings['sync_mode'] = $sync_mode;
		}
		if ( null !== $sync_interval ) {
			$intervals = Google_Sheets_Cron::get_intervals();
			if ( isset( $intervals[ $sync_interval ] ) ) {
				$settings['sync_interval'] = $sync_interval;
			}
		}

		Google_Sheets_Sync::update_settings( $settings );

		// Update cron based on new settings.
		$cron = Google_Sheets_Cron::get_instance();
		$cron->update_cron_from_settings( array_merge( Google_Sheets_Sync::get_settings(), $settings ) );

		// If save_only, just return settings without sync.
		if ( $save_only ) {
			return rest_ensure_response( [
				'success'  => true,
				'settings' => Google_Sheets_Sync::get_settings(),
			] );
		}

		// If spreadsheet changed, set it up and sync all leads.
		$sync_result = null;
		if ( ! empty( $settings['spreadsheet_id'] ) ) {
			$sync = Google_Sheets_Sync::get_instance();

			// Setup sheet with headers and formatting.
			$setup_result = $sync->setup_spreadsheet( $settings['spreadsheet_id'] );

			if ( ! is_wp_error( $setup_result ) ) {
				// Reset all leads sync status and perform full sync.
				Models\Lead::reset_sync_status();
				$sync_result = $sync->sync_leads( true );
			}
		}

		$response = [
			'success'  => true,
			'settings' => Google_Sheets_Sync::get_settings(),
			'stats'    => Google_Sheets_Sync::get_instance()->get_sync_stats(),
		];

		if ( $sync_result ) {
			$response['synced'] = $sync_result['synced'] ?? 0;
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Trigger Google Sheets sync.
	 */
	public function trigger_google_sheets_sync() {
		$sync   = Google_Sheets_Sync::get_instance();
		$result = $sync->sync_leads( false );

		return rest_ensure_response( [
			'success' => $result['success'],
			'synced'  => $result['synced'],
			'error'   => $result['error'],
			'stats'   => $sync->get_sync_stats(),
		] );
	}

	/**
	 * Trigger full Google Sheets resync.
	 */
	public function trigger_google_sheets_resync() {
		$sync   = Google_Sheets_Sync::get_instance();
		$result = $sync->sync_leads( true );

		return rest_ensure_response( [
			'success' => $result['success'],
			'synced'  => $result['synced'],
			'error'   => $result['error'],
			'stats'   => $sync->get_sync_stats(),
		] );
	}

	/**
	 * Get Google Sheets sync status/stats.
	 */
	public function get_google_sheets_sync_status() {
		$sync  = Google_Sheets_Sync::get_instance();
		$stats = $sync->get_sync_stats();

		return rest_ensure_response( [
			'success' => true,
			'stats'   => $stats,
		] );
	}

	/**
	 * Permission callback.
	 */
	public function permission_callback() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get integrations.
	 */
	public function get_integrations() {
		$integrations = $this->integrations();

		// Add plugin status to each integration
		foreach ( $integrations as $key => $integration ) {
			if ( isset( $integration['type'] ) && 'wp_plugin' === $integration['type'] && isset( $integration['plugin'] ) ) {
				$status = $this->check_plugin_status( $integration['plugin'] );
				$integrations[ $key ]['is_plugin_installed'] = $status['is_installed'];
				$integrations[ $key ]['is_plugin_active'] = $status['is_active'];
			}
		}

		$response = [
			'categories' => $this->categories(),
			'integrations' => $integrations,
		];

		$output = apply_filters( 'formychat_integrations_rest_response', $response );

		return rest_ensure_response( $output );
	}

	/**
	 * Update integration.
	 */
	public function update_integration( $request ) {

		$integration_id = $request->get_param( 'integration_id' );
		$enabled = $request->get_param( 'enabled' );

		update_option( 'formychat_integration_' . $integration_id, $enabled );

		do_action( 'formychat_integration_updated', $integration_id, $enabled );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'Integration updated successfully.', 'social-contact-form' ),
		] );
	}

	/**
	 * Categories.
	 */
	public function categories() {
		$categories = [
			'crm_marketing' => 'CRM & Marketing',
			'email_newsletters' => 'Email & Newsletters',
			'automation_webhooks' => 'Automation & Webhooks',
			'file_storage_cloud' => 'File Storage & Cloud',
			'analytics_tracking' => 'Analytics & Tracking',
			'communication' => 'Communication',
			'others' => 'Others',
		];

		return apply_filters( 'formychat_integrations_categories', $categories );
	}


	/**
	 * Check plugin status.
	 */
	private function check_plugin_status( $plugin_file ) {
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		$is_installed = file_exists( $plugin_path );
		$is_active = false;

		if ( $is_installed && function_exists( 'is_plugin_active' ) ) {
			$is_active = is_plugin_active( $plugin_file );
		}

		return [
			'is_installed' => $is_installed,
			'is_active' => $is_active,
		];
	}

	/**
	 * Integrations.
	 */
	public function integrations() {
		$integrations = [
			[
				'id' => 'fluent-crm',
				'logo' => FORMYCHAT_PUBLIC . '/images/integrations/fluent-crm.png',
				'color' => 'rgb(119 66 230)',
				'title' => 'FluentCRM',
				'description' => 'Connect your Form leads to FluentCRM and instantly add new contacts when a form is submitted. Perfect for automated email campaigns and segmented marketing.',
				'type' => 'wp_plugin',
				'plugin' => 'fluent-crm/fluent-crm.php',
				'categories' => [ 'crm_marketing', 'email_newsletters' ],
				'status' => 'available',
				'link' => 'https://fluentcrm.com/',
				'tutorial_url' => 'https://www.youtube.com/watch?v=5VFeUIqw3cg',
				'enabled' => wp_validate_boolean( get_option( 'formychat_integration_fluent-crm', false ) ),
			],
			[
				'id' => 'mailchimp',
				'logo' => FORMYCHAT_PUBLIC . '/images/integrations/mailchimp.png',
				'color' => 'rgb(255 224 27)',
				'title' => 'MailChimp',
				'description' => 'Create Mailchimp newsletter signups directly from your form submissions. Automatically grow your audience and engage your subscribers.',
				'type' => 'api',
				'categories' => [ 'crm_marketing', 'email_newsletters' ],
				'status' => 'available',
				'link' => 'https://mailchimp.com/',
				'tutorial_url' => 'https://youtu.be/x70qZf2KWwg',
				'enabled' => wp_validate_boolean( get_option( 'formychat_integration_mailchimp', false ) ),
			],
			[
				'id' => 'google_sheets',
				'logo' => FORMYCHAT_PUBLIC . '/images/integrations/google-sheets.png',
				'color' => 'rgb(52 168 83)',
				'title' => 'Google Sheets',
				'description' => 'Sync form submissions directly to Google Sheets in real time. Perfect for reporting, analytics, or team collaboration without manual data entry.',
				'type' => 'api',
				'categories' => [ 'automation_webhooks', 'file_storage_cloud' ],
				'status' => 'available',
				'link' => 'https://sheets.google.com',
				'tutorial_url' => 'https://www.youtube.com/watch?v=bjJcWoY-8zA&list=PLd6WEu38CQSyebRIikg4qX54h-R1n35TG',
				'enabled' => wp_validate_boolean( get_option( 'formychat_integration_google_sheets', false ) ),
			],
			[
				'id' => 'mailpoet',
				'logo' => FORMYCHAT_PUBLIC . '/images/integrations/mailpoet.png',
				'title' => 'MailPoet',
				'description' => 'Add subscribers to your MailPoet mailing lists as soon as they submit a form. Automate your newsletter growth and keep your audience engaged.',
				'plugin' => 'mailpoet/mailpoet.php',
				'status' => 'upcoming',
			],
		];

		return apply_filters( 'formychat_integrations', $integrations );
	}

	/**
	 * Install integration.
	 */
	public function install_integration( $request ) {
		$integration_id = $request->get_param( 'integration_id' );
		$action = $request->get_param( 'action' ); // 'install' or 'activate'

		if ( empty( $integration_id ) ) {
			return new \WP_Error( 'missing_integration_id', __( 'Integration ID is required.', 'social-contact-form' ), [ 'status' => 400 ] );
		}

		// Get integration data
		$integrations = $this->integrations();
		$integration = null;

		foreach ( $integrations as $item ) {
			if ( $item['id'] === $integration_id ) {
				$integration = $item;
				break;
			}
		}

		if ( ! $integration ) {
			return new \WP_Error( 'integration_not_found', __( 'Integration not found.', 'social-contact-form' ), [ 'status' => 404 ] );
		}

		// Check if it's a WordPress plugin integration
		if ( 'wp_plugin' !== $integration['type'] || empty( $integration['plugin'] ) ) {
			return new \WP_Error( 'invalid_integration_type', __( 'This integration cannot be installed automatically.', 'social-contact-form' ), [ 'status' => 400 ] );
		}

		$plugin_file = $integration['plugin'];
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
		$is_installed = file_exists( $plugin_path );
		$is_active = is_plugin_active( $plugin_file );

		// Handle activation if already installed
		if ( $is_installed && ! $is_active && ( 'activate' === $action || 'install' === $action ) ) {
			// Buffer output during activation to prevent HTML contamination
			ob_start();
			$result = activate_plugin( $plugin_file );
			ob_end_clean();

			if ( is_wp_error( $result ) ) {
				return new \WP_Error( 'activation_failed', $result->get_error_message(), [ 'status' => 500 ] );
			}

			// Update integration state and enable it
			update_option( 'formychat_integration_' . $integration_id, true );

			do_action( 'formychat_plugin_activated', $integration_id, $plugin_file );

			return rest_ensure_response( [
				'success' => true,
				'action' => 'activated',
				// translators: %s is the integration title (e.g. FluentCRM, MailChimp)
				'message' => wp_sprintf( esc_html__( '%s has been activated successfully.', 'social-contact-form' ), $integration['title'] ),
				'integration' => array_merge( $integration, [
					'is_installed' => true,
					'is_activated' => true,
					'is_plugin_installed' => true,
					'is_plugin_active' => true,
					'enabled' => true,
				] ),
			] );
		}

		// Handle installation if not installed
		if ( ( ! $is_installed || ! file_exists( $plugin_dir ) ) && 'install' === $action ) {
			// Include required WordPress files
			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}
			if ( ! class_exists( 'WP_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}
			if ( ! class_exists( 'Plugin_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
			}
			if ( ! function_exists( 'request_filesystem_credentials' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			if ( ! function_exists( 'wp_filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/misc.php';
			}
			if ( ! function_exists( 'activate_plugin' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Request filesystem credentials if necessary.
			$creds = request_filesystem_credentials( '', '', false, false, null );

			// Check if we can use the filesystem, if not, throw an error.
			if ( ! WP_Filesystem( $creds ) ) {
				return new \WP_Error( 'filesystem_error', __( 'Could not access filesystem.', 'social-contact-form' ), [ 'status' => 500 ] );
			}

			// Check if plugins directory is writable
			if ( ! wp_is_writable( WP_PLUGIN_DIR ) ) {
				return new \WP_Error( 'filesystem_error', __( 'Plugins directory is not writable.', 'social-contact-form' ), [ 'status' => 500 ] );
			}

			// Get plugin slug from plugin file
			$plugin_slug = dirname( $plugin_file );

			// Try to get plugin information from WordPress repository
			$api = plugins_api( 'plugin_information', [ 'slug' => $plugin_slug ] );

			if ( is_wp_error( $api ) ) {
				// translators: %s is the error message from WordPress API
				return new \WP_Error( 'plugin_api_failed', wp_sprintf( esc_html__( 'Could not retrieve plugin information: %s', 'social-contact-form' ), $api->get_error_message() ), [ 'status' => 500 ] );
			}

			// Check if download link exists
			if ( empty( $api->download_link ) ) {
				return new \WP_Error( 'no_download_link', __( 'Plugin download link not found.', 'social-contact-form' ), [ 'status' => 500 ] );
			}

			// Create a custom silent upgrader skin to prevent any HTML output
			$skin = new class extends \WP_Upgrader_Skin { // phpcs:ignore
				public $error_message = '';

				public function feedback( $string, ...$args ) {
					// Suppress all feedback
				}
				public function header() {
					// Suppress header output
				}
				public function footer() {
					// Suppress footer output
				}
				public function error( $error ) {
					// Store error but don't output
					if ( is_wp_error( $error ) ) {
						$this->error_message = $error->get_error_message();
					} else {
						$this->error_message = (string) $error;
					}
				}
			};

			// Start comprehensive output buffering to catch any stray output
			$original_ob_level = ob_get_level();
			ob_start();
			ob_start();
			ob_start(); // Triple buffer for extra safety

			// Temporarily suppress WordPress hooks that might output HTML
			$ajax_defined_here = false;
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
				$ajax_defined_here = true;
			}

			try {
				$upgrader = new \Plugin_Upgrader( $skin );
				$install_result = $upgrader->install( $api->download_link );
			} catch ( \Exception $e ) {
				// Clean all output buffers and return error
				while ( ob_get_level() > $original_ob_level ) {
					ob_end_clean();
				}
				return new \WP_Error( 'installation_exception', $e->getMessage(), [ 'status' => 500 ] );
			}

			// Clean all output buffers
			while ( ob_get_level() > $original_ob_level ) {
				ob_end_clean();
			}

			if ( is_wp_error( $install_result ) ) {
				return new \WP_Error( 'installation_failed', $install_result->get_error_message(), [ 'status' => 500 ] );
			}

			if ( ! $install_result ) {
				$error_message = __( 'Plugin installation failed.', 'social-contact-form' );
				if ( ! empty( $skin->error_message ) ) {
					$error_message = $skin->error_message;
				}
				return new \WP_Error( 'installation_failed', $error_message, [ 'status' => 500 ] );
			}

			// Check if plugin file exists after installation
			if ( ! file_exists( $plugin_path ) ) {
				return new \WP_Error( 'installation_failed', __( 'Plugin file not found after installation.', 'social-contact-form' ), [ 'status' => 500 ] );
			}

			// Activate plugin after installation with output buffering
			ob_start();
			$activation_result = activate_plugin( $plugin_file );
			ob_end_clean();

			if ( is_wp_error( $activation_result ) ) {
				return new \WP_Error( 'activation_failed', $activation_result->get_error_message(), [ 'status' => 500 ] );
			}

			// Update integration state and enable it
			update_option( 'formychat_integration_' . $integration_id, true );

			do_action( 'formychat_plugin_installed_and_activated', $integration_id, $plugin_file );

			return rest_ensure_response( [
				'success' => true,
				'action' => 'installed_and_activated',
				// translators: %s is the integration title (e.g. FluentCRM, MailChimp)
				'message' => sprintf( __( '%s has been installed and activated successfully.', 'social-contact-form' ), $integration['title'] ),
				'integration' => array_merge( $integration, [
					'is_installed' => true,
					'is_activated' => true,
					'is_plugin_installed' => true,
					'is_plugin_active' => true,
					'enabled' => true,
				] ),
			] );
		}

		// Plugin already installed and active
		if ( $is_installed && $is_active ) {
			return rest_ensure_response( [
				'success' => true,
				// translators: %s is the integration title (e.g. FluentCRM, MailChimp)
				'message' => sprintf( __( '%s is already installed and activated.', 'social-contact-form' ), $integration['title'] ),
				'integration' => array_merge( $integration, [
					'is_installed' => true,
					'is_activated' => true,
					'is_plugin_installed' => true,
					'is_plugin_active' => true,
					'enabled' => wp_validate_boolean( get_option( 'formychat_integration_' . $integration_id, false ) ),
				] ),
			] );
		}

		return new \WP_Error( 'invalid_action', __( 'Invalid action requested.', 'social-contact-form' ), [ 'status' => 400 ] );
	}
}

Integrations::init();
