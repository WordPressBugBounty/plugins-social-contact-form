<?php

/**
 * Form class.
 * Handles all Form requests.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace.
namespace FormyChat\Forminator;

// Exit if accessed directly.
defined('ABSPATH') || exit;


if ( ! class_exists(__NAMESPACE__ . '\Admin') ) {
	/**
	 * Form class.
	 * Handles all Form requests.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Admin extends \FormyChat\Base {

		/**
		 * Register actions.
		 *
		 * @since 1.0.0
		 */
		public function actions() {
		}
	}

	// Initialize Form class. Only if doing Form.
	Admin::init();
}
