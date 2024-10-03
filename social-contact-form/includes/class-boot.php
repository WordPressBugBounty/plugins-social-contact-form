<?php
/**
 * Boot file.
 * Loads all the required files.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace.
namespace FormyChat;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( __NAMESPACE__ . '\Boot' ) ) {

	class Boot {
		/**
		 * Constructor.
		 */
		public function run() {
			$this->define_constants();
			$this->includes();
		}

		/**
		 * Define constants.
		 */
		private function define_constants() {
			// Other constants.
			define( 'FORMYCHAT_INCLUDES', plugin_dir_path( FORMYCHAT_FILE ) . '/includes' );
			define( 'FORMYCHAT_PUBLIC', plugin_dir_url( FORMYCHAT_FILE ) . 'public' );
		}

		/**
		 * Include files.
		 */
		private function includes() {
			$this->include_libs();
			$this->include_common_file();
			$this->include_admin_files();
			$this->include_public_files();
		}

		/**
		 * Include libraries.
		 */
		private function include_libs() {
			// Require files.
			if ( file_exists( FORMYCHAT_INCLUDES . '/wppool/class-plugin.php' ) ) {
				require_once FORMYCHAT_INCLUDES . '/wppool/class-plugin.php';
			}
		}

		/**
		 * Include common files.
		 */
		private function include_common_file() {

			// Load deprecated class.
			require_once FORMYCHAT_INCLUDES . '/others/class-admin.php';

			// Base.
			require_once FORMYCHAT_INCLUDES . '/core/class-base.php';
			require_once FORMYCHAT_INCLUDES . '/core/class-app.php';

			// Models.
			require_once FORMYCHAT_INCLUDES . '/core/class-database.php';

			require_once FORMYCHAT_INCLUDES . '/models/class-widget.php';
			require_once FORMYCHAT_INCLUDES . '/models/class-lead.php';
			require_once FORMYCHAT_INCLUDES . '/models/class-lead-cf7.php';
			// Rest.
			require_once FORMYCHAT_INCLUDES . '/admin/class-admin-rest.php';
		}

		/**
		 * Include admin files.
		 */
		private function include_admin_files() {
			// Bail if not in admin.
			if ( ! is_admin() ) {
				return;
			}

			require_once FORMYCHAT_INCLUDES . '/admin/class-admin-assets.php';

			require_once FORMYCHAT_INCLUDES . '/admin/class-admin-hooks.php';

			// Contact Form 7 Settings.
			require_once FORMYCHAT_INCLUDES . '/contact-form/class-cf7-settings.php';
		}

		/**
		 * Include public files.
		 */
		private function include_public_files() {
			require_once FORMYCHAT_INCLUDES . '/public/class-assets.php';
			require_once FORMYCHAT_INCLUDES . '/public/class-rest.php';

			// Contact Form 7 Email.
			require_once FORMYCHAT_INCLUDES . '/contact-form/class-cf7-email.php';
		}
	}

	// Go go go.
	$formychat = new Boot();
	$formychat->run();

}
