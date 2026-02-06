<?php
/**
 * WooCommerce Addon Admin.
 *
 * @package FormyChat
 * @since 2.14.0
 */

namespace FormyChat\Addons\WooCommerce;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( __NAMESPACE__ . '\Admin' ) ) {
	/**
	 * WooCommerce Addon Admin class.
	 *
	 * @package FormyChat
	 * @since 2.14.0
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
			add_action( 'formychat_admin_menu', [ $this, 'register_admin_menu' ] );
			add_action( 'rest_api_init', [ $this, 'register_routes' ] );
			add_filter( 'formychat_admin_vars', [ $this, 'formychat_admin_vars' ] );
		}

		/**
		 * Register admin menu.
		 *
		 * @return void
		 */
		public function register_admin_menu() {
			add_submenu_page(
				'formychat',
				__( 'WooCommerce', 'social-contact-form' ),
				__( 'WooCommerce', 'social-contact-form' ),
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
		 * @since 2.14.0
		 * @return void
		 */
		public function register_routes() {
			register_rest_route(
				'formychat',
				'/woocommerce/settings',
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				]
			);

			register_rest_route(
				'formychat',
				'/woocommerce/settings',
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_settings' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				]
			);

			register_rest_route(
				'formychat',
				'/woocommerce/product-settings',
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_product_settings' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
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
						return current_user_can( 'manage_options' );
					},
				]
			);
		}

		/**
		 * Get default shop settings.
		 *
		 * @return array
		 */
		private function get_default_settings() {
			return [
				'enabled'          => false,
				'country_code'     => get_option( 'formychat_country_code', '44' ),
				'whatsapp_number'  => '',
				'button_position'  => 'below',
				'button_text'      => 'Buy on WhatsApp',
				'message_template' => 'Hello! I\'d like to ask about {product_name} (SKU: {product_sku}) on {site_title}.',
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
		 * Get default product settings.
		 *
		 * @return array
		 */
		private function get_default_product_settings() {
			return [
				'enabled'          => false,
				'country_code'     => get_option( 'formychat_country_code', '44' ),
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
		 * Get WooCommerce shop settings.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function get_settings( $request ) {
			$default_settings = $this->get_default_settings();
			$saved_settings = get_option( 'formychat_wc_shop', [] );
			$settings = array_merge( $default_settings, $saved_settings );

			return new \WP_REST_Response(
				[
					'success' => true,
					'data'    => $settings,
				]
			);
		}

		/**
		 * Save WooCommerce shop settings.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function save_settings( $request ) {
			$settings = $request->get_param( 'settings' );

			if ( null === $settings ) {
				return new \WP_REST_Response(
					[
						'success' => false,
						'message' => __( 'No settings provided.', 'social-contact-form' ),
					],
					400
				);
			}

			update_option( 'formychat_wc_shop', $settings );

			do_action( 'formychat_wc_shop_settings_saved', $settings );

			return new \WP_REST_Response(
				[
					'success' => true,
					'message' => __( 'Settings saved successfully.', 'social-contact-form' ),
				]
			);
		}

		/**
		 * Get WooCommerce product settings.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function get_product_settings( $request ) {
			$default_settings = $this->get_default_product_settings();
			$saved_settings   = get_option( 'formychat_wc_product', [] );
			$settings         = array_merge( $default_settings, $saved_settings );

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
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function save_product_settings( $request ) {
			$settings = $request->get_param( 'settings' );

			if ( null === $settings ) {
				return new \WP_REST_Response(
					[
						'success' => false,
						'message' => __( 'No settings provided.', 'social-contact-form' ),
					],
					400
				);
			}

			update_option( 'formychat_wc_product', $settings );

			do_action( 'formychat_wc_product_settings_saved', $settings );

			return new \WP_REST_Response(
				[
					'success' => true,
					'message' => __( 'Settings saved successfully.', 'social-contact-form' ),
				]
			);
		}

		/**
		 * Get product fields for message template placeholders.
		 *
		 * @since 2.14.0
		 * @return array
		 */
		public function get_product_fields() {
			$fields = [
				'product_name'          => __( 'Product Name', 'social-contact-form' ),
				'product_slug'          => __( 'Product Slug', 'social-contact-form' ),
				'product_sku'           => __( 'Product SKU', 'social-contact-form' ),
				'product_price'         => __( 'Product Price', 'social-contact-form' ),
				'product_regular_price' => __( 'Regular Price', 'social-contact-form' ),
				'product_sale_price'    => __( 'Sale Price', 'social-contact-form' ),
				'product_stock_status'  => __( 'Stock Status', 'social-contact-form' ),
				'current_url'           => __( 'Current URL', 'social-contact-form' ),
				'current_title'         => __( 'Current Page Title', 'social-contact-form' ),
				'site_title'            => __( 'Site Title', 'social-contact-form' ),
				'site_url'              => __( 'Site URL', 'social-contact-form' ),
				'site_email'            => __( 'Site Email', 'social-contact-form' ),
				'date'                  => __( 'Current Date', 'social-contact-form' ),
				'time'                  => __( 'Current Time', 'social-contact-form' ),
			];

			/**
			 * Filter the product fields available for message templates.
			 *
			 * Developers can use this filter to add custom product fields.
			 *
			 * @since 2.14.0
			 * @param array $fields Key-value pairs of placeholder => label.
			 */
			return apply_filters( 'formychat_woocommerce_product_fields', $fields );
		}

		/**
		 * FormyChat admin vars.
		 *
		 * @param array $vars
		 * @return array
		 */
		public function formychat_admin_vars( $vars ) {
			$default_shop_settings    = $this->get_default_settings();
			$saved_shop_settings      = get_option( 'formychat_wc_shop', [] );
			$default_product_settings = $this->get_default_product_settings();
			$saved_product_settings   = get_option( 'formychat_wc_product', [] );

			$vars['woocommerce'] = [
				'shop_settings'    => array_merge( $default_shop_settings, $saved_shop_settings ),
				'product_settings' => array_merge( $default_product_settings, $saved_product_settings ),
				'product_fields'   => $this->get_product_fields(),
			];
			return $vars;
		}
	}

	// Initialize.
	Admin::init();
}
