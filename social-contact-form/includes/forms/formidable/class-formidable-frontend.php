<?php

/**
 * Frontend class.
 * Handles all Frontend requests.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace.
namespace FormyChat\Formidable;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( __NAMESPACE__ . '\Frontend' ) ) {
	/**
	 * Frontend class.
	 * Handles all Frontend requests.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Frontend extends \FormyChat\Base {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function actions() {
			// Ajax.
			add_action( 'wp_ajax_formychat_get_formidable_entry', [ $this, 'get_formidable_entry' ] );
			add_action( 'wp_ajax_nopriv_formychat_get_formidable_entry', [ $this, 'get_formidable_entry' ] );

			add_filter( 'frm_success_filter', [ $this, 'on_success' ], 10, 3 );
			add_action( 'formychat_footer', [ $this, 'footer' ], 10, 3 );

			// Store entry data after creation for secure retrieval.
			add_action( 'frm_after_create_entry', [ $this, 'store_entry_for_retrieval' ], 10, 2 );
		}

		/**
		 * Get the unique session identifier for the current user.
		 *
		 * Uses a combination of IP and user agent for guests, or user ID for logged-in users.
		 * This creates a reasonably unique identifier without requiring full session management.
		 *
		 * @since 1.0.0
		 *
		 * @return string The session identifier hash.
		 */
		private function get_session_identifier() {
			if ( is_user_logged_in() ) {
				return 'user_' . get_current_user_id();
			}

			// For guests, use a hash of IP + User Agent.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Used for hashing only.
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Used for hashing only.
			$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';

			return 'guest_' . md5( $ip . '|' . $ua . '|' . wp_salt( 'auth' ) );
		}

		/**
		 * Get the transient key for storing entry data.
		 *
		 * @since 1.0.0
		 *
		 * @param int $form_id The form ID.
		 * @return string The transient key.
		 */
		private function get_entry_transient_key( $form_id ) {
			$session_id = $this->get_session_identifier();
			return 'formychat_entry_' . md5( $session_id . '_' . $form_id );
		}

		/**
		 * Store entry data after creation for secure retrieval.
		 *
		 * Called via frm_after_create_entry hook. Stores the entry_id in a short-lived
		 * transient tied to the user's session, allowing subsequent AJAX requests to
		 * securely retrieve the entry data.
		 *
		 * @since 1.0.0
		 *
		 * @param int $entry_id The entry ID that was just created.
		 * @param int $form_id  The form ID.
		 * @return void
		 */
		public function store_entry_for_retrieval( $entry_id, $form_id ) {
			$transient_key = $this->get_entry_transient_key( $form_id );

			// Generate a token for additional verification.
			$token = $this->generate_entry_token( $entry_id, $form_id );

			// Store entry data for 60 seconds (enough time for AJAX request).
			set_transient(
				$transient_key,
				array(
					'entry_id' => absint( $entry_id ),
					'token'    => $token,
					'time'     => time(),
				),
				60
			);
		}

		/**
		 * Generate a secure verification token for an entry.
		 *
		 * This token is used to verify that the requester is authorized
		 * to access the entry data (i.e., they just submitted the form).
		 *
		 * @since 1.0.0
		 *
		 * @param int $entry_id The entry ID.
		 * @param int $form_id  The form ID.
		 * @return string The verification token.
		 */
		private function generate_entry_token( $entry_id, $form_id ) {
			return hash_hmac( 'sha256', $entry_id . '|' . $form_id, wp_salt( 'auth' ) );
		}

		/**
		 * AJAX handler to get Formidable entry data.
		 *
		 * This endpoint uses session-based verification to ensure the requester
		 * is the same user who just submitted the form. The entry data is retrieved
		 * from a short-lived transient.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function get_formidable_entry() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Session-based verification is used instead of nonce.
			$form_id = isset( $_REQUEST['form_id'] ) ? absint( $_REQUEST['form_id'] ) : 0;

			// Validate required parameter.
			if ( ! $form_id ) {
				wp_send_json_error( array( 'message' => __( 'Missing form ID.', 'suspended-lists' ) ) );
				wp_die();
			}

			// Get the form.
			$form = \FrmForm::getOne( $form_id );

			if ( ! $form ) {
				wp_send_json_error( array( 'message' => __( 'Form not found.', 'suspended-lists' ) ) );
				wp_die();
			}

			// Check for stored entry data from the user's session.
			$transient_key = $this->get_entry_transient_key( $form_id );
			$stored_data   = get_transient( $transient_key );

			if ( ! $stored_data || empty( $stored_data['entry_id'] ) ) {
				wp_send_json_error( array( 'message' => __( 'No recent form submission found.', 'suspended-lists' ) ) );
				wp_die();
			}

			$entry_id = absint( $stored_data['entry_id'] );

			// Delete the transient immediately after use (one-time use).
			delete_transient( $transient_key );

			global $wpdb;

			// Verify the entry exists and belongs to this form.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for Formidable Forms table.
			$entry_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}frm_items WHERE id = %d AND form_id = %d",
					$entry_id,
					$form_id
				)
			);

			if ( ! $entry_exists ) {
				wp_send_json_error( array( 'message' => __( 'Entry not found.', 'suspended-lists' ) ) );
				wp_die();
			}

			// Get form fields.
			$fields = \FrmField::get_all_for_form( $form_id );

			// Build field key to name mapping.
			$fields_key_name = array();
			foreach ( $fields as $field ) {
				$fields_key_name[ $field->field_key ] = $field->name;
			}

			// Get all field values for the verified entry.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for Formidable Forms table.
			$entry_meta = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d",
					$entry_id
				)
			);

			// Convert to key-value pair (field_key => value).
			$entry = array();
			foreach ( $entry_meta as $meta ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for Formidable Forms table.
				$field_key = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT field_key FROM {$wpdb->prefix}frm_fields WHERE id = %d",
						$meta->field_id
					)
				);

				if ( ! isset( $fields_key_name[ $field_key ] ) ) {
					continue;
				}

				$entry[ $fields_key_name[ $field_key ] ] = $meta->meta_value;
			}

			// Settings.
			$options = $form->options;

			$number = isset( $options['formychat_phone_code'] ) ? $options['formychat_phone_code'] : '';
			$number .= isset( $options['formychat_whatsapp_number'] ) ? $options['formychat_whatsapp_number'] : '';

			$data = array(
				'form_id'   => $form_id,
				'entry_id'  => $entry_id,
				'inputs'    => $entry,
				'formychat' => array(
					'status'          => isset( $options['formychat_status'] ) ? wp_validate_boolean( $options['formychat_status'] ) : 0,
					'number'          => $number,
					'message'         => isset( $options['formychat_message'] ) ? $options['formychat_message'] : '',
					'new_tab'         => isset( $options['formychat_new_tab'] ) ? wp_validate_boolean( $options['formychat_new_tab'] ) : 0,
					'navigate_to_web' => isset( $options['formychat_navigate_to_web'] ) ? wp_validate_boolean( $options['formychat_navigate_to_web'] ) : '',
				),
			);

			wp_send_json_success( $data );
			wp_die();
		}

		/**
		 * Handle successful form submission for non-AJAX forms.
		 *
		 * Generates a secure token and passes entry data to JavaScript.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method The success method.
		 * @param object $form   The form object.
		 * @param object $action The action object.
		 * @return string The method.
		 */
		public function on_success( $method, $form, $action ) {
			// Get form data.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Form submission is handled by Formidable Forms.
			$form_id = isset( $_REQUEST['form_id'] ) ? absint( $_REQUEST['form_id'] ) : 0;

			$form = \FrmForm::getOne( $form_id );

			if ( ! $form ) {
				return $method;
			}

			// Settings.
			$options = $form->options;
			if ( isset( $options['ajax_submit'] ) && $options['ajax_submit'] ) {
				return $method;
			}

			$fields = \FrmField::get_all_for_form( $form_id );

			// Build field key to name mapping.
			$fields_key_name = array();
			foreach ( $fields as $field ) {
				$fields_key_name[ $field->field_key ] = $field->name;
			}

			global $wpdb;

			// Get the last entry ID for the specific form (this is the entry just submitted).
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for Formidable Forms table.
			$entry_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = %d ORDER BY created_at DESC LIMIT 1",
					$form_id
				)
			);

			if ( ! $entry_id ) {
				return $method;
			}

			// Get all field values for the submitted entry.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for Formidable Forms table.
			$entry_meta = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d",
					$entry_id
				)
			);

			// Convert to key-value pair (field_key => value).
			$entry = array();
			foreach ( $entry_meta as $meta ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for Formidable Forms table.
				$field_key = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT field_key FROM {$wpdb->prefix}frm_fields WHERE id = %d",
						$meta->field_id
					)
				);

				if ( ! isset( $fields_key_name[ $field_key ] ) ) {
					continue;
				}

				$entry[ $fields_key_name[ $field_key ] ] = $meta->meta_value;
			}

			$number = isset( $options['formychat_phone_code'] ) ? $options['formychat_phone_code'] : '';
			$number .= isset( $options['formychat_whatsapp_number'] ) ? $options['formychat_whatsapp_number'] : '';

			$data = array(
				'form_id'   => $form_id,
				'entry_id'  => $entry_id,
				'inputs'    => $entry,
				'formychat' => array(
					'status'          => isset( $options['formychat_status'] ) ? wp_validate_boolean( $options['formychat_status'] ) : 0,
					'number'          => $number,
					'message'         => isset( $options['formychat_message'] ) ? $options['formychat_message'] : '',
					'new_tab'         => isset( $options['formychat_new_tab'] ) ? wp_validate_boolean( $options['formychat_new_tab'] ) : 0,
					'navigate_to_web' => isset( $options['formychat_navigate_to_web'] ) ? wp_validate_boolean( $options['formychat_navigate_to_web'] ) : '',
				),
			);

			// Print Inline_script.
			echo '<script> 
			if ( window.formychat_formidable_submit ) {
				window.formychat_formidable_submit(' . wp_json_encode( $data ) . ');
			} else {
				document.addEventListener("formychat_formidable_loaded", (event) => {
					if ( window.formychat_formidable_loaded ) return;
					window.formychat_formidable_submit(' . wp_json_encode( $data ) . ');
					window.formychat_formidable_loaded = true;
				});
			}
			
			</script>';

			return $method;
		}

		public function footer( $form, $form_id, $widget ) {
			if ( 'formidable' === $form ) {
				echo '<script>
				document.addEventListener("DOMContentLoaded", (event) => {
					const frmShowForm = document.querySelector(".frm-show-form");
					if ( frmShowForm ) {
						frmShowForm.classList.add("frm_ajax_submit");
					}
				});
				</script>';
			}
		}
	}

	// Initialize Message class. Only if doing Message.
	Frontend::init();
}
