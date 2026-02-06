<?php
/**
 * WooCommerce Addon Frontend.
 *
 * @package FormyChat
 * @since 2.14.0
 */

namespace FormyChat\Addons\WooCommerce;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( __NAMESPACE__ . '\Frontend' ) ) {
	/**
	 * WooCommerce Addon Frontend class.
	 *
	 * @package FormyChat
	 * @since 2.14.0
	 */
	class Frontend extends \FormyChat\Base {

		/**
		 * Shop settings.
		 *
		 * @var array
		 */
		private $shop_settings = [];

		/**
		 * Product settings.
		 *
		 * @var array
		 */
		private $product_settings = [];

		/**
		 * Constructor.
		 *
		 * @since 2.14.0
		 */
		public function hooks() {
			$this->load_settings();

			// Only proceed if at least one feature is enabled.
			if ( ! $this->shop_settings['enabled'] && ! $this->product_settings['enabled'] ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
			add_action( 'wp_head', [ $this, 'maybe_hide_add_to_cart' ] );

			// Inject inline product data for shortcode products.
			if ( $this->shop_settings['enabled'] ) {
				add_action( 'woocommerce_after_shop_loop_item', [ $this, 'inject_product_data' ], 99 );
			}
		}

		/**
		 * Load settings.
		 *
		 * @since 2.14.0
		 */
		private function load_settings() {
			$this->shop_settings    = $this->get_shop_settings();
			$this->product_settings = $this->get_product_settings();
		}

		/**
		 * Get default shop settings.
		 *
		 * @return array
		 */
		private function get_default_shop_settings() {
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
		 * Get shop settings.
		 *
		 * @return array
		 */
		private function get_shop_settings() {
			$default  = $this->get_default_shop_settings();
			$saved    = get_option( 'formychat_wc_shop', [] );
			return array_merge( $default, $saved );
		}

		/**
		 * Get product settings.
		 *
		 * @return array
		 */
		private function get_product_settings() {
			$default = $this->get_default_product_settings();
			$saved   = get_option( 'formychat_wc_product', [] );
			return array_merge( $default, $saved );
		}

		/**
		 * Check if we should load assets on current page.
		 *
		 * @return bool
		 */
		private function should_load_assets() {
			// If shop is enabled, always load - products can appear anywhere via shortcodes/blocks.
			// JS will detect and inject buttons only where products exist.
			if ( $this->shop_settings['enabled'] ) {
				return true;
			}

			// Product page enabled and on single product.
			if ( $this->product_settings['enabled'] && is_product() ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if current page has WooCommerce blocks.
		 *
		 * @return bool
		 */
		private function has_woocommerce_blocks() {
			global $post;

			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
				return false;
			}

			$wc_blocks = [
				'woocommerce/all-products',
				'woocommerce/product-collection',
				'woocommerce/products-by-attribute',
				'woocommerce/product-best-sellers',
				'woocommerce/product-new',
				'woocommerce/product-on-sale',
				'woocommerce/product-top-rated',
				'woocommerce/handpicked-products',
			];

			foreach ( $wc_blocks as $block ) {
				if ( has_block( $block, $post ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Enqueue frontend assets.
		 *
		 * @since 2.14.0
		 */
		public function enqueue_assets() {
			if ( ! $this->should_load_assets() ) {
				return;
			}

			// Enqueue styles (only if file exists).
			$css_path = plugin_dir_path( FORMYCHAT_FILE ) . 'public/css/woocommerce.min.css';
			if ( file_exists( $css_path ) ) {
				wp_enqueue_style(
					'formychat-woocommerce',
					FORMYCHAT_PUBLIC . '/css/woocommerce.min.css',
					[],
					FORMYCHAT_VERSION
				);
			}

			// Enqueue scripts.
			wp_enqueue_script(
				'formychat-woocommerce',
				FORMYCHAT_PUBLIC . '/js/woocommerce.min.js',
				[],
				FORMYCHAT_VERSION,
				true
			);

			// Pass settings to JavaScript.
			wp_localize_script(
				'formychat-woocommerce',
				'formychat_woo_vars',
				$this->get_js_vars()
			);
		}

		/**
		 * Get JavaScript variables.
		 *
		 * @return array
		 */
		private function get_js_vars() {
			return [
				'shop'    => $this->shop_settings,
				'product' => $this->product_settings,
				'site'    => [
					'title' => get_bloginfo( 'name' ),
					'url'   => get_site_url(),
					'email' => get_bloginfo( 'admin_email' ),
				],
				'selectors' => [
					// Shortcode product selectors (li.product is the actual product item).
					'shortcodeProduct'  => 'ul.products > li.product',
					// Block product selectors (wc-block-product for new blocks).
					'blockProduct'      => 'li.wc-block-product',
					// Single product selectors.
					'singleProduct'     => '.single-product .product',
					'addToCartButton'   => '.add_to_cart_button, .single_add_to_cart_button',
					'productTitle'      => '.woocommerce-loop-product__title, .wp-block-post-title, h2 a',
					'productPrice'      => '.price .woocommerce-Price-amount, .wc-block-components-product-price .woocommerce-Price-amount',
					'productLink'       => 'a.woocommerce-LoopProduct-link, .wp-block-post-title a, a[href*="/product/"]',
				],
				'restUrl'   => rest_url( 'wc/store/v1/products' ),
				'device'    => wp_is_mobile() ? 'mobile' : 'desktop',
			];
		}

		/**
		 * Inject inline product data JSON for shortcode products.
		 *
		 * @since 2.14.0
		 */
		public function inject_product_data() {
			global $product;

			if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
				return;
			}

			$data = [
				'id'            => $product->get_id(),
				'name'          => $product->get_name(),
				'price'         => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_price() ) ) ),
				'regular_price' => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_regular_price() ) ) ),
				'sale_price'    => $product->get_sale_price() ? html_entity_decode( wp_strip_all_tags( wc_price( $product->get_sale_price() ) ) ) : '',
				'sku'           => $product->get_sku(),
				'stock_status'  => $product->get_stock_status(),
				'url'           => get_permalink( $product->get_id() ),
				'type'          => $product->get_type(),
			];

			printf(
				'<script type="application/json" class="formychat-product-data">%s</script>',
				wp_json_encode( $data )
			);
		}

		/**
		 * Maybe hide Add to Cart button via CSS.
		 *
		 * @since 2.14.0
		 */
		public function maybe_hide_add_to_cart() {
			if ( ! $this->should_load_assets() ) {
				return;
			}

			$hide_shop    = $this->shop_settings['enabled'] && $this->shop_settings['hide_add_to_cart'];
			$hide_product = $this->product_settings['enabled'] && $this->product_settings['hide_add_to_cart'];

			if ( ! $hide_shop && ! $hide_product ) {
				return;
			}

			echo '<style id="formychat-woo-hide-atc">';

			if ( $hide_shop ) {
				echo '.products .add_to_cart_button,
					  .wc-block-grid__product .add_to_cart_button,
					  .wc-block-components-product-button { display: none !important; }';
			}

			if ( $hide_product ) {
				echo '.single-product .single_add_to_cart_button,
					  .single-product form.cart .button[type="submit"] { display: none !important; }';
			}

			echo '</style>';
		}
	}

	// Initialize.
	Frontend::init();
}
