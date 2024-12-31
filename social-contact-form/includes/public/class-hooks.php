<?php

/**
 * Public Hooks.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace .
namespace FormyChat\Publics;

// Exit if accessed directly.
defined('ABSPATH') || exit;

// User models.
use FormyChat\Models\Lead;

if ( ! class_exists(__NAMESPACE__ . '\Hooks') ) {
	/**
	 * Public Hooks
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
			add_action( 'wp_footer', [ $this, 'print_widgets' ] );
			// Template redirect.
			add_action('template_redirect', [ $this, 'template_redirect' ]);
		}

		/**
		 * Template redirect.
		 *
		 * @return void
		 */
		public function template_redirect() {

			if ( ! isset($_GET['formychat-form']) ) {
				return;
			}

			define('FORMYCHAT_FORM_PAGE', true);

			// If 'admin' found in query.
			if ( isset($_GET['admin']) ) {
				define('FORMYCHAT_FORM_ADMIN', true);
			}

			add_filter('show_admin_bar', '__return_false');

			wp_head();

			$form = isset( $_GET['form'] ) ? sanitize_text_field( wp_unslash( $_GET['form']) ) : 'cf7';
			$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

			if ( ! $id ) {
				echo '<h2>No form found</h2>';
				wp_footer();
				exit;
			}

			do_action('formychat_before_custom_form', $form, $id);

			$number = isset( $_GET['number'] ) ? sanitize_text_field( wp_unslash( $_GET['number'] ) ) : '';
			$new_tab = isset( $_GET['new_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['new_tab'] ) ) : '';

			echo '<div class="formychat-custom-form" style="padding: 15px;"
			data-whatsapp="' . esc_attr( $number ) . '"
			data-new-tab="' . esc_attr( $new_tab ) . '"
			>';

			if ( isset( $_GET['header']) && ! empty( $_GET['header'] ) ) {
				echo '<div class="formychat-header">';
				echo wp_kses_post( sanitize_text_field( wp_unslash( $_GET['header'] ) ) );
				echo '</div>';
			}

			switch ( $form ) {
				case 'cf7':
					echo do_shortcode('[contact-form-7 id="' . $id . '"]');
					break;
				case 'wpforms':
					echo do_shortcode('[wpforms id="' . $id . '"]');
					break;
				case 'gravity':
					echo do_shortcode('[gravityform id=' . $id . ' title=false description=false ajax=true]');

					echo '<style> .formychat-header { padding-bottom: 15px; } </style>';
					break;
				default:
					echo '<h1>Invalid form</h1>';
					break;
			}

			if ( isset( $_GET['footer'] ) && ! empty( $_GET['footer'] ) ) {
				echo '<div class="formychat-footer">';
				echo wp_kses_post( sanitize_text_field( wp_unslash( $_GET['footer'] ) ) );
				echo '</div>';
			}

			echo '</div>';

			do_action('formychat_after_custom_form', $form, $id);
			$this->enqueue_form_style( $form );

			wp_footer();
			exit;
		}



		/**
		 * Add contact form to footer.
		 *
		 * @return void
		 */
		public function print_widgets() {

			// Bail if FORMYCHAT_FORM is defined.
			if ( defined('FORMYCHAT_FORM_PAGE') ) {
				return;
			}

			echo '<div id="formychat-widgets"></div>';
		}

		/**
		 * Enqueue form style.
		 *
		 * @return void
		 */
		public function enqueue_form_style( $form = 'cf7' ) {
			if ( ! file_exists( plugin_dir_path( FORMYCHAT_FILE ) . '/public/css/forms/' . $form . '.css' ) ) {
				return;
			}
			wp_enqueue_style('formychat-' . $form, FORMYCHAT_PUBLIC . '/css/forms/' . $form . '.css', []);
		}
	}


	// Run.
	Hooks::init();
}
