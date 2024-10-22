<?php
/**
 * Plugin name:      FormyChat
 * Plugin URI:       https://wppool.dev/social-contact-form-pricing/
 * Description:      Add a contact form on your website that sends form leads directly to your WhatsApp web or mobile, including WooCommerce orders, cart, etc
 * Version:          2.9.1
 * Author:           WPPOOL
 * Author URI:       https://wppool.dev
 * License:          GPLv2 or later
 * License URI:      http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      social-contact-form
 * Domain Path:      /languages
 * Requires at least: 5.0 or higher
 * Requires PHP: 5.6 or higher
 * Tested up to: 6.6
 *
 * @package FormyChat
 * @since 1.0.0
 * @author WPPOOL
 * @link https://wppool.dev
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define constants.
define('FORMYCHAT_FILE', __FILE__ );
define('FORMYCHAT_VERSION', '2.9.1' );

// Include files.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-boot.php';

/**
 * The main function responsible for returning the one true FormyChat instance to functions everywhere.
 *
 * @author WPPOOL
 * @link https://wppool.dev/social-contact-form/
 */
