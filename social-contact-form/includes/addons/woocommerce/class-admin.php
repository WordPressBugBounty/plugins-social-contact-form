<?php
/**
 * WooCommerce Addon Admin.
 *
 * @package FormyChat
 * @since   2.14.0
 */

namespace FormyChat\Addons\WooCommerce;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();

if ( ! class_exists(__NAMESPACE__ . '\Admin') ) {
    /**
     * WooCommerce Addon Admin class.
     *
     * @package FormyChat
     * @since   2.14.0
     */
    class Admin extends \FormyChat\Base {


        /**
         * Constructor.
         *
         * @since 2.14.0
         */
        public function hooks() {
            $this->actions();
        }

        /**
         * Register actions.
         *
         * @since 2.14.0
         */
        public function actions() {
            add_action('formychat_admin_menu', [ $this, 'register_admin_menu' ]);
            add_action('rest_api_init', [ $this, 'register_routes' ]);
            add_filter('formychat_admin_vars', [ $this, 'formychat_admin_vars' ]);
        }

        /**
         * Advanced product-button fields that are FormyChat Ultimate only.
         *
         * Enable, whatsapp_number, country_code, and button_position stay
         * free. Everything below is forced back to its free default here
         * (not just hidden in the admin UI) so a value saved via a direct
         * REST request cannot make a locked control take effect.
         *
         * @since 2.16.0
         * @var   string[]
         */
        const PRODUCT_ADVANCED_FIELDS = [
            'button_text',
            'message_template',
            'bg_color',
            'bg_hover_color',
            'text_color',
            'text_hover_color',
            'border_radius',
            'open_new_tab',
            'hide_add_to_cart',
            'display_desktop',
            'display_mobile',
        ];

        /**
         * Clamp WooCommerce product-button settings to the free tier.
         *
         * @since  2.16.0
         * @param  array $settings Settings to clamp.
         * @return array
         */
        public function clamp_product_settings( $settings ) {
            if ( ! is_array($settings) ) {
                return $settings;
            }

            $default = $this->get_default_product_settings();

            foreach ( self::PRODUCT_ADVANCED_FIELDS as $field ) {
                if ( array_key_exists($field, $default) ) {
                    $settings[ $field ] = $default[ $field ];
                }
            }

            return $settings;
        }

        /**
         * Register admin menu.
         *
         * @return void
         */
        public function register_admin_menu() {
            add_submenu_page(
                'formychat',
                __('WooCommerce', 'social-contact-form'),
                __('WooCommerce', 'social-contact-form'),
                'manage_options',
                'formychat-woocommerce',
                [ $this, 'load_woocommerce_app' ],
                1
            );
        }

        /**
         * Render WooCommerce settings page.
         *
         * @return void
         */
        public function load_woocommerce_app() {
            echo '<div id="formychat-woocommerce"></div>';
        }

        /**
         * Register REST API routes.
         *
         * @since  2.14.0
         * @return void
         */
        public function register_routes() {
            // NOTE: The Shop Page `/woocommerce/settings` REST routes are a
            // FormyChat Ultimate-only feature (row 92) and are registered by
            // Ultimate's own WooCommerce_Shop class, not here.
            register_rest_route(
                'formychat',
                '/woocommerce/product-settings',
                [
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_product_settings' ],
					'permission_callback' => function () {
						return current_user_can('manage_options');
					},
                ]
            );

            register_rest_route(
                'formychat',
                '/woocommerce/product-settings',
                [
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_product_settings' ],
					'permission_callback' => function () {
						return current_user_can('manage_options');
					},
                ]
            );
        }

        /**
         * Get default product settings.
         *
         * @return array
         */
        private function get_default_product_settings() {
            return [
				'enabled'          => false,
				'country_code'     => \FormyChat\App::default_country_code(),
				'whatsapp_number'  => '',
				'button_position'  => 'after_add_to_cart',
				'button_text'      => 'Buy on WhatsApp',
				'message_template' => "Hello! I'd like to order {product_name} (SKU: {product_sku}) on {site_title}.",
				'bg_color'         => '#25D366',
				'bg_hover_color'   => '#21bd5b',
				'text_color'       => '#ffffff',
				'text_hover_color' => '#ffffff',
				'border_radius'    => 4,
				'open_new_tab'     => false,
				'hide_add_to_cart' => false,
				'display_desktop'  => true,
				'display_mobile'   => true,
            ];
        }

        /**
         * Get WooCommerce product settings.
         *
         * @param  \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function get_product_settings( $request ) {
            $default_settings = $this->get_default_product_settings();
            $saved_settings   = get_option('formychat_wc_product', []);
            $settings         = array_merge($default_settings, $saved_settings);
            $settings         = $this->clamp_product_settings($settings);

            return new \WP_REST_Response(
                [
					'success' => true,
					'data'    => $settings,
                ]
            );
        }

        /**
         * Save WooCommerce product settings.
         *
         * @param  \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function save_product_settings( $request ) {
            $settings = $request->get_param('settings');

            if ( null === $settings ) {
                return new \WP_REST_Response(
                    [
						'success' => false,
						'message' => __('No settings provided.', 'social-contact-form'),
                    ],
                    400
                );
            }

            $settings = $this->clamp_product_settings($settings);

            update_option('formychat_wc_product', $settings);

            do_action('formychat_wc_product_settings_saved', $settings);

            return new \WP_REST_Response(
                [
					'success' => true,
					'message' => __('Settings saved successfully.', 'social-contact-form'),
                ]
            );
        }

        /**
         * FormyChat admin vars.
         *
         * @param  array $vars
         * @return array
         */
        public function formychat_admin_vars( $vars ) {
            $default_product_settings = $this->get_default_product_settings();
            $saved_product_settings   = get_option('formychat_wc_product', []);
            $product_settings         = array_merge($default_product_settings, $saved_product_settings);

            // NOTE: `shop_settings` and `product_fields` (the placeholder
            // picker, row 99) are FormyChat Ultimate-only and are localized
            // by Ultimate's own WooCommerce_Shop class, not here.
            $vars['woocommerce'] = [
				'product_settings' => $this->clamp_product_settings($product_settings),
            ];
            return $vars;
        }
    }

    // Initialize.
    Admin::init();
}
