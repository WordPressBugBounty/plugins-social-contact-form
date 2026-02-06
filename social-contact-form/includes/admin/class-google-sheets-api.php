<?php
/**
 * Google Sheets API Client.
 *
 * Handles all Google Sheets and Drive API interactions.
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
 * Google Sheets API class.
 */
class Google_Sheets_API {

	/**
	 * Google Sheets API base URL.
	 */
	const SHEETS_API_BASE = 'https://sheets.googleapis.com/v4/spreadsheets';

	/**
	 * Google Drive API base URL.
	 */
	const DRIVE_API_BASE = 'https://www.googleapis.com/drive/v3/files';

	/**
	 * Get valid access token.
	 *
	 * @return string|\WP_Error Access token or error.
	 */
	private function get_access_token() {
		$data = get_option( 'formychat_google_sheets', [] );

		if ( empty( $data['connected'] ) || ! empty( $data['revoked'] ) ) {
			return new \WP_Error( 'not_connected', __( 'Google Sheets is not connected.', 'formychat' ) );
		}

		if ( empty( $data['access_token'] ) ) {
			return new \WP_Error( 'no_token', __( 'No access token available.', 'formychat' ) );
		}

		// Check if token is expired and refresh if needed.
		$expires = $data['token_expires'] ?? 0;
		if ( $expires && $expires < time() ) {
			$refreshed = Google_Sheets_Token::force_refresh();
			if ( ! $refreshed ) {
				return new \WP_Error( 'token_expired', __( 'Access token expired and refresh failed.', 'formychat' ) );
			}
			$data = get_option( 'formychat_google_sheets', [] );
		}

		return $data['access_token'];
	}

