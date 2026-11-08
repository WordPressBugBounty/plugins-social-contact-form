<?php
/**
 * Lead model.
 *
 * @package FormyChat
 * @since   1.0.0
 */

// Namespace .
namespace FormyChat\Models;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewParamTypeDeclarations,PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations -- Plugin requires PHP 7.4+.

if ( ! class_exists(__NAMESPACE__ . '\Lead') ) {
    /**
     * Lead model.
     *
     * @package FormyChat
     * @since   1.0.0
     */
    class Lead {


        /**
         * Create lead for FORMYCHAT.
         *
         * @param  mixed $field Field.
         * @param  mixed $meta  Meta.
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

            $channel = isset($data['channel']) && is_string($data['channel']) ? sanitize_key($data['channel']) : 'whatsapp';
            if ( ! in_array($channel, \FormyChat\App::CHANNELS, true) ) {
                $channel = 'whatsapp';
            }

            // Without the channel column (upgrade not finished), keep the channel inside meta so it is not lost.
            if ( ! self::has_channel_column() && is_array($data['meta']) ) {
                $data['meta']['channel'] = $channel;
            }

            // Build the row explicitly so each value is always paired with its own format.
            $row = [
				'widget_id'  => absint($data['widget_id']),
				'field'      => wp_json_encode( $data['field'], JSON_UNESCAPED_UNICODE ) ?: '{}', // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
				'meta'       => wp_json_encode( $data['meta'], JSON_UNESCAPED_UNICODE ) ?: '{}', // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
				'note'       => is_string($data['note']) ? $data['note'] : '',
				'form'       => is_string($data['form']) ? $data['form'] : 'formychat',
				'form_id'    => absint($data['form_id']),
				'created_at' => current_time('mysql'),
            ];
            $formats = [ '%d', '%s', '%s', '%s', '%s', '%d', '%s' ];

            if ( self::has_channel_column() ) {
                $row['channel'] = $channel;
                $formats[]      = '%s';
            }

            $result = $wpdb->insert($table_name, $row, $formats); // db call ok; no-cache ok.

            if ( false === $result ) {
                return 0;
            }

            return $wpdb->insert_id;
        }

        /**
         * Whether the leads table has the `channel` column yet.
         *
         * @since 2.16.0
         * @return bool
         */
        public static function has_channel_column() {
            return absint(get_option('formychat_leads_schema', 0)) >= 2;
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
					'channel' => '',
                ]
            );

            global $wpdb;

            // Apply filter only when they are not empty.
            $where = [];

            if ( ! empty($filter['search']) ) {
                // Search in field, meta, note and widget_id.
                $where[] = $wpdb->prepare(
                    '(field LIKE %s OR meta LIKE %s OR note LIKE %s OR widget_id = %d)',
                    '%' . $wpdb->esc_like($filter['search']) . '%',
                    '%' . $wpdb->esc_like($filter['search']) . '%',
                    '%' . $wpdb->esc_like($filter['search']) . '%',
                    $filter['search']
                );
            }

            if ( ! empty($filter['after']) ) {
                $where[] = $wpdb->prepare('created_at >= %s', $filter['after']);
            }

            if ( ! empty($filter['before']) ) {
                $where[] = $wpdb->prepare('created_at <= %s', $filter['before']);
            }

            // Widget ID.
            if ( ! empty($filter['widget_id']) && is_numeric($filter['widget_id']) ) {
                $where[] = $wpdb->prepare('widget_id = %d', $filter['widget_id']);
            }

            // Form.
            if ( ! empty($filter['form']) ) {
                if ( 'formychat' === $filter['form'] ) {
                    $where[] = $wpdb->prepare('(form = %s OR form IS NULL)', $filter['form']);
                } else {
                    $where[] = $wpdb->prepare('form = %s', $filter['form']);
                }
            }

            // Form ID.
            if ( ! empty($filter['form_id']) && is_numeric($filter['form_id']) ) {
                $where[] = $wpdb->prepare('form_id = %d', $filter['form_id']);
            }

            // Channel. Leads saved before channels existed have no channel and count as WhatsApp.
            if ( self::has_channel_column() && in_array($filter['channel'], \FormyChat\App::CHANNELS, true) ) {
                if ( 'whatsapp' === $filter['channel'] ) {
                    $where[] = "(channel = 'whatsapp' OR channel IS NULL)";
                } else {
                    $where[] = $wpdb->prepare('channel = %s', $filter['channel']);
                }
            }

            $where[] = 'deleted_at IS NULL';

            $where = implode(' AND ', $where);

            // Defense-in-depth: whitelist orderby/order again here, since this is raw SQL.
            $allowed_orderby = [ 'created_at', 'id', 'widget_id', 'form', 'form_id' ];
            $safe_orderby = in_array($filter['orderby'], $allowed_orderby, true) ? $filter['orderby'] : 'created_at';
            $safe_order = in_array(strtoupper($filter['order']), [ 'ASC', 'DESC' ], true) ? strtoupper($filter['order']) : 'DESC';

            $leads = $wpdb->get_results(
                $wpdb->prepare(
           "SELECT * FROM {$wpdb->prefix}scf_leads WHERE {$where} ORDER BY {$safe_orderby} {$safe_order} LIMIT %d,%d", // phpcs:ignore
                    ( 'All' === $filter['per_page'] ) ? 1 : ( ( $filter['page'] - 1 ) * $filter['per_page'] ),
                    ( 'All' === $filter['per_page'] ) ? 99999999 : intval($filter['per_page'])
                )
            ); // db call ok; no-cache ok.

            if ( $leads ) {
                foreach ( $leads as $lead ) {
                    $lead->id = intval($lead->id);
                    $lead->widget_id = empty($lead->widget_id) ? 1 : intval($lead->widget_id);
                    $lead->field = empty($lead->field) ? [] : json_decode(( $lead->field ));
                    $lead->meta = empty($lead->meta) ? [] : json_decode(( $lead->meta ));
                    $lead->note = empty($lead->note) ? '' : $lead->note;
                    $lead->form = empty($lead->form) ? 'formychat' : $lead->form;
                    $lead->form_id = empty($lead->form_id) ? 0 : intval($lead->form_id);
                    $lead->channel = self::channel_of($lead);
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
                    "UPDATE {$wpdb->prefix}scf_leads SET deleted_at = %s WHERE form = %s AND id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ') ',
                    current_time('mysql'),
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
            return $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}scf_leads WHERE deleted_at IS NULL"); // db call ok; no-cache ok.
        }

        /**
         * Get lead count from form.
         *
         * @param  string $form Form.
         * @return mixed
         */
        public static function total_from( $form ) {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$wpdb->prefix}scf_leads WHERE form = %s AND deleted_at IS NULL", $form)); // db call ok; no-cache ok.
        }

        /**
         * Get a single lead by ID.
         *
         * @param  int $id Lead ID.
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
                $lead->id        = intval($lead->id);
                $lead->widget_id = empty($lead->widget_id) ? 1 : intval($lead->widget_id);
                $lead->field     = empty($lead->field) ? [] : json_decode($lead->field);
                $lead->meta      = empty($lead->meta) ? [] : json_decode($lead->meta);
                $lead->note      = empty($lead->note) ? '' : $lead->note;
                $lead->form      = empty($lead->form) ? 'formychat' : $lead->form;
                $lead->form_id   = empty($lead->form_id) ? 0 : intval($lead->form_id);
                $lead->channel   = self::channel_of($lead);
            }

            return $lead;
        }

        /**
         * Channel of a lead row. Older leads (no channel stored) are WhatsApp.
         *
         * @since 2.16.0
         * @param  object $lead Lead row with decoded meta.
         * @return string
         */
        private static function channel_of( $lead ) {
            if ( ! empty($lead->channel) && in_array($lead->channel, \FormyChat\App::CHANNELS, true) ) {
                return $lead->channel;
            }

            if ( isset($lead->meta->channel) && in_array($lead->meta->channel, \FormyChat\App::CHANNELS, true) ) {
                return $lead->meta->channel;
            }

            return 'whatsapp';
        }
    }

}
