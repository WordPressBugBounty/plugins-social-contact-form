<?php
/**
 * WooCommerce Addon Settings.
 *
 * @package FormyChat
 * @since 2.14.0
 */

namespace FormyChat\Addons\WooCommerce;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( __NAMESPACE__ . '\Settings' ) ) {
	/**
	 * WooCommerce Addon Settings class.
	 *
	 * @package FormyChat
	 * @since 2.14.0
	 */
	class Settings {

		/**
		 * Shop settings.
		 *
		 * @var array
		 */
		public static $shop_settings = [
			'enabled' => false,
			'whatsapp_number' => '',
			'button_position' => 'below',
			'button_text' => 'Buy on WhatsApp',
			'message_template' => 'Hello! I\'d like to ask about {productName} (SKU: {productSku}) on {siteTitle}.',
			'bg_color' => '#25D366',
			'bg_hover_color' => '#21bd5b',
			'text_color' => '#ffffff',
			'text_hover_color' => '#ffffff',
			'border_radius' => 4,
			'open_new_tab' => false,
			'hide_add_to_cart' => false,
			'display_desktop' => true,
			'display_mobile' => true,
		];

		// public static $product_settings = [];
		// public static $cart_settings = [];
		// public static $checkout_settings = [];

		/**
		 * Get Shop settings.
		 *
		 * @return array
		 */
		public static function get_shop_settings() {
			$default_settings = self::$shop_settings;
			$saved_settings = get_option( 'formychat_wc_shop', [] );
			return array_merge( $default_settings, $saved_settings );
		}
	}
}
