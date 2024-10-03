<?php
/**
 * Admin Assets.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace .
namespace FormyChat\Admin;

use FormyChat\App;
use FormyChat\Models\Widget;
use FormyChat\Models\Lead;
use FormyChat\Models\LeadCF7;

// Exit if accessed directly.
defined('ABSPATH') || exit;


if ( ! class_exists( __NAMESPACE__ . '\Assets') ) {
	/**
	 * Admin class.
	 * Handles all admin related functionality.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Assets extends \FormyChat\Base {

		/**
		 * Actions
		 *
		 * @since 1.0.0
		 */
		public function actions() {
			add_action('admin_enqueue_scripts', [ $this, 'enqueue_scripts' ]);
		}
		/**
		 * Custom icon.
		 *
		 * @return void
		 */
		public function formychat_custom_icon() {
			$image = FORMYCHAT_PUBLIC . '/images/icon-white.svg';
			$css   = "#adminmenu div.wp-menu-image.dashicons-formychat {
				background: url({$image}) no-repeat center center !important; 
				background-size: 44% !important;
				width: 38px !important;
				height: 30px !important;
			}";

			wp_enqueue_style( 'scf_inline_style', esc_url( FORMYCHAT_PUBLIC ) . '/css/blank.css', [], microtime() );
			wp_add_inline_style('scf_inline_style', $css);
		}

		/**
		 * Enqueue admin scripts.
		 *
		 * @param string $hook Hook name.
		 * @return mixed
		 */
		public function enqueue_scripts( $hook = '' ) {

			// Always load custom icon.
			$this->formychat_custom_icon();

			// Only load for FormyChat pages.
			if ( ! in_array($hook, [ 'toplevel_page_formychat', 'formychat_page_formychat-leads' ]) ) {
				return false;
			}

			wp_enqueue_media();
			wp_enqueue_script('formychat-admin', FORMYCHAT_PUBLIC . '/js/admin.min.js', [], FORMYCHAT_VERSION, true);
			wp_enqueue_style('formychat-admin', FORMYCHAT_PUBLIC . '/css/admin.min.css', [], FORMYCHAT_VERSION);

			wp_localize_script(
				'formychat-admin',
				'formychat_admin_vars',
				[
					'rest_endpoint'    => rest_url('formychat'),
					'rest_nonce'  => wp_create_nonce('wp_rest'),
					'public'      => FORMYCHAT_PUBLIC . '/',

					'is_premium'  => $this->is_ultimate_active(),

					'cf7' => [
						'is_installed' => file_exists(WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php'),
						'is_active' => is_plugin_active('contact-form-7/wp-contact-form-7.php'),
					],

					'total' => [
						'formychat_leads'    => Lead::total(),
						'cf7_leads'    => LeadCF7::total(),
						'widgets'    => Widget::total(),
					],

					'data' => [
						'widget_config' => App::widget_config(),
					],

					'site' => [
						'url' => get_site_url(),
						'name' => get_bloginfo('name'),
						'language' => get_bloginfo('language'),
						'admin_email' => get_bloginfo('admin_email'),
					],
				]
			);

			$font_css = App::embed_fonts();
			wp_add_inline_style('formychat-admin', $font_css);
		}
	}


	// Initialize.
	Assets::init();
}
