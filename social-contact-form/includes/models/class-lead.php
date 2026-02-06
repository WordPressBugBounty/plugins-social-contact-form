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
		 * Create lead for FORMYCHAT.
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

		/**
		 * Get leads pending Google Sheets sync.
		 *
		 * @param int $limit 0 = no limit.
		 * @return array
		 */
		public static function get_pending_sync( int $limit = 0 ): array {
			global $wpdb;

			$limit_sql = $limit > 0 ? $wpdb->prepare( 'LIMIT %d', $limit ) : '';

			$leads = $wpdb->get_results(
				"SELECT * FROM {$wpdb->prefix}scf_leads
				WHERE google_sheet_synced_at IS NULL
				AND deleted_at IS NULL
				ORDER BY created_at ASC
				{$limit_sql}" // phpcs:ignore
			); // db call ok; no-cache ok.

			if ( $leads ) {
				foreach ( $leads as $lead ) {
					$lead->id        = intval( $lead->id );
					$lead->widget_id = empty( $lead->widget_id ) ? 1 : intval( $lead->widget_id );
					$lead->field     = empty( $lead->field ) ? [] : json_decode( $lead->field );
					$lead->meta      = empty( $lead->meta ) ? [] : json_decode( $lead->meta );
					$lead->note      = empty( $lead->note ) ? '' : $lead->note;
					$lead->form      = empty( $lead->form ) ? 'formychat' : $lead->form;
					$lead->form_id   = empty( $lead->form_id ) ? 0 : intval( $lead->form_id );
				}
			}

			return $leads ? $leads : [];
		}

		/**
		 * Mark leads as synced to Google Sheets.
		 *
		 * @param array $ids Lead IDs.
		 * @return int Number of rows updated.
		 */
		public static function mark_synced( array $ids ): int {
			global $wpdb;

			if ( empty( $ids ) ) {
				return 0;
			}

			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}scf_leads
					SET google_sheet_synced_at = %s
					WHERE id IN ({$placeholders})", // phpcs:ignore
					current_time( 'mysql' ),
					...$ids
				)
			); // db call ok; no-cache ok.

			return $wpdb->rows_affected;
		}

		/**
		 * Reset sync status for all leads.
		 *
		 * @return int Number of rows updated.
		 */
		public static function reset_sync_status(): int {
			global $wpdb;

			$wpdb->query(
				"UPDATE {$wpdb->prefix}scf_leads
				SET google_sheet_synced_at = NULL
				WHERE deleted_at IS NULL" // phpcs:ignore
			); // db call ok; no-cache ok.

			return $wpdb->rows_affected;
		}

		/**
		 * Count synced leads.
		 *
		 * @return int
		 */
		public static function count_synced(): int {
			global $wpdb;
			return (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}scf_leads
				WHERE google_sheet_synced_at IS NOT NULL
				AND deleted_at IS NULL" // phpcs:ignore
			); // db call ok; no-cache ok.
		}

		/**
		 * Count pending sync leads.
		 *
		 * @return int
		 */
		public static function count_pending_sync(): int {
			global $wpdb;
			return (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}scf_leads
				WHERE google_sheet_synced_at IS NULL
				AND deleted_at IS NULL" // phpcs:ignore
			); // db call ok; no-cache ok.
		}

		/**
		 * Get a single lead by ID.
		 *
		 * @param int $id Lead ID.
		 * @return object|null
		 */
		public static function find( int $id ) {
			global $wpdb;

			$lead = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}scf_leads WHERE id = %d AND deleted_at IS NULL",
					$id
				)
			); // db call ok; no-cache ok.

			if ( $lead ) {
				$lead->id        = intval( $lead->id );
				$lead->widget_id = empty( $lead->widget_id ) ? 1 : intval( $lead->widget_id );
				$lead->field     = empty( $lead->field ) ? [] : json_decode( $lead->field );
				$lead->meta      = empty( $lead->meta ) ? [] : json_decode( $lead->meta );
				$lead->note      = empty( $lead->note ) ? '' : $lead->note;
				$lead->form      = empty( $lead->form ) ? 'formychat' : $lead->form;
				$lead->form_id   = empty( $lead->form_id ) ? 0 : intval( $lead->form_id );
			}

			return $lead;
		}

		/**
		 * Get all unique field keys from all leads.
		 *
		 * @return array Unique field keys.
		 */
		public static function get_all_field_keys(): array {
			global $wpdb;

			$fields = $wpdb->get_col(
				"SELECT field FROM {$wpdb->prefix}scf_leads WHERE deleted_at IS NULL AND field IS NOT NULL AND field != ''" // phpcs:ignore
			); // db call ok; no-cache ok.

			$keys = [];
			foreach ( $fields as $field_json ) {
				$field = json_decode( $field_json, true );
				if ( is_array( $field ) ) {
					foreach ( array_keys( $field ) as $key ) {
						if ( ! in_array( $key, $keys, true ) ) {
							$keys[] = $key;
						}
					}
				}
			}

			return $keys;
		}
	}

}
