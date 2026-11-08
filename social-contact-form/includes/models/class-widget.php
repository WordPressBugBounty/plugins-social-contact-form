<?php
/**
 * Widget model.
 *
 * @package FormyChat
 * @since   1.0.0
 */

// Namespace .
namespace FormyChat\Models;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();


if ( ! class_exists(__NAMESPACE__ . '\Widget') ) {
    /**
     * Widget model.
     *
     * @package FormyChat
     * @since   1.0.0
     */
    class Widget {


        /**
         * Get all widgets.
         *
         * @return array
         */
        public static function get_all() {
            global $wpdb;

            $widgets = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}scf_widgets WHERE `deleted_at` IS NULL ORDER BY id DESC"); // db call ok; no-cache ok.

            if ( $widgets && ! empty($widgets) ) {
                foreach ( $widgets as $widget ) {
                    $widget = self::wrap($widget);
                }

                return $widgets;
            }

            return [];
        }

        /**
         * Active widgets.
         */
        public static function get_active_widgets() {
            global $wpdb;
            $active_widgets = $wpdb->get_results("SELECT `id`, `name`, `config` FROM {$wpdb->prefix}scf_widgets WHERE `is_active` IS TRUE AND `deleted_at` IS NULL");  // db call ok; no-cache ok.

            // Bail if no active widgets.
            if ( ! $active_widgets ) {
                return;
            }

            // Format widget data.
            $widgets = [];
            foreach ( $active_widgets as $widget ) {
                $config = json_decode($widget->config, true);
                if ( ! is_array($config) ) {
                    $config = [];
                }
                $config = \FormyChat\App::sanitize_whatsapp_message_templates($config);
                $config = \FormyChat\App::normalize_widget_config($config);

                /**
                 * Filters a widget's config before it reaches the front end.
                 *
                 * Free clamps "custom" style options (icon size/position, form
                 * size/colors, greeting styles) back to their defaults.
                 * FormyChat Ultimate returns the config un-clamped.
                 *
                 * @since 2.16.0
                 * @param array $config Widget config.
                 */
                $config = apply_filters('formychat_widget_config', $config);
                $config = \FormyChat\App::resolve_launcher_color($config);

                $widgets[] = [
					'id' => intval($widget->id),
					'name' => $widget->name,
					'config' => json_decode(wp_json_encode($config)),
					// Channel and launcher icons the site shows (2.16.0); the config above is unchanged.
					'resolved' => \FormyChat\App::resolve_widget_icons($config),
                ];
            }

            return $widgets;
        }

        /**
         * Get names.
         *
         * @return array
         */
        public static function get_names() {
            global $wpdb;

            $widgets = $wpdb->get_results("SELECT `id`, `name` FROM {$wpdb->prefix}scf_widgets WHERE `deleted_at` IS NULL ORDER BY id DESC"); // db call ok; no-cache ok.

            if ( $widgets && ! empty($widgets) ) {
                return $widgets;
            }

            return [];
        }

        /**
         * Find lead.
         */
        public static function find( $id ) {
            global $wpdb;

            $widget = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}scf_widgets WHERE id = %d", $id)); // db call ok; no-cache ok.

            if ( $widget ) {
                $widget = self::wrap($widget);

                return $widget;
            }

            return null;
        }

        /**
         * Wrapper for get.
         */
        public static function wrap( $object ) {

            // Int id.
            $object->id = (int) $object->id;

            // Is active.
            $object->is_active = wp_validate_boolean($object->is_active);

            // Decode config.
            $object->config = empty($object->config) ? [] : json_decode($object->config, true);
            if ( is_array($object->config) ) {
                $object->config = \FormyChat\App::sanitize_whatsapp_message_templates($object->config);
            }

            // Upgrade to the current schema in memory only; the stored row is not changed here.
            $object->config = \FormyChat\App::normalize_widget_config($object->config);

            /*
             * The settings the site shows (2.16.0): `formychat_widget_config` (documented in
             * get_active_widgets()) turns options this site does not offer back to their
             * defaults. The editor shows these values; the saved config above is unchanged,
             * so the saved choices come back as soon as the site offers them again.
             */
            $object->site_config = apply_filters('formychat_widget_config', $object->config);

            return $object;
        }

        /**
         * Get widget count.
         *
         * @return mixed
         */
        public static function total() {
            global $wpdb;

            return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}scf_widgets WHERE `deleted_at` IS NULL"); // db call ok; no-cache ok.
        }


        /**
         * Create widget.
         *
         * @param array $data Data.
         */
        public static function create( $data ) {
            global $wpdb;

            $data = wp_parse_args(
                $data, [
					'name' => 'Untitled',
					'is_active' => 1,
					'config' => [],
                ]
            );

            if ( isset($data['config']) && is_array($data['config']) ) {
                $data['config'] = \FormyChat\App::sanitize_whatsapp_message_templates($data['config']);
            }

            $data['config'] = \FormyChat\App::normalize_widget_config($data['config']);
            $data['config'] = wp_json_encode($data['config']);
            $data['updated_at'] = current_time('mysql');

            $wpdb->insert($wpdb->prefix . 'scf_widgets', $data); // db call ok; no-cache ok.

            return $wpdb->insert_id;
        }

        /**
         * Update widget.
         *
         * @param int   $id   ID.
         * @param array $data Data.
         */
        public static function update( $id, $data ) {
            global $wpdb;

            $data['updated_at'] = current_time('mysql');

            $allowed = [ 'name', 'is_active', 'config' ];

            if ( array_key_exists('config', $data) ) {
                self::backup_config_before_upgrade($id);
            }

            foreach ( $data as $key => $value ) {
                if ( in_array($key, $allowed, true) ) {
                    if ( 'config' === $key && is_array($value) ) {
                        $value = \FormyChat\App::sanitize_whatsapp_message_templates($value);
                        $value = \FormyChat\App::normalize_widget_config($value);
                    }
                    $data[ $key ] = $value;

                    if ( 'config' === $key ) {
                        $data[ $key ] = wp_json_encode($value);
                    }
                }
            }

            $wpdb->update($wpdb->prefix . 'scf_widgets', $data, [ 'id' => $id ]); // db call ok; no-cache ok.

            return $wpdb->rows_affected;
        }

        /**
         * Keep a one-time copy of a widget's config as it was before its first save in the
         * channels schema, so an upgrade can always be undone. Never overwritten.
         *
         * @since 2.16.0
         * @param int $id Widget ID.
         */
        private static function backup_config_before_upgrade( $id ) {
            global $wpdb;

            $option = 'formychat_widget_' . absint($id) . '_config_backup';

            if ( false !== get_option($option, false) ) {
                return;
            }

            $raw = $wpdb->get_var($wpdb->prepare("SELECT config FROM {$wpdb->prefix}scf_widgets WHERE id = %d", $id)); // db call ok; no-cache ok.

            if ( null === $raw ) {
                return;
            }

            $stored = json_decode($raw, true);
            $version = is_array($stored) && isset($stored['config_version']) ? absint($stored['config_version']) : 1;

            if ( $version >= \FormyChat\App::CONFIG_VERSION ) {
                return;
            }

            add_option($option, $raw, '', 'no');
        }

        /**
         * Delete widget.
         *
         * @param int $id ID.
         */
        public static function delete( $ids ) {
            global $wpdb;

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}scf_widgets SET `deleted_at` = %s WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ')',
                    array_merge([ current_time('mysql') ], $ids)
                )
            ); // db call ok; no-cache ok.

            return $wpdb->rows_affected;
        }
    }

}
