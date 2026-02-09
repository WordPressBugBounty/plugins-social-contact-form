<?php
/**
 * Google Sheets Cron Manager.
 *
 * Handles WP Cron scheduling for automatic sync.
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
 * Google Sheets Cron class.
 */
class Google_Sheets_Cron extends Base {

	/**
	 * Cron hook name.
	 */
	const CRON_HOOK = 'formychat_google_sheets_sync_cron';

	/**
	 * Actions.
	 */
	public function actions() {
		add_action( self::CRON_HOOK, [ $this, 'execute_cron_sync' ] );
		add_action( 'init', [ $this, 'maybe_schedule_cron' ] );
	}

	/**
	 * Ensure cron is scheduled if settings indicate it should be.
	 */
	public function maybe_schedule_cron(): void {
		// Only run in admin or during cron.
		if ( ! is_admin() && ! wp_doing_cron() ) {
			return;
		}

		// Check if integration is enabled.
		if ( ! wp_validate_boolean( get_option( 'formychat_integration_google_sheets', false ) ) ) {
			return;
		}

		$settings = Google_Sheets_Sync::get_settings();

		// If scheduled mode but no cron scheduled, schedule it.
		if ( in_array( $settings['sync_mode'], [ 'scheduled', 'automatic' ], true ) && ! $this->is_scheduled() ) {
			$this->schedule_sync( $settings['sync_interval'] );
		}
	}

	/**
	 * Filters.
	 */
	public function filters() {
		add_filter( 'cron_schedules', [ $this, 'register_cron_schedules' ] );
	}

	/**
	 * Register custom cron schedules.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array
	 */
	public function register_cron_schedules( array $schedules ): array {
		$custom_schedules = [
			'formychat_15min'  => [
				'interval' => 15 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 15 minutes', 'social-contact-form' ),
			],
			'formychat_30min'  => [
				'interval' => 30 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 30 minutes', 'social-contact-form' ),
			],
			'formychat_6hours' => [
				'interval' => 6 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 6 hours', 'social-contact-form' ),
			],
		];

		return apply_filters(
			'formychat_google_sheets_cron_schedules',
			array_merge( $schedules, $custom_schedules )
		);
	}

	/**
	 * Get available sync intervals.
	 *
	 * @return array
	 */
	public static function get_intervals(): array {
		return apply_filters(
			'formychat_google_sheets_sync_intervals',
			[
				'15m'   => [
					'schedule' => 'formychat_15min',
					'label'    => __( 'Every 15 minutes', 'social-contact-form' ),
				],
				'30m'   => [
					'schedule' => 'formychat_30min',
					'label'    => __( 'Every 30 minutes', 'social-contact-form' ),
				],
				'1h'    => [
					'schedule' => 'hourly',
					'label'    => __( 'Every hour', 'social-contact-form' ),
				],
				'6h'    => [
					'schedule' => 'formychat_6hours',
					'label'    => __( 'Every 6 hours', 'social-contact-form' ),
				],
				'daily' => [
					'schedule' => 'daily',
					'label'    => __( 'Daily', 'social-contact-form' ),
				],
			]
		);
	}

	/**
	 * Get intervals for frontend (simplified format).
	 *
	 * @return array
	 */
	public static function get_intervals_for_frontend(): array {
		$intervals = self::get_intervals();
		$result    = [];

		foreach ( $intervals as $key => $data ) {
			$result[] = [
				'value' => $key,
				'label' => $data['label'],
			];
		}

		return $result;
	}

	/**
	 * Schedule sync cron job.
	 *
	 * @param string $interval Interval key (15m, 30m, 1h, 6h, daily).
	 * @return bool
	 */
	public function schedule_sync( string $interval ): bool {
		// First unschedule any existing job.
		$this->unschedule_sync();

		$intervals = self::get_intervals();

		if ( ! isset( $intervals[ $interval ] ) ) {
			return false;
		}

		$schedule = $intervals[ $interval ]['schedule'];

		return (bool) wp_schedule_event( time(), $schedule, self::CRON_HOOK );
	}

	/**
	 * Unschedule sync cron job.
	 *
	 * @return bool
	 */
	public function unschedule_sync(): bool {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );

		if ( $timestamp ) {
			return wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}

		return true;
	}

	/**
	 * Reschedule sync with new interval.
	 *
	 * @param string $new_interval New interval key.
	 * @return bool
	 */
	public function reschedule_sync( string $new_interval ): bool {
		return $this->schedule_sync( $new_interval );
	}

	/**
	 * Check if cron is scheduled.
	 *
	 * @return bool
	 */
	public function is_scheduled(): bool {
		return (bool) wp_next_scheduled( self::CRON_HOOK );
	}

	/**
	 * Get next scheduled run time.
	 *
	 * @return int|false Timestamp or false if not scheduled.
	 */
	public function get_next_run() {
		return wp_next_scheduled( self::CRON_HOOK );
	}

	/**
	 * Execute cron sync.
	 */
	public function execute_cron_sync(): void {
		// Check if integration is enabled.
		if ( ! wp_validate_boolean( get_option( 'formychat_integration_google_sheets', false ) ) ) {
			return;
		}

		$settings = Google_Sheets_Sync::get_settings();

		// Only run if scheduled mode is enabled (also accept 'automatic' for backward compatibility).
		if ( ! in_array( $settings['sync_mode'], [ 'scheduled', 'automatic' ], true ) ) {
			// If not scheduled, unschedule the cron.
			$this->unschedule_sync();
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

		// Execute sync.
		$sync = Google_Sheets_Sync::get_instance();
		$sync->sync_leads();
	}

	/**
	 * Update cron based on settings change.
	 *
	 * @param array $settings New settings.
	 */
	public function update_cron_from_settings( array $settings ): void {
		$sync_mode     = $settings['sync_mode'] ?? 'manual';
		$sync_interval = $settings['sync_interval'] ?? '1h';

		// Backward compatibility: treat 'automatic' as 'scheduled'.
		if ( in_array( $sync_mode, [ 'scheduled', 'automatic' ], true ) ) {
			$this->schedule_sync( $sync_interval );
		} else {
			$this->unschedule_sync();
		}
	}
}

Google_Sheets_Cron::init();
