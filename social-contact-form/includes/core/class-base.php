<?php
/**
 * Base abstract class for FormyChat
 *
 * @package FormyChat
 */

// FormyChat namespace.
namespace FormyChat;

// Exit if accessed directly.
defined('ABSPATH') || exit(1);

if ( ! class_exists(__NAMESPACE__ . 'Base') ) {

    /**
     * Base abstract class for FormyChat
     *
     * @package FormyChat
     * @since   1.0.0
     */
    abstract class Base {


        /**
         * Contains the instances of the child classes.
         *
         * @since 1.0.0
         * @var   array<object>
         */
        private static $instances = array();

        /**
         * Returns the instance of the child class.
         *
         * @since  1.0.0
         * @return object
         */
        public static function get_instance() {
            $class_name = get_called_class();

            if ( ! isset(self::$instances[ $class_name ]) ) {
                self::$instances[ $class_name ] = new $class_name();
            }

            return self::$instances[ $class_name ];
        }

        /**
         * Initializes the child class.
         *
         * @since  1.0.0
         * @return void
         */
        public static function init() {
            $instance = static::get_instance();

            $instance->hooks();
        }

        /**
         * Executes the hooks for the child class.
         *
         * @since  1.0.0
         * @return void
         */
        public function hooks() {
            // Call actions and filters if exists.
            if ( method_exists($this, 'actions') ) {
                $this->actions();
            }
            if ( method_exists($this, 'filters') ) {
                $this->filters();
            }
        }

        /**
         * Actions.
         *
         * @since  1.0.0
         * @return void
         */
        public function actions() {
        }

        /**
         * Filters.
         *
         * @since  1.0.0
         * @return void
         */
        public function filters() {
        }

        /**
         * Is ultimate license active.
         *
         * @since  1.0.0
         * @return bool
         */
        public function is_ultimate_active() {
            return apply_filters('formychat_is_ultimate', false) || apply_filters('is_scf_ultimate', false);
        }

        /**
         * Resolves the effective WhatsApp destination type.
         *
         * Free supports only 'phone'. FormyChat Ultimate adds 'group'.
         * Enforced here (not just in the admin UI) so a value saved by a
         * direct request cannot make a locked destination take effect.
         *
         * @since 2.16.0
         * @param string $saved_type Destination type from the widget config.
         * @return string
         */
        public function resolve_destination_type( $saved_type ) {
            /**
             * Filters the effective WhatsApp destination type.
             *
             * @since 2.16.0
             * @param string $type       Effective type. Free default: always 'phone'.
             * @param string $saved_type The type stored in the widget config.
             */
            return apply_filters( 'formychat_destination_type', 'phone', $saved_type );
        }

        /**
         * Resolves the effective WhatsApp target number.
         *
         * Free always returns the configured phone number. Ultimate returns
         * the group invite code when the destination type is 'group'.
         *
         * @since 2.16.0
         * @param string $phone_number Configured phone number.
         * @param array  $config       Widget WhatsApp config.
         * @return string
         */
        public function resolve_whatsapp_number( $phone_number, $config ) {
            /**
             * Filters the effective WhatsApp target (number or group invite code).
             *
             * @since 2.16.0
             * @param string $number Effective target. Free default: the phone number.
             * @param array  $config Widget WhatsApp config.
             */
            return apply_filters( 'formychat_whatsapp_number', $phone_number, $config );
        }
    }
}
