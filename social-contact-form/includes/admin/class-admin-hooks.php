<?php
/**
 * Admin Hooks.
 *
 * @package FormyChat
 * @since   1.0.0
 */

// Namespace .
namespace FormyChat\Admin;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();


if ( ! class_exists(__NAMESPACE__ . '\Hooks') ) {
    /**
     * Admin class.
     *
     * @package FormyChat
     * @since   1.0.0
     */
    class Hooks extends \FormyChat\Base {


        /**
         * Constructor.
         *
         * @since 1.0.0
         */
        public function hooks() {
            $this->add_actions();
            $this->add_filters();
        }

        /**
         * Register actions.
         *
         * @since 1.0.0
         */
        public function add_actions() {
            add_action('admin_init', [ $this, 'init_appsero' ], 0);
            add_action('admin_init', [ $this, 'handle_safe_redirection' ]);
            add_action('admin_menu', [ $this, 'register_admin_menu' ], 10);
            add_action('admin_head', [ $this, 'admin_menu_styles' ]);
        }

        /**
         * Register filters.
         *
         * @since 1.0.0
         */
        public function add_filters() {
            add_filter('plugin_action_links_' . plugin_basename(FORMYCHAT_FILE), [ $this, 'plugin_action_links' ]);
        }



        /**
         * Redirect to setup page on activation.
         *
         * @return void
         */
        public function handle_safe_redirection() {
            if ( ! wp_validate_boolean(get_option('scf-setup-run')) ) {
                update_option('scf-setup-run', true);
                wp_safe_redirect(admin_url('admin.php?page=formychat'));
                // phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
                exit();
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

            // Custom CSS.
            add_submenu_page(
                'formychat',
                __('Custom CSS', 'social-contact-form'),
                __('Custom CSS', 'social-contact-form'),
                'manage_options',
                'formychat-custom-css',
                [ $this, 'load_custom_css_app' ]
            );

            add_submenu_page(
                'formychat',
                __('FormyChat Leads', 'social-contact-form'),
                __('Leads', 'social-contact-form'),
                'manage_options',
                'formychat-leads',
                [ $this, 'load_lead_app' ]
            );

            add_submenu_page(
                'formychat',
                __('Integrations', 'social-contact-form'),
                __('Integrations', 'social-contact-form') . ' <span style="background-color: #28a745; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 5px;">NEW</span>',
                'manage_options',
                'formychat-integrations',
                [ $this, 'load_integrations_app' ]
            );

            // Upgrade Now. Free only: the slug is an external URL, so
            // WordPress renders it as a plain link instead of a settings page.
            if ( ! $this->is_ultimate_active() ) {
                add_submenu_page(
                    'formychat',
                    __('Upgrade Now', 'social-contact-form'),
                    wp_sprintf(
                        '<span class="formychat-upgrade-now">%s <svg width="14" height="11" viewBox="0 0 15 12" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M5.2 0H2.8L0 3.6H4L5.2 0Z" fill="#7CFFCA"></path>
					<path d="M14.4 3.6L11.6 0H9.19995L10.4 3.6H14.4Z" fill="#219D6B"></path>
					<path d="M10.4 3.6H14.4L7.20001 12L10.4 3.6Z" fill="#24A973"></path>
					<path d="M4 3.6H0L7.2 12L4 3.6ZM5.2 0L4 3.6H10.4L9.2 0H5.2Z" fill="#3BF5A9"></path>
					<path d="M7.2 12L4 3.6H10.4L7.2 12Z" fill="#2BD08D"></path>
					</svg></span>',
                        __('Upgrade Now', 'social-contact-form')
                    ),
                    'manage_options',
                    'https://wppool.dev/formychat-pricing/?utm_source=plugin&utm_medium=admin-menu&utm_campaign=formychat',
                    '',
                    40
                );
            }

            do_action('formychat_admin_menu');
        }

        /**
         * Styles for the Upgrade Now menu item.
         *
         * @return void
         */
        public function admin_menu_styles() {
            if ( $this->is_ultimate_active() ) {
                return;
            }
            ?>
            <style>
                .formychat-upgrade-now {
                    color: #34D399;
                    text-transform: uppercase;
                    font-size: 13px;
                    line-height: 20px;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    font-family: -apple-system, "system-ui", "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                }

                .formychat-upgrade-now svg {
                    transform: translateY(1px);
                }
            </style>
            <?php
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
         * Render integration page.
         *
         * @return void
         */
        public function load_integrations_app() {
            echo '<div id="formychat-integrations"></div>';
        }

        /**
         * Render Custom CSS page.
         *
         * @return void
         */
        public function load_custom_css_app() {
            echo '<div id="formychat-custom-css"></div>';
        }

        /**
         * Add plugin action links.
         *
         * @param  array $links Plugin action links.
         * @return array
         */
        public function plugin_action_links( $links ) {
            if ( $this->is_ultimate_active() ) {
                $links[] = '<a href="' . admin_url('admin.php?page=formychat') . '">' . __('Settings', 'social-contact-form') . '</a>';
            } else {
                $links[] = '<a href="https://wppool.dev/formychat-pricing/?utm_source=plugin&utm_medium=plugin-list&utm_campaign=formychat&ref=' . rawurlencode(home_url()) . '" target="_blank" rel="noopener noreferrer" style="color: #b32d2e;">' . __('Upgrade', 'social-contact-form') . '</a>';
            }
            return $links;
        }

        /**
         * Initialize Appsero SDK.
         *
         * @return void
         */
        public function init_appsero() {
            // The SDK ships via composer, namespaced to avoid collisions with
            // other plugins bundling the same package; bail if unavailable.
            if ( ! class_exists('\FormyChat\Appsero\Client') ) {
                return;
            }

            add_filter('appsero_is_local', '__return_false');

            $appsero = new \FormyChat\Appsero\Client('9b39bac1-3b27-41d1-aeec-18fbfd4a9977', 'FormyChat', FORMYCHAT_FILE);

            // Active insights.
            $appsero->insights()->init();

            if ( function_exists('wppool_plugin_init') ) {
                $bg_image = plugin_dir_url(FORMYCHAT_FILE) . '/includes/wppool/background-image.png';
                $plugin = wppool_plugin_init('social_contact_form', $bg_image);

                if ( $plugin && is_object( $plugin ) && method_exists( $plugin, 'set_campaign' ) ) {
                    $campaign_image = plugin_dir_url( FORMYCHAT_FILE ) . '/includes/wppool/summer.png';
                    $to             = '2026-07-14 23:59:00';
                    $from           = '2026-06-24 17:00:00';
                    $cta_text       = esc_html__( 'Save Now', 'formychat' );
                    $button_link    = 'https://lnk.wppool.dev/5P9DXHz';
                    $plugin->set_campaign( $campaign_image, $to, $from, $cta_text, $button_link );
                }
            }
        }
    }


    // Initialize the plugin.
    Hooks::init();
}
