<?php
/**
 * Widget model.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace .
namespace FormyChat\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( __NAMESPACE__ . '\Widget') ) {
	/**
	 * Widget model.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Widget {

		/**
		 * Get all widgets.
		 *
		 * @return array
		 */
		public static function get_all() {
			global $wpdb;

			$widgets = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}scf_widgets WHERE `deleted_at` IS NULL ORDER BY id DESC" ); // db call ok; no-cache ok.

			if ( $widgets && ! empty( $widgets ) ) {
				foreach ( $widgets as $widget ) {
					$widget = self::wrap( $widget );
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
			$active_widgets = $wpdb->get_results( "SELECT `id`, `name`, `config` FROM {$wpdb->prefix}scf_widgets WHERE `is_active` IS TRUE AND `deleted_at` IS NULL" );  // db call ok; no-cache ok.

			// Bail if no active widgets.
			if ( ! $active_widgets ) {
				return;
			}

			// Format widget data.
			$widgets = [];
			foreach ( $active_widgets as $widget ) {
				$config = json_decode( $widget->config );

				$widgets[] = [
					'id' => intval($widget->id),
					'name' => $widget->name,
					'config' => $config,
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

			$widgets = $wpdb->get_results( "SELECT `id`, `name` FROM {$wpdb->prefix}scf_widgets WHERE `deleted_at` IS NULL ORDER BY id DESC" ); // db call ok; no-cache ok.

			if ( $widgets && ! empty( $widgets ) ) {
				return $widgets;
			}

			return [];
		}

		/**
		 * Find lead.
		 */
		public static function find( $id ) {
			global $wpdb;

			$widget = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}scf_widgets WHERE id = %d", $id ) ); // db call ok; no-cache ok.

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
			$object->is_active = wp_validate_boolean( $object->is_active );

			// Decode config.
			$object->config = empty( $object->config ) ? [] : json_decode( $object->config, true );

			return $object;
		}

		/**
		 * Get widget count.
		 *
		 * @return mixed
		 */
		public static function total() {
			global $wpdb;

			return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}scf_widgets WHERE `deleted_at` IS NULL" ); // db call ok; no-cache ok.
		}


		/**
		 * Create widget.
		 *
		 * @param array $data Data.
		 */
		public static function create( $data ) {
			global $wpdb;

			$data = wp_parse_args( $data, [
				'name' => 'Untitled',
				'is_active' => 1,
				'config' => [],
			] );

			$data['config'] = wp_json_encode($data['config']);
			$data['updated_at'] = current_time( 'mysql' );

			$wpdb->insert( $wpdb->prefix . 'scf_widgets', $data ); // db call ok; no-cache ok.

			return $wpdb->insert_id;
		}

		/**
		 * Update widget.
		 *
		 * @param int $id ID.
		 * @param array $data Data.
		 */
		public static function update( $id, $data ) {
			global $wpdb;

			$data['updated_at'] = current_time( 'mysql' );

			$allowed = [ 'name', 'is_active', 'config' ];

			foreach ( $data as $key => $value ) {
				if ( in_array( $key, $allowed ) ) {
					$data[ $key ] = $value;

					if ( 'config' === $key ) {
						$data[ $key ] = wp_json_encode( $value );
					}
				}
			}

			$wpdb->update( $wpdb->prefix . 'scf_widgets', $data, [ 'id' => $id ] ); // db call ok; no-cache ok.

			return $wpdb->rows_affected;
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
					"UPDATE {$wpdb->prefix}scf_widgets SET `deleted_at` = %s WHERE id IN (" . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
					array_merge( [ current_time( 'mysql' ) ], $ids )
				)
			); // db call ok; no-cache ok.

			return $wpdb->rows_affected;
		}
	}

}
