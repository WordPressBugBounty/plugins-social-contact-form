<?php
/**
 * Boot file.
 * Loads all the required files.
 *
 * @package FormyChat
 * @since   1.0.0
 */

// Namespace.
namespace FormyChat;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();

if ( ! class_exists(__NAMESPACE__ . '\Boot') ) {

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
            define('FORMYCHAT_INCLUDES', plugin_dir_path(FORMYCHAT_FILE) . '/includes');
            define('FORMYCHAT_PUBLIC', plugin_dir_url(FORMYCHAT_FILE) . 'public');
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
            if ( file_exists(FORMYCHAT_INCLUDES . '/wppool/class-plugin.php') ) {
                include_once FORMYCHAT_INCLUDES . '/wppool/class-plugin.php';
            }
        }

        /**
         * Include common files.
         */
        private function include_common_file() {

            // Load deprecated class.
            include_once FORMYCHAT_INCLUDES . '/others/class-admin.php';

            // Base.
            include_once FORMYCHAT_INCLUDES . '/core/class-base.php';
            include_once FORMYCHAT_INCLUDES . '/core/class-app.php';

            // Models.
            include_once FORMYCHAT_INCLUDES . '/core/class-database.php';

            include_once FORMYCHAT_INCLUDES . '/models/class-widget.php';
            include_once FORMYCHAT_INCLUDES . '/models/class-lead.php';
            // Rest.
            include_once FORMYCHAT_INCLUDES . '/admin/class-admin-rest.php';
            // Rest.
            include_once FORMYCHAT_INCLUDES . '/compatibility/class-compatibility.php';
            // Load deprecated class.
            include_once FORMYCHAT_INCLUDES . '/others/functions.php';

            // Integrations.
            include_once FORMYCHAT_INCLUDES . '/admin/class-integrations.php';
            include_once FORMYCHAT_INCLUDES . '/admin/class-google-sheets-token.php';
            include_once FORMYCHAT_INCLUDES . '/admin/class-google-sheets-api.php';
            include_once FORMYCHAT_INCLUDES . '/admin/class-google-sheets-sync.php';
            include_once FORMYCHAT_INCLUDES . '/admin/class-google-sheets-cron.php';

            // WooCommerce Addon.
            include_once FORMYCHAT_INCLUDES . '/addons/woocommerce/class-load.php';
        }

        /**
         * Include admin files.
         */
        private function include_admin_files() {
            // Bail if not in admin.
            if ( ! is_admin() ) {
                return;
            }

            include_once FORMYCHAT_INCLUDES . '/admin/legacy/class-admin.php';

            // Load translation strings class.
            include_once FORMYCHAT_INCLUDES . '/class-strings.php';

            include_once FORMYCHAT_INCLUDES . '/admin/class-admin-assets.php';

            include_once FORMYCHAT_INCLUDES . '/admin/class-admin-hooks.php';

            // Contact Form 7.
            include_once FORMYCHAT_INCLUDES . '/forms/contact-form/class-cf7-admin.php';

            // WPForms.
            include_once FORMYCHAT_INCLUDES . '/forms/wpforms/class-wpforms-admin.php';

            // Gravity Forms.
            include_once FORMYCHAT_INCLUDES . '/forms/gravity-forms/class-gf-admin.php';

            // FluentForm.
            include_once FORMYCHAT_INCLUDES . '/forms/fluentform/class-fluentform-admin.php';

            // Formidable.
            include_once FORMYCHAT_INCLUDES . '/forms/formidable/class-formidable-admin.php';

            // Ninja.
            if ( class_exists('\NF_Abstracts_Action') ) {
                include_once FORMYCHAT_INCLUDES . '/forms/ninjaforms/class-ninjaforms-admin.php';
            }
        }

        /**
         * Include public files.
         */
        private function include_public_files() {
            include_once FORMYCHAT_INCLUDES . '/public/class-assets.php';
            include_once FORMYCHAT_INCLUDES . '/public/class-widget-form.php';
            include_once FORMYCHAT_INCLUDES . '/public/class-rest.php';

            // Contact Form 7.
            include_once FORMYCHAT_INCLUDES . '/forms/contact-form/class-cf7-frontend.php';

            // WPForms.
            include_once FORMYCHAT_INCLUDES . '/forms/wpforms/class-wpforms-frontend.php';

            // Gravity Forms.
            include_once FORMYCHAT_INCLUDES . '/forms/gravity-forms/class-gf-frontend.php';

            // FluentForm.
            include_once FORMYCHAT_INCLUDES . '/forms/fluentform/class-fluentform-frontend.php';

            // Formidable.
            include_once FORMYCHAT_INCLUDES . '/forms/formidable/class-formidable-frontend.php';
        }
    }

    // Go go go.
    $formychat = new Boot();
    $formychat->run();

}