	/**
	 * Make authenticated API request.
	 *
	 * @param string $url     API URL.
	 * @param string $method  HTTP method.
	 * @param array  $body    Request body.
	 * @param array  $headers Additional headers.
	 * @return array|\WP_Error Response data or error.
	 */
	private function make_request( string $url, string $method = 'GET', array $body = [], array $headers = [] ) {
		$access_token = $this->get_access_token();

		if ( is_wp_error( $access_token ) ) {
			return $access_token;
		}

		$default_headers = [
			'Authorization' => 'Bearer ' . $access_token,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		];

		$args = [
			'method'  => $method,
			'timeout' => 30,
			'headers' => array_merge( $default_headers, $headers ),
		];

		if ( ! empty( $body ) && in_array( $method, [ 'POST', 'PUT', 'PATCH' ], true ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			error_log( 'FormyChat: wp_remote_request error: ' . $response->get_error_message() );
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$result      = json_decode( $body, true );

		if ( $status_code >= 400 ) {
			$error_message = $result['error']['message'] ?? __( 'API request failed.', 'formychat' );
			error_log( 'FormyChat: API error ' . $status_code . ': ' . $error_message );
			return new \WP_Error( 'api_error', $error_message, [ 'status' => $status_code ] );
		}

		return $result;
	}

	/**
	 * List user's spreadsheets.
	 *
	 * @return array|\WP_Error List of spreadsheets or error.
	 */
	public function list_spreadsheets() {
		$url = add_query_arg(
			[
				'q'        => "mimeType='application/vnd.google-apps.spreadsheet'",
				'fields'   => 'files(id,name,modifiedTime)',
				'orderBy'  => 'modifiedTime desc',
				'pageSize' => 50,
			],
			self::DRIVE_API_BASE
		);

		error_log( 'FormyChat: Listing spreadsheets from URL: ' . $url );

		$result = $this->make_request( $url );

		if ( is_wp_error( $result ) ) {
			error_log( 'FormyChat: list_spreadsheets API error: ' . $result->get_error_message() );
			return $result;
		}

		error_log( 'FormyChat: list_spreadsheets result: ' . wp_json_encode( $result ) );

		return $result['files'] ?? [];
	}

	/**
	 * Create a new spreadsheet.
	 *
	 * @param string $title Spreadsheet title.
	 * @return array|\WP_Error Created spreadsheet data or error.
	 */
	public function create_spreadsheet( string $title ) {
		$body = [
			'properties' => [
				'title' => $title,
			],
			'sheets'     => [
				[
					'properties' => [
						'title' => 'Leads',
					],
				],
			],
		];

		$result = $this->make_request( self::SHEETS_API_BASE, 'POST', $body );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return [
			'id'   => $result['spreadsheetId'] ?? '',
			'name' => $result['properties']['title'] ?? $title,
			'url'  => $result['spreadsheetUrl'] ?? '',
		];
	}

	/**
	 * Get spreadsheet details.
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @return array|\WP_Error Spreadsheet data or error.
	 */
	public function get_spreadsheet( string $spreadsheet_id ) {
		$url = self::SHEETS_API_BASE . '/' . $spreadsheet_id;

		return $this->make_request( $url );
	}

	/**
	 * Set header row in spreadsheet.
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @param array  $headers        Array of header values.
	 * @param string $sheet_name     Sheet name (default: 'Leads').
	 * @return bool|\WP_Error True on success or error.
	 */
	public function set_headers( string $spreadsheet_id, array $headers, string $sheet_name = 'Leads' ) {
		$range = $sheet_name . '!A1';
		$url   = self::SHEETS_API_BASE . '/' . $spreadsheet_id . '/values/' . rawurlencode( $range );
		$url   = add_query_arg( 'valueInputOption', 'RAW', $url );

		$body = [
			'values' => [ $headers ],
		];

		$result = $this->make_request( $url, 'PUT', $body );

		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Apply basic filter to sheet.
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @param int    $end_column     Number of columns.
	 * @param int    $sheet_id       Sheet ID (default: 0 for first sheet).
	 * @return bool|\WP_Error True on success or error.
	 */
	public function apply_table_formatting( string $spreadsheet_id, int $end_column, int $sheet_id = 0 ) {
		$url = self::SHEETS_API_BASE . '/' . $spreadsheet_id . ':batchUpdate';

		$body = [
			'requests' => [
				[
					'setBasicFilter' => [
						'filter' => [
							'range' => [
								'sheetId'          => $sheet_id,
								'startRowIndex'    => 0,
								'startColumnIndex' => 0,
								'endColumnIndex'   => $end_column,
							],
						],
					],
				],
			],
		];

		$result = $this->make_request( $url, 'POST', $body );

		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Get or create sheet by name.
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @param string $sheet_name     Sheet name.
	 * @return int|\WP_Error Sheet ID or error.
	 */
	public function get_or_create_sheet( string $spreadsheet_id, string $sheet_name = 'Leads' ) {
		// Get spreadsheet metadata.
		$spreadsheet = $this->get_spreadsheet( $spreadsheet_id );

		if ( is_wp_error( $spreadsheet ) ) {
			return $spreadsheet;
		}

		// Find sheet by name.
		$sheets = $spreadsheet['sheets'] ?? [];
		foreach ( $sheets as $sheet ) {
			if ( ( $sheet['properties']['title'] ?? '' ) === $sheet_name ) {
				return (int) ( $sheet['properties']['sheetId'] ?? 0 );
			}
		}

		// Sheet not found, create it.
		$url  = self::SHEETS_API_BASE . '/' . $spreadsheet_id . ':batchUpdate';
		$body = [
			'requests' => [
				[
					'addSheet' => [
						'properties' => [
							'title' => $sheet_name,
						],
					],
				],
			],
		];

		$result = $this->make_request( $url, 'POST', $body );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return (int) ( $result['replies'][0]['addSheet']['properties']['sheetId'] ?? 0 );
	}

	/**
	 * Setup sheet with headers and table formatting.
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @param array  $headers        Array of header values.
	 * @param string $sheet_name     Sheet name (default: 'Leads').
	 * @return bool|\WP_Error True on success or error.
	 */
	public function setup_sheet( string $spreadsheet_id, array $headers, string $sheet_name = 'Leads' ) {
		// Get or create the sheet.
		$sheet_id = $this->get_or_create_sheet( $spreadsheet_id, $sheet_name );

		if ( is_wp_error( $sheet_id ) ) {
			return $sheet_id;
		}

		// Clear existing data (preserves nothing, we'll set headers fresh).
		$this->clear_sheet( $spreadsheet_id, $sheet_name );

		// Set headers.
		$result = $this->set_headers( $spreadsheet_id, $headers, $sheet_name );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Apply table formatting.
		return $this->apply_table_formatting( $spreadsheet_id, count( $headers ), $sheet_id );
	}

	/**
	 * Append rows to spreadsheet.
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @param array  $rows           Array of row arrays.
	 * @param string $sheet_name     Sheet name (default: 'Leads').
	 * @return array|\WP_Error Response data or error.
	 */
	public function append_rows( string $spreadsheet_id, array $rows, string $sheet_name = 'Leads' ) {
		if ( empty( $rows ) ) {
			return [ 'updates' => [ 'updatedRows' => 0 ] ];
		}

		$range = $sheet_name . '!A:A';
		$url   = self::SHEETS_API_BASE . '/' . $spreadsheet_id . '/values/' . rawurlencode( $range ) . ':append';
		$url   = add_query_arg(
			[
				'valueInputOption' => 'RAW',
				'insertDataOption' => 'INSERT_ROWS',
			],
			$url
		);

		$body = [
			'values' => $rows,
		];

		return $this->make_request( $url, 'POST', $body );
	}

	/**
	 * Clear sheet contents (except header row).
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @param string $sheet_name     Sheet name (default: 'Leads').
	 * @return bool|\WP_Error True on success or error.
	 */
	public function clear_sheet( string $spreadsheet_id, string $sheet_name = 'Leads' ) {
		// Clear from row 2 onwards (preserve header).
		$range = $sheet_name . '!A2:ZZ';
		$url   = self::SHEETS_API_BASE . '/' . $spreadsheet_id . '/values/' . rawurlencode( $range ) . ':clear';

		$result = $this->make_request( $url, 'POST' );

		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Get all values from sheet.
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @param string $range          Range to get (default: all data in Leads sheet).
	 * @return array|\WP_Error Values or error.
	 */
	public function get_values( string $spreadsheet_id, string $range = 'Leads!A:ZZ' ) {
		$url = self::SHEETS_API_BASE . '/' . $spreadsheet_id . '/values/' . rawurlencode( $range );

		$result = $this->make_request( $url );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $result['values'] ?? [];
	}

	/**
	 * Get row count in sheet (excluding header).
	 *
	 * @param string $spreadsheet_id Spreadsheet ID.
	 * @param string $sheet_name     Sheet name (default: 'Leads').
	 * @return int|\WP_Error Row count or error.
	 */
	public function get_row_count( string $spreadsheet_id, string $sheet_name = 'Leads' ) {
		$values = $this->get_values( $spreadsheet_id, $sheet_name . '!A:A' );

		if ( is_wp_error( $values ) ) {
			return $values;
		}

		// Subtract 1 for header row.
		return max( 0, count( $values ) - 1 );
	}
}
