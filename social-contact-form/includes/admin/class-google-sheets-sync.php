<?php
/**
 * Google Sheets Sync Service.
 *
 * Handles lead synchronization to Google Sheets.
 *
 * @package FormyChat
 * @since 1.0.0
 */

namespace FormyChat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Sheets Sync class.
 */
class Google_Sheets_Sync extends Base {

	/**
	 * Option key for sync settings.
	 */
	const OPTION_KEY = 'formychat_google_sheets_sync';

	/**
	 * Free tier row limit.
	 */
	const FREE_LIMIT = 100;

	/**
	 * API instance.
	 *
	 * @var Google_Sheets_API
	 */
	private $api;

	/**
	 * Widget names cache.
	 *
	 * @var array
	 */
	private $widget_names = [];

	/**
	 * Dynamic field keys for current sync.
	 *
	 * @var array
	 */
	private $field_keys = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->api = new Google_Sheets_API();
	}

	/**
	 * Actions.
	 */
	public function actions() {
		// Hook into lead creation for real-time sync.
		add_action( 'formychat_lead_created', [ $this, 'handle_realtime_sync' ], 20, 3 );
	}

	/**
	 * Get sync settings.
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$settings = wp_parse_args(
			get_option( self::OPTION_KEY, [] ),
			[
				'spreadsheet_id'   => '',
				'spreadsheet_name' => '',
				'sync_mode'        => 'scheduled',
				'sync_interval'    => '1h',
				'last_sync_at'     => null,
				'last_sync_count'  => 0,
				'last_sync_error'  => null,
			]
		);

		// Backward compatibility: map 'automatic' to 'scheduled'.
		if ( 'automatic' === $settings['sync_mode'] ) {
			$settings['sync_mode'] = 'scheduled';
		}

		return $settings;
	}

	/**
	 * Update sync settings.
	 *
	 * @param array $settings Settings to update.
	 * @return bool
	 */
	public static function update_settings( array $settings ): bool {
		$current  = self::get_settings();
		$settings = wp_parse_args( $settings, $current );

		return update_option( self::OPTION_KEY, $settings );
	}

	/**
	 * Get sync statistics.
	 *
	 * @return array
	 */
	public function get_sync_stats(): array {
		$settings = self::get_settings();
		$total    = (int) Models\Lead::total();
		$synced   = Models\Lead::count_synced();
		$pending  = Models\Lead::count_pending_sync();

		return [
			'total'      => $total,
			'synced'     => $synced,
			'pending'    => $pending,
			'last_sync'  => $settings['last_sync_at'],
			'last_count' => $settings['last_sync_count'],
			'last_error' => $settings['last_sync_error'],
		];
	}

	/**
	 * Get free tier limit.
	 *
	 * @return int
	 */
	public function get_free_limit(): int {
		return (int) apply_filters( 'formychat_google_sheets_free_limit', self::FREE_LIMIT );
	}

	/**
	 * Check if free tier limit is reached.
	 *
	 * @return bool
	 */
	public function is_limit_reached(): bool {
		if ( $this->is_ultimate_active() ) {
			return false;
		}

		return Models\Lead::count_synced() >= $this->get_free_limit();
	}

	/**
	 * Get remaining sync slots for free tier.
	 *
	 * @return int
	 */
	public function get_remaining_slots(): int {
		if ( $this->is_ultimate_active() ) {
			return PHP_INT_MAX;
		}

		$synced = Models\Lead::count_synced();
		$limit  = $this->get_free_limit();

		return max( 0, $limit - $synced );
	}

	/**
	 * Get headers for sheet.
	 *
	 * @return array
	 */
	public function get_headers(): array {
		$headers = [
			'ID',
			'Widget',
			'Form',
			'Date',
			'Time',
		];

		// Add dynamic field headers.
		foreach ( $this->field_keys as $key ) {
			$headers[] = ucfirst( str_replace( [ '_', '-' ], ' ', $key ) );
		}

		return apply_filters( 'formychat_google_sheets_headers', $headers );
	}

	/**
	 * Get widget name by ID.
	 *
	 * @param int|null $widget_id Widget ID.
	 * @return string
	 */
	private function get_widget_name( $widget_id ): string {
		if ( empty( $widget_id ) ) {
			return '';
		}

		if ( empty( $this->widget_names ) ) {
			$widgets = Models\Widget::get_names();
			foreach ( $widgets as $widget ) {
				$this->widget_names[ (int) $widget->id ] = $widget->name;
			}
		}

		return $this->widget_names[ (int) $widget_id ] ?? '';
	}

	/**
	 * Get readable form name.
	 *
	 * @param string|null $form Form type.
	 * @return string
	 */
	private function get_form_name( $form ): string {
		if ( empty( $form ) ) {
			return '';
		}

		$form_names = [
			'formychat'  => 'FormyChat',
			'whatsapp'   => 'WhatsApp',
			'messenger'  => 'Messenger',
			'cf7'        => 'Contact Form 7',
			'wpforms'    => 'WPForms',
			'gravity'    => 'Gravity Forms',
			'ninja'      => 'Ninja Forms',
			'fluentform' => 'Fluent Forms',
			'fluent'     => 'Fluent Forms',
			'formidable' => 'Formidable Forms',
			'forminator' => 'Forminator',
			'elementor'  => 'Elementor Forms',
		];

		return $form_names[ strtolower( $form ) ] ?? ucfirst( $form );
	}

	/**
	 * Build row data from lead.
	 *
	 * @param object $lead Lead object.
	 * @return array
	 */
	private function build_row( $lead ): array {
		$field = is_object( $lead->field ) ? (array) $lead->field : ( is_array( $lead->field ) ? $lead->field : [] );

		// Format date and time separately.
		$date = '';
		$time = '';
		if ( ! empty( $lead->created_at ) ) {
			$timestamp = strtotime( $lead->created_at );
			$date      = wp_date( 'M j, Y', $timestamp );
			$time      = wp_date( 'g:i A', $timestamp );
		}

		$row = [
			$lead->id,
			$this->get_widget_name( $lead->widget_id ?? null ),
			$this->get_form_name( $lead->form ?? null ),
			$date,
			$time,
		];

		// Add dynamic field values.
		foreach ( $this->field_keys as $key ) {
			$row[] = $field[ $key ] ?? '';
		}

		return apply_filters( 'formychat_google_sheets_row_data', $row, $lead );
	}

	/**
	 * Sync leads to Google Sheets.
	 *
	 * @param bool $is_full_resync Whether this is a full resync.
	 * @return array Result with success, synced count, and error.
	 */
	public function sync_leads( bool $is_full_resync = false ): array {
		// Check if integration is enabled.
		if ( ! wp_validate_boolean( get_option( 'formychat_integration_google_sheets', false ) ) ) {
			return [
				'success' => false,
				'synced'  => 0,
				'error'   => __( 'Google Sheets integration is disabled.', 'formychat' ),
			];
		}

		$settings = self::get_settings();

		if ( empty( $settings['spreadsheet_id'] ) ) {
			return [
				'success' => false,
				'synced'  => 0,
				'error'   => __( 'No spreadsheet selected.', 'formychat' ),
			];
		}

		// Get leads limit.
		$limit = $this->is_ultimate_active() ? 0 : $this->get_remaining_slots();

		if ( 0 === $limit && ! $this->is_ultimate_active() ) {
			return [
				'success' => false,
				'synced'  => 0,
				'error'   => __( 'Free tier limit reached. Upgrade to sync more leads.', 'formychat' ),
			];
		}

		// For full resync, reset sync status first, then get all leads.
		if ( $is_full_resync ) {
			Models\Lead::reset_sync_status();
		}

		// Get pending leads.
		$leads = Models\Lead::get_pending_sync( $limit );

		if ( empty( $leads ) ) {
			// Update last sync time even when no leads to sync.
			self::update_settings( [
				'last_sync_at'    => current_time( 'mysql' ),
				'last_sync_count' => 0,
				'last_sync_error' => null,
			] );

			return [
				'success' => true,
				'synced'  => 0,
				'error'   => null,
			];
		}

		// Collect ALL dynamic field keys from database for consistent headers.
		$this->field_keys = Models\Lead::get_all_field_keys();

		// For full resync, reinitialize sheet with dynamic headers.
		if ( $is_full_resync ) {
			$setup_result = $this->setup_spreadsheet( $settings['spreadsheet_id'] );

			if ( is_wp_error( $setup_result ) ) {
				$this->update_sync_error( $setup_result->get_error_message() );
				return [
					'success' => false,
					'synced'  => 0,
					'error'   => $setup_result->get_error_message(),
				];
			}
		}

		// Build rows.
		$rows = [];
		foreach ( $leads as $lead ) {
			$rows[] = $this->build_row( $lead );
		}

		// Append to sheet.
		$result = $this->api->append_rows( $settings['spreadsheet_id'], $rows );

		if ( is_wp_error( $result ) ) {
			$this->update_sync_error( $result->get_error_message() );
			return [
				'success' => false,
				'synced'  => 0,
				'error'   => $result->get_error_message(),
			];
		}

		// Mark leads as synced.
		$lead_ids = array_map( fn( $lead ) => $lead->id, $leads );
		Models\Lead::mark_synced( $lead_ids );

		// Update settings.
		self::update_settings( [
			'last_sync_at'    => current_time( 'mysql' ),
			'last_sync_count' => count( $leads ),
			'last_sync_error' => null,
		] );

		return [
			'success' => true,
			'synced'  => count( $leads ),
			'error'   => null,
		];
	}

	/**
	 * Sync a single lead (for real-time mode).
	 *
	 * @param int $lead_id Lead ID.
	 * @return bool
	 */
	public function sync_single_lead( int $lead_id ): bool {
		// Check if integration is enabled.
		if ( ! wp_validate_boolean( get_option( 'formychat_integration_google_sheets', false ) ) ) {
			return false;
		}

		$settings = self::get_settings();

		if ( empty( $settings['spreadsheet_id'] ) ) {
			return false;
		}

		if ( $this->is_limit_reached() ) {
			return false;
		}

		$lead = Models\Lead::find( $lead_id );

		if ( ! $lead ) {
			return false;
		}

		// Collect ALL field keys for consistent column order.
		$this->field_keys = Models\Lead::get_all_field_keys();

		$row    = $this->build_row( $lead );
		$result = $this->api->append_rows( $settings['spreadsheet_id'], [ $row ] );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		Models\Lead::mark_synced( [ $lead_id ] );

		self::update_settings( [
			'last_sync_at'    => current_time( 'mysql' ),
			'last_sync_count' => 1,
		] );

		return true;
	}

	/**
	 * Handle real-time sync on lead creation.
	 *
	 * @param array            $form_data Form data.
	 * @param int              $lead_id   Lead ID.
	 * @param \WP_REST_Request $request   Request object.
	 */
	public function handle_realtime_sync( array $form_data, int $lead_id, $request ): void {
		// Check if integration is enabled.
		if ( ! wp_validate_boolean( get_option( 'formychat_integration_google_sheets', false ) ) ) {
			return;
		}

		$settings = self::get_settings();

		// Only sync if real-time mode is enabled.
		if ( 'realtime' !== $settings['sync_mode'] ) {
			return;
		}

		// Check connection.
		$gs_data = get_option( 'formychat_google_sheets', [] );
		if ( empty( $gs_data['connected'] ) || ! empty( $gs_data['revoked'] ) ) {
			return;
		}

		// Check spreadsheet is set.
		if ( empty( $settings['spreadsheet_id'] ) ) {
			return;
		}

		$this->sync_single_lead( $lead_id );
	}

	/**
	 * Setup spreadsheet with headers and filter.
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @return bool|\WP_Error
	 */
	public function setup_spreadsheet( string $spreadsheet_id ) {
		// Ensure field_keys is populated for dynamic headers.
		if ( empty( $this->field_keys ) ) {
			$this->field_keys = Models\Lead::get_all_field_keys();
		}

		$headers = $this->get_headers();

		return $this->api->setup_sheet( $spreadsheet_id, $headers );
	}

	/**
	 * Update sync error in settings.
	 *
	 * @param string $error Error message.
	 */
	private function update_sync_error( string $error ): void {
		self::update_settings( [
			'last_sync_at'    => current_time( 'mysql' ),
			'last_sync_error' => $error,
		] );
	}
}

Google_Sheets_Sync::init();
