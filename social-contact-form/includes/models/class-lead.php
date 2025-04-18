<?php
/**
 * Lead model.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace .
namespace FormyChat\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( __NAMESPACE__ . '\Lead' ) ) {
	/**
	 * Lead model.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Lead {

		/**
		 * Create lead for SCF.
		 *
		 * @param mixed $field Field.
		 * @param mixed $meta Meta.
		 * @return int
		 */
		public static function create( $data ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'scf_leads';

			$data = wp_parse_args(
				$data,
				[
					'widget_id' => 1,
					'field' => [],
					'meta'  => [],
					'note'  => '',
					'form' => 'formychat',
					'form_id' => 0,
				]
			);

			// Add created_at.
			$data['created_at'] = current_time( 'mysql' );

			// Ensure proper JSON encoding with error handling
			$data['field'] = wp_json_encode( $data['field'], JSON_UNESCAPED_UNICODE ) ?: '{}'; // phpcs:ignore
			$data['meta']  = wp_json_encode( $data['meta'], JSON_UNESCAPED_UNICODE ) ?: '{}'; // phpcs:ignore

			$result = $wpdb->insert(
				$table_name,
				$data,
				[
					'%d',  // widget_id
					'%s',  // field
					'%s',  // meta
					'%s',  // note
					'%s',  // form
					'%d',  // form_id
					'%s',  // created_at
				]
			); // db call ok; no-cache ok.

			if ( false === $result ) {
				return 0;
			}

			return $wpdb->insert_id;
		}

		/**
		 * Get all leads.
		 *
		 * @return array|null|object
		 */
		public static function get( $filter = [] ) {

			$filter = wp_parse_args(
				$filter,
				[
					'search' => '',
					'before' => '',
					'after' => '',
					'per_page' => 20,
					'page' => 1,
					'order' => 'DESC',
					'orderby' => 'created_at',
					'widget_id' => 1,
					'form' => 'formychat',
					'form_id' => 0,
				]
			);

			global $wpdb;

			// Apply filter only when they are not empty.
			$where = [];

			if ( ! empty( $filter['search'] ) ) {
				// Search in field, meta, note and widget_id.
				$where[] = $wpdb->prepare(
					'(field LIKE %s OR meta LIKE %s OR note LIKE %s OR widget_id = %d)',
					'%' . $wpdb->esc_like( $filter['search'] ) . '%',
					'%' . $wpdb->esc_like( $filter['search'] ) . '%',
					'%' . $wpdb->esc_like( $filter['search'] ) . '%',
					$filter['search']
				);
			}

			if ( ! empty( $filter['after'] ) ) {
				$where[] = $wpdb->prepare( 'created_at >= %s', $filter['after'] );
			}

			if ( ! empty( $filter['before'] ) ) {
				$where[] = $wpdb->prepare( 'created_at <= %s', $filter['before'] );
			}

			// Widget ID.
			if ( ! empty( $filter['widget_id'] ) && is_numeric( $filter['widget_id'] ) ) {
				$where[] = $wpdb->prepare( 'widget_id = %d', $filter['widget_id'] );
			}

			// Form.
			if ( ! empty( $filter['form'] ) ) {
				if ( 'formychat' === $filter['form'] ) {
					$where[] = $wpdb->prepare( 'form = %s OR form IS NULL', $filter['form'] );
				} else {
					$where[] = $wpdb->prepare( 'form = %s', $filter['form'] );
				}
			}

			// Form ID.
			if ( ! empty( $filter['form_id'] ) && is_numeric( $filter['form_id'] ) ) {
				$where[] = $wpdb->prepare( 'form_id = %d', $filter['form_id'] );
			}

			$where[] = 'deleted_at IS NULL';

			$where = implode( ' AND ', $where );

			$leads = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}scf_leads WHERE {$where} ORDER BY {$filter['orderby']} {$filter['order']} LIMIT %d,%d", // phpcs:ignore
					( 'All' === $filter['per_page'] ) ? 1 : ( ( $filter['page'] - 1 ) * $filter['per_page'] ),
					( 'All' === $filter['per_page'] ) ? 99999999 : intval( $filter['per_page'] )
				)
			); // db call ok; no-cache ok.

			if ( $leads ) {
				foreach ( $leads as $lead ) {
					$lead->id = intval( $lead->id );
					$lead->widget_id = empty( $lead->widget_id ) ? 1 : intval( $lead->widget_id );
					$lead->field = empty( $lead->field ) ? [] : json_decode( ( $lead->field ) );
					$lead->meta = empty( $lead->meta ) ? [] : json_decode( ( $lead->meta ) );
					$lead->note = empty( $lead->note ) ? '' : $lead->note;
					$lead->form = empty( $lead->form ) ? 'formychat' : $lead->form;
					$lead->form_id = empty( $lead->form_id ) ? 0 : intval( $lead->form_id );
				}
			}

			return $leads;
		}

		/**
		 * Delete leads
		 *
		 * @param mixed $ids Lead IDs.
		 */
		public static function delete( $ids, $form = 'formychat' ) {
			global $wpdb;

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}scf_leads SET deleted_at = %s WHERE form = %s AND id IN (" . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ') ',
					current_time( 'mysql' ),
					$form,
					...$ids
				)
			); // db call ok; no-cache ok.

			return $wpdb->rows_affected;
		}

		/**
		 * Get lead count.
		 *
		 * @return mixed
		 */
		public static function total() {
			global $wpdb;
			return $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}scf_leads WHERE deleted_at IS NULL" ); // db call ok; no-cache ok.
		}

		/**
		 * Get lead count from form.
		 *
		 * @param string $form Form.
		 * @return mixed
		 */
		public static function total_from( $form ) {
			global $wpdb;
			return $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$wpdb->prefix}scf_leads WHERE form = %s AND deleted_at IS NULL", $form ) ); // db call ok; no-cache ok.
		}
	}

}
