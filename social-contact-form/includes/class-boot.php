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

            /**
             * Clamp "custom" style options in widget config back to defaults.
             * FormyChat Ultimate registers its own callback at a later priority
             * to return the config un-clamped.
             *
             * @since 2.16.0
             */
            add_filter( 'formychat_widget_config', [ '\FormyChat\App', 'clamp_widget_config' ] );

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

            /**
             * The Google Sheets sync module (OAuth, API client, sync engine,
             * cron) is a FormyChat Ultimate-only feature and no longer ships
             * in the free plugin at all. FormyChat Ultimate loads its own
             * verbatim copies of these classes only when its license is
             * valid (see formychat-ultimate/includes/classes/class-hooks.php).
             *
             * @since 2.16.0
             */

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

            /**
             * Fires after the free form integrations (Contact Form 7, WPForms) are
             * loaded on the admin side. FormyChat Ultimate hooks this to load the
             * additional form integrations (Gravity Forms, Fluent Forms, Forminator,
             * Formidable Forms, Ninja Forms).
             *
             * Deferred to `plugins_loaded` (priority 20) because this file is
             * required at raw plugin-load time, before any add_action() callback
             * from a dependent plugin (e.g. FormyChat Ultimate) has had a chance
             * to register — firing do_action() here directly would run before
             * Ultimate ever hooks it.
             *
             * @since 2.16.0
             */
            add_action(
                'plugins_loaded', function () {
                    do_action( 'formychat_register_form_integrations_admin' );
                }, 20
            );
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

            /**
             * Fires after the free form integrations are loaded on the front end.
             * FormyChat Ultimate hooks this to load its additional form integrations.
             *
             * Deferred to `plugins_loaded` (priority 20) — see the matching note
             * in include_admin_files().
             *
             * @since 2.16.0
             */
            add_action(
                'plugins_loaded', function () {
                    do_action( 'formychat_register_form_integrations_public' );
                }, 20
            );
        }
    }

    // Go go go.
    $formychat = new Boot();
    $formychat->run();

}
