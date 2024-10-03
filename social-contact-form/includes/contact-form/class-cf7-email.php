<?php
/**
 * Message class.
 * Handles all Message requests.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace.
namespace FormyChat\ContactForm7;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( __NAMESPACE__ . '\Mail' ) ) {
	/**
	 * Message class.
	 * Handles all Message requests.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Email extends \FormyChat\Base {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function actions() {
			add_action( 'wpcf7_skip_mail', [ $this, 'skip_mail' ] );
		}

		/**
		 * Get tab form id and disable mail.
		 *
		 * @param array $skip_mail CF7 Id and disable mail.
		 *
		 * @return mixed
		 */
		public function skip_mail( $skip_mail ) {

			$contact_form = \WPCF7_ContactForm::get_current();
			$form_id = $contact_form->id();

			$formychat_config = get_post_meta( $form_id, '_formy_chat_configuration', true );

			// Try checking skip_email, if not found, check formy_chat_mail_status.
			if ( isset( $formychat_config['skip_email'] ) && 'on' === $formychat_config['skip_email'] ) {
				$skip_mail = true;
			} elseif ( isset( $formychat_config['formy_chat_mail_status'] ) && 'on' === $formychat_config['formy_chat_mail_status'] ) {
				$skip_mail = true;
			}

			return $skip_mail;
		}
	}

	// Initialize Message class. Only if doing Message.
	Email::init();
}
