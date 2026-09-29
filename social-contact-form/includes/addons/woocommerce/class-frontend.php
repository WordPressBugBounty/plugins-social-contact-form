<?php
/**
 * WooCommerce Addon Frontend.
 *
 * @package FormyChat
 * @since   2.14.0
 */

namespace FormyChat\Addons\WooCommerce;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();

if ( ! class_exists(__NAMESPACE__ . '\Frontend') ) {
    /**
     * WooCommerce Addon Frontend class.
     *
     * @package FormyChat
     * @since   2.14.0
     */
    class Frontend extends \FormyChat\Base {


        /**
         * Product settings.
         *
         * @var array
         */
        private $product_settings = [];

        /**
         * Advanced product-button fields that are FormyChat Ultimate only.
         *
         * Mirrors `Admin::PRODUCT_ADVANCED_FIELDS`, kept in sync so a
         * value saved before this clamp existed never renders on the
         * frontend either.
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
         * Constructor.
         *
         * @since 2.14.0
         */
        public function hooks() {
            $this->load_settings();

            // NOTE: Shop Page (row 92) is a FormyChat Ultimate-only feature;
            // this class now only ever handles the Product Page button.
            if ( ! $this->product_settings['enabled'] ) {
                return;
            }

            add_action('wp_enqueue_scripts', [ $this, 'enqueue_assets' ]);
            add_action('wp_head', [ $this, 'maybe_hide_add_to_cart' ]);
        }

        /**
         * Load settings.
         *
         * @since 2.14.0
         */
        private function load_settings() {
            $this->product_settings = $this->get_product_settings();
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
         * Clamp WooCommerce product-button settings to the free tier.
         *
         * Defense on read: even if a Pro value was saved to the option
         * before this clamp existed (or via a direct request), it never
         * reaches the frontend render.
         *
         * @since  2.16.0
         * @param  array $settings Settings to clamp.
         * @return array
         */
        private function clamp_product_settings( $settings ) {
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
         * Get product settings.
         *
         * @return array
         */
        private function get_product_settings() {
            $default = $this->get_default_product_settings();
            $saved   = get_option('formychat_wc_product', []);
            $settings = array_merge($default, $saved);
            return $this->clamp_product_settings($settings);
        }

        /**
         * Check if we should load assets on current page.
         *
         * @return bool
         */
        private function should_load_assets() {
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

            if ( ! $post || ! is_a($post, 'WP_Post') ) {
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
                if ( has_block($block, $post) ) {
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
            $css_path = plugin_dir_path(FORMYCHAT_FILE) . 'public/css/woocommerce.min.css';
            if ( file_exists($css_path) ) {
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
				'product' => $this->product_settings,
				'site'    => [
					'title' => get_bloginfo('name'),
					'url'   => get_site_url(),
					'email' => get_bloginfo('admin_email'),
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
				'restUrl'   => rest_url('wc/store/v1/products'),
				'device'    => wp_is_mobile() ? 'mobile' : 'desktop',
            ];
        }

        /**
         * Maybe hide Add to Cart button via CSS.
         *
         * NOTE: `hide_add_to_cart` (row 105) is a FormyChat Ultimate-only
         * feature; free's `product_settings` is always clamped to `false`
         * for this field (see `clamp_product_settings()`), so this is a
         * structural no-op for free installs. Kept as-is (rather than
         * removed) so it continues to work unmodified once Ultimate
         * supplies a real value through the same filter/option shape.
         *
         * @since 2.14.0
         */
        public function maybe_hide_add_to_cart() {
            if ( ! $this->should_load_assets() ) {
                return;
            }

            $hide_product = $this->product_settings['enabled'] && $this->product_settings['hide_add_to_cart'];

            if ( ! $hide_product ) {
                return;
            }

            echo '<style id="formychat-woo-hide-atc">';
            echo '.single-product .single_add_to_cart_button,
				  .single-product form.cart .button[type="submit"] { display: none !important; }';
            echo '</style>';
        }
    }

    // Initialize.
    Frontend::init();
}
