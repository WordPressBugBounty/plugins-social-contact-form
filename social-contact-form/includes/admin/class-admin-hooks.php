<?php
/**
 * Admin Hooks.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace .
namespace FormyChat\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;


if ( ! class_exists( __NAMESPACE__ . '\Hooks') ) {
	/**
	 * Admin class.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Hooks extends \FormyChat\Base {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function actions() {
			// Appsero.
			$this->init_appsero();

			add_action('admin_init', [ $this, 'handle_safe_redirection' ]);
			add_action('admin_menu', [ $this, 'register_admin_menu' ], 0);
			add_filter('plugin_action_links_' . plugin_basename( FORMYCHAT_FILE ), [ $this, 'plugin_action_links' ]);
		}



		/**
		 * Redirect to setup page on activation.
		 *
		 * @return void
		 */
		public function handle_safe_redirection() {
			if ( ! wp_validate_boolean( get_option( 'scf-setup-run' ) ) ) {
				update_option( 'scf-setup-run', true );
				wp_safe_redirect( admin_url( 'admin.php?page=formychat' ) );
				exit;
			}
		}

		/**
		 * Admin menu.
		 *
		 * @return void
		 */
		public function register_admin_menu() {
			add_menu_page(
				__('FormyChat', 'social-contact-form'),
				__('FormyChat', 'social-contact-form'),
				'manage_options',
				'formychat',
				[ $this, 'load_widget_app' ],
				'dashicons-formychat'
			);

			// Submenu with same slug as parent.
			add_submenu_page(
				'formychat',
				__('Floating Widgets', 'social-contact-form'),
				__('Floating Widgets', 'social-contact-form'),
				'manage_options',
				'formychat',
				[ $this, 'load_widget_app' ]
			);

			add_submenu_page(
				'formychat',
				__('FormyChat Leads', 'social-contact-form'),
				__('Leads', 'social-contact-form'),
				'manage_options',
				'formychat-leads',
				[ $this, 'load_lead_app' ]
			);

			do_action('scf_admin_menu');
		}


		/**
		 * Render settings page.
		 *
		 * @return void
		 */
		public function load_widget_app() {
			echo '<div id="formychat-widgets"></div>';
		}

		/**
		 * Render leads page.
		 *
		 * @return void
		 */
		public function load_lead_app() {
			echo '<div id="formychat-leads"></div>';
		}

		/**
		 * Add plugin action links.
		 *
		 * @param array $links Plugin action links.
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			if ( $this->is_ultimate_active() ) {
				$links[] = '<a href="' . admin_url('admin.php?page=formychat') . '">' . __('Settings', 'social-contact-form') . '</a>';
			} else {
				$links[] = '<a href="https://go.wppool.dev/2rc7/?ref=' . esc_url(home_url()) . '" target="_blank" style="color: #b32d2e;">' . __('Upgrade', 'social-contact-form') . '</a>';
			}
			return $links;
		}

		/**
		 * Initialize Appsero SDK.
		 *
		 * @return void
		 */
		public function init_appsero() {
			if ( ! class_exists('\Appsero\Client') ) {
				require_once FORMYCHAT_INCLUDES . '/appsero/src/Client.php';
			}

			add_filter('appsero_is_local', '__return_false');

			$appsero = new \Appsero\Client('9b39bac1-3b27-41d1-aeec-18fbfd4a9977', 'FormyChat', FORMYCHAT_FILE);
			$appsero->set_textdomain('social-contact-form');

			// Active insights.
			$appsero->insights()->init();

			if ( function_exists( 'wppool_plugin_init' ) ) {
				$bg_image = plugin_dir_url( FORMYCHAT_FILE ) . '/includes/wppool/background-image.png';

				$scf_plugin = wppool_plugin_init('social_contact_form', $bg_image  );
			}
		}
	}


	// Initialize the plugin.
	Hooks::init();
}
