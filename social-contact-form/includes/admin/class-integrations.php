<?php

/**
 * FormyChat Integrations.
 */

namespace FormyChat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
	exit();
}

// phpcs:disable PHPCompatibility.Operators.NewOperators -- Plugin requires PHP 7.0+; ?? is valid.

class Integrations extends Base {
	/**
	 * Actions.
	 */
	public function actions() {
		// Register REST API Endpoint.
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );

		/**
		 * The Google Sheets OAuth callback and its REST routes are a
		 * FormyChat Ultimate-only feature (entire module relocated).
		 * FormyChat Ultimate registers its own `admin_init` callback and
		 * routes only when its license is valid — see
		 * formychat-ultimate/includes/integrations/google-sheets/class-google-sheets-rest.php.
		 *
		 * @since 2.16.0
		 */
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
			'all' => __( 'All', 'social-contact-form' ),
			'crm_marketing' => __( 'CRM & Marketing', 'social-contact-form' ),
			'email_newsletters' => __( 'Email & Newsletters', 'social-contact-form' ),
			'automation_webhooks' => __( 'Automation & Webhooks', 'social-contact-form' ),
			'file_storage_cloud' => __( 'File Storage & Cloud', 'social-contact-form' ),
			'analytics_tracking' => __( 'Analytics & Tracking', 'social-contact-form' ),
			'communication' => __( 'Communication', 'social-contact-form' ),
			'others' => __( 'Others', 'social-contact-form' ),
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
				'title' => __( 'FluentCRM', 'social-contact-form' ),
				'description' => __( 'Connect your Form leads to FluentCRM and instantly add new contacts when a form is submitted. Perfect for automated email campaigns and segmented marketing.', 'social-contact-form' ),
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
				'title' => __( 'MailChimp', 'social-contact-form' ),
				'description' => __( 'Create Mailchimp newsletter signups directly from your form submissions. Automatically grow your audience and engage your subscribers.', 'social-contact-form' ),
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
				'title' => __( 'Google Sheets', 'social-contact-form' ),
				'description' => __( 'Sync form submissions directly to Google Sheets in real time. Perfect for reporting, analytics, or team collaboration without manual data entry.', 'social-contact-form' ),
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
				'title' => __( 'MailPoet', 'social-contact-form' ),
				'description' => __( 'Add subscribers to your MailPoet mailing lists as soon as they submit a form. Automate your newsletter growth and keep your audience engaged.', 'social-contact-form' ),
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
		$confirmed = $request->has_param( 'confirm' ) ? wp_validate_boolean( $request->get_param( 'confirm' ) ) : false;

		if ( empty( $integration_id ) ) {
			return new \WP_Error( 'missing_integration_id', __( 'Integration ID is required.', 'social-contact-form' ), [ 'status' => 400 ] );
		}

		if ( ! $confirmed ) {
			return new \WP_Error( 'confirmation_required', __( 'This action must be explicitly confirmed.', 'social-contact-form' ), [ 'status' => 400 ] );
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
