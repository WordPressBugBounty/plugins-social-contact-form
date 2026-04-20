<?php
/**
 * Public Assets Class.
 * Handles all assets for the public side.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace .
namespace FormyChat\Publics;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // phpcs:ignore Universal.PHP.RequireExitDieParentheses.Missing


if ( ! class_exists( __NAMESPACE__ . '\Assets' ) ) {
	/**
	 * Public Assets.
	 * Handles all assets for the public side.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Assets extends \FormyChat\Base {
		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function actions() {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			wp_enqueue_style( 'formychat-frontend', FORMYCHAT_PUBLIC . '/css/frontend.min.css', [], FORMYCHAT_VERSION );

			if ( defined('FORMYCHAT_FORM_ADMIN') ) {
				return;
			}

			$recaptcha_enabled = wp_validate_boolean( get_option( 'formychat_recaptcha_enabled', false ) );
			$recaptcha_site_key = get_option( 'formychat_recaptcha_site_key', '' );
			$recaptcha_version = get_option( 'formychat_recaptcha_version', 'v2' );

			$turnstile_enabled = wp_validate_boolean( get_option( 'formychat_turnstile_enabled', false ) );
			$turnstile_site_key = get_option( 'formychat_turnstile_site_key', '' );

			// Turnstile takes precedence over reCAPTCHA when both are configured (admin UI enforces one active).
			$use_turnstile_frontend = $turnstile_enabled && ! empty( $turnstile_site_key );
			$use_recaptcha_frontend = ! $use_turnstile_frontend && $recaptcha_enabled && ! empty( $recaptcha_site_key );

			if ( $use_turnstile_frontend ) {
				wp_enqueue_script( 'formychat-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true );
			}

			if ( $use_recaptcha_frontend ) {
				if ( 'v3' === $recaptcha_version ) {
					wp_enqueue_script( 'formychat-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . esc_attr( $recaptcha_site_key ), [], null, true );
				} else {
					wp_enqueue_script( 'formychat-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=formychatRecaptchaOnload&render=explicit', [], null, true );
					wp_add_inline_script( 'formychat-recaptcha', 'window.formychatRecaptchaOnload=function(){window.formychatRecaptchaReady=true;}', 'before' );
				}
			}

			$frontend_deps = [ 'jquery' ];
			if ( $use_turnstile_frontend ) {
				$frontend_deps[] = 'formychat-turnstile';
			}
			if ( $use_recaptcha_frontend ) {
				$frontend_deps[] = 'formychat-recaptcha';
			}

			wp_enqueue_script( 'formychat-frontend', FORMYCHAT_PUBLIC . '/js/frontend.min.js', $frontend_deps, FORMYCHAT_VERSION, true );

			wp_localize_script(
				'formychat-frontend',
				'formychat_vars',
				apply_filters( 'formychat_vars', [

					'ajax_url'    => admin_url( 'admin-ajax.php' ),
					'nonce'       => wp_create_nonce( 'formychat_widget_nonce' ),

					'rest_url'    => rest_url( 'formychat/v1' ),
					'rest_nonce'  => wp_create_nonce( 'wp_rest' ),

					'is_premium'      => $this->is_ultimate_active(),
					'is_form_page'    => defined('FORMYCHAT_FORM_PAGE'),

					'form_submit_label'            => __( 'Submit', 'social-contact-form' ),
					'submission_success_title'     => __( 'Successfully Submitted!', 'social-contact-form' ),
					'submission_success_btn'       => __( 'Done', 'social-contact-form' ),

					'recaptcha' => [
						'enabled' => $use_recaptcha_frontend,
						'site_key' => $recaptcha_site_key,
						'version' => $recaptcha_version,
					],

					'turnstile' => [
						'enabled' => $use_turnstile_frontend,
						'site_key' => $turnstile_site_key,
					],

					'current' => [
						'post_type' => get_post_type(),
						'post_id'   => get_the_ID(),
						'is_home'   => is_home(),
						'is_front_page' => is_front_page(),
					],
					'data' => [
						'countries' => \FormyChat\App::countries(),
						'widgets'     => \FormyChat\Models\Widget::get_active_widgets(),
						'default_config' => \FormyChat\App::widget_config(),
						'form_fields' => \FormyChat\App::form_fields(),
					],
					'site' => [
						'url' => get_site_url(),
						'name' => get_bloginfo( 'name' ),
						'description' => get_bloginfo( 'description' ),
					],
					'user' => $this->get_user(),
					'custom_tags' => \FormyChat\App::custom_tags(),
				] )
			);

			// Embed fonts.
			$font_css = \FormyChat\App::embed_fonts();
			$inline_css = apply_filters( 'formychat_inline_css', $font_css );
			if ( ! empty( $font_css ) ) {
				wp_add_inline_style( 'formychat-frontend', $inline_css );
			}
		}

		/**
		 * Get user.
		 *
		 * @return array
		 */
		public function get_user() {

			// Bail if user is not logged in.
			if ( ! is_user_logged_in() ) {
				return [];
			}

			$user = wp_get_current_user();

			$name = $user->display_name;

			if ( empty( $name ) ) {
				$name = trim( get_user_meta( $user->ID, 'first_name', true ) . ' ' . get_user_meta( $user->ID, 'last_name', true ) );
			}

			$user_data = [
				'id' => $user->ID,
				'email' => $user->user_email,
				'first_name' => get_user_meta( $user->ID, 'first_name', true ),
				'last_name' => get_user_meta( $user->ID, 'last_name', true ),
				'name' => $name,
				'phone' => get_user_meta( $user->ID, 'billing_phone', true ),
			];

			return apply_filters( 'formychat_user_data', $user_data );
		}
	}

	// Init.
	Assets::init();
}
