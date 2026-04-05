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

if ( ! class_exists(__NAMESPACE__ . '\Load') ) {
    /**
     * WooCommerce Addon Admin class.
     *
     * @package FormyChat
     * @since   2.14.0
     */
    class Load extends \FormyChat\Base {


        /**
         * Constructor.
         *
         * @since 2.14.0
         */
        public function hooks() {
            include_once FORMYCHAT_INCLUDES . '/addons/woocommerce/class-settings.php';
            include_once FORMYCHAT_INCLUDES . '/addons/woocommerce/class-admin.php';

            // Load frontend class on non-admin pages.
            if ( ! is_admin() ) {
                include_once FORMYCHAT_INCLUDES . '/addons/woocommerce/class-frontend.php';
            }
        }
    }

    // Initialize.
    Load::init();
}
