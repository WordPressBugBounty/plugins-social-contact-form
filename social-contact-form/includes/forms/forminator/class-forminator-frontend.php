<?php

/**
 * Frontend class.
 * Handles all Frontend requests.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace.
namespace FormyChat\Forminator;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( __NAMESPACE__ . '\Frontend' ) ) {
	/**
	 * Frontend class.
	 * Handles all Frontend requests.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Frontend extends \FormyChat\Base {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function actions() {
		}
	}

	// Initialize Message class. Only if doing Message.
	Frontend::init();
}
