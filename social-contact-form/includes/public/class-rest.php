<?php

/**
 * Public Ajax.
 * Handles all ajax requests from the public side.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace .
namespace FormyChat\Publics;

// Exit if accessed directly.
defined('ABSPATH') || exit;

// User models.
use FormyChat\Models\Lead;
use FormyChat\Models\LeadCF7;

if ( ! class_exists(__NAMESPACE__ . '\REST') ) {
	/**
	 * Public Ajax.
	 * Handles all ajax requests from the public side.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class REST extends \FormyChat\Base {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function actions() {
			add_action('rest_api_init', [ $this, 'register_routes' ]);

			// To support CF7.
			add_filter('wpcf7_feedback_response', [ $this, 'wpcf7_feedback_response' ], 10, 2);

			// Submitted form.
			add_action('formychat_lead_created', [ $this, 'formychat_lead_created' ], 10, 3);
		}

		/**
		 * Register REST routes.
		 *
		 * @return void
		 */
		public function register_routes() {
			register_rest_route(
				'formychat/v1',
				'/get-cf7-form',
				[
					'methods'  => 'GET',
					'callback' => [ $this, 'get_cf7_form' ],
					'permission_callback' => '__return_true',
				]
			);

			register_rest_route(
				'formychat/v1',
				'/submit-form',
				[
					'methods'  => 'POST',
					'callback' => [ $this, 'handle_form_submission' ],
					'permission_callback' => '__return_true',
				]
			);

			// IP Address.
			register_rest_route(
				'formychat/v1',
				'/ip-address',
				[
					'methods'  => 'GET',
					'callback' => [ $this, 'get_ip_address' ],
					'permission_callback' => '__return_true',
				]
			);
		}

		/**
		 * Manipulate CF7 response.
		 *
		 * @return void
		 */
		public function wpcf7_feedback_response( $response, $result ) {

			$formychat = get_post_meta($response['contact_form_id'], '_formy_chat_configuration', true);

			$form_options = [
				'status' => [ 'formy_chat_status', 'on' ],
				'country_code' => [ 'formy_chat_country_code', '44' ],
				'number' => [ 'formy_chat_number', '' ],
				'message' => [ 'formy_chat_message_fields', '*Name*: [your-name] ' . "\n" . '*Email*: [your-email]' . "\n" . '*Subject*: [your-subject]' . "\n" . '*Message*:' . "\n" . '[your-message]' . "\n" ],
				'new_tab' => [ 'formy_chat_tabs_status', 'off' ],
				'skip_email' => [ 'formy_chat_mail_status', 'off' ],
			];

			// Adjust the config, if new not set, try to get old.
			foreach ( $form_options as $key => $option ) {
				if ( ! isset( $formychat[ $key ] ) ) {
					$formychat[ $key ] = isset( $formychat[ $option[0] ]) ? $formychat[ $option[0] ] : $option[1];
				}
			}

			$response['formychat'] = $formychat;
			return $response;
		}

		/**
		 * Load CF7 form.
		 *
		 * @return void
		 */
		public function get_cf7_form( $request ) {
			$form_id = $request->has_param('form_id') ? intval($request->get_param('form_id')) : 0;

			// Bail if CF7 is not active.
			if ( ! class_exists('\WPCF7') ) {
				return rest_ensure_response([ 'message' => __('Contact Form 7 is not active.', 'formychat') ]);
			}

			// Get form.
			ob_start();

			echo do_shortcode(wp_sprintf('[contact-form-7 id=%s]', $form_id));

			$form = ob_get_clean();

			return rest_ensure_response(wp_unslash($form));
		}

		/**
		 * Handle form submission.
		 *
		 * @return void
		 */
		public function handle_form_submission( $request ) {
			$source = $request->has_param('source') ? $request->get_param('source') : 'formychat';

			$form_data = [
				'field' => $request->has_param('field') ? $request->get_param('field') : [],
				'meta' => $request->has_param('meta') ? $request->get_param('meta') : [],
			];

			do_action('formychat_form_submitted', $form_data, $request);

			$form_data = apply_filters('formychat_form_data', $form_data);

			// Create lead.
			if ( 'formychat' === $source ) {
				$form_data['widget_id'] = $request->has_param('widget_id') ? $request->get_param('widget_id') : 1;
				$lead_id = Lead::create($form_data);
			} else {
				$form_data['cf7_id'] = $request->has_param('cf7_id') ? $request->get_param('cf7_id') : 0;
				$lead_id = LeadCF7::create($form_data);
			}

			do_action('formychat_lead_created', $form_data, $lead_id, $request);

			wp_send_json_success(
				[
					'lead_id' => $lead_id,
				]
			);
			wp_die();
		}

		/**
		 * FormyChat Lead Created.
		 *
		 * @return void
		 */
		public function formychat_lead_created( $form_data, $lead_id, $request ) {
			// Bail, if widget_id is not set.
			if ( ! isset($form_data['widget_id']) ) {
				return;
			}

			$widget = \FormyChat\Models\Widget::find($form_data['widget_id']);

			$settings = $widget->config['email'];

			// Bail, if email is not enabled.
			if ( ! wp_validate_boolean($settings['enabled']) ) {
				return;
			}

			$to = wp_validate_boolean($settings['admin_email']) ? get_option('admin_email') : $settings['address'];

			// Bail, if email is not set.
			if ( empty($to) ) {
				return;
			}

			// Build data.
			$data = implode('<br/>', array_map(function ( $key, $value ) {
				return wp_sprintf('<strong>%s</strong>: %s', ucfirst($key), $value);
			}, array_keys($form_data['field']), $form_data['field']));

			// Build subject
			$subject = apply_filters('formychat_email_subject', wp_sprintf('New Lead from %s', get_bloginfo('name')), $form_data, $lead_id, $request);

			// Build body.
			$body = apply_filters(
				'formychat_email_body',
				wp_sprintf('Hi,<br/><br/>You have received a new lead from %s. <br/><br/>Please check the details below:<br/> %s <br/><br/><br/>Sent at %s<br/>Thank you.', get_bloginfo('name'), $data, gmdate('Y-m-d H:i:s')),
				$form_data,
				$lead_id,
				$request
			);

			$headers = apply_filters('formychat_email_headers', [
				'Content-Type: text/html; charset=UTF-8',
			], $form_data, $lead_id, $request);

			// Send email.
			try {
				wp_mail($to, $subject, $body, $headers);
			} catch (\Exception $e) { // phpcs:ignore
				// Log error.
			}
		}


		/**
		 * Get IP Address.
		 *
		 * @return void
		 */
		public function get_ip_address() {

			$ip = '';
			if ( isset($_SERVER['HTTP_CLIENT_IP']) && ! empty($_SERVER['HTTP_CLIENT_IP']) ) {
				$ip = sanitize_text_field( wp_unslash($_SERVER['HTTP_CLIENT_IP']) );
			} elseif ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
				$ip = sanitize_text_field( wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']) );
			} elseif ( isset($_SERVER['REMOTE_ADDR']) && ! empty($_SERVER['REMOTE_ADDR']) ) {
				$ip = sanitize_text_field( wp_unslash($_SERVER['REMOTE_ADDR']) );
			} else {
				return rest_ensure_response([ 'message' => __('Failed to get IP address.', 'formychat') ]);
			}

			$ip_details = wp_remote_get('http://ip-api.com/json/' . $ip);

			if ( is_wp_error($ip_details) ) {
				return rest_ensure_response([ 'message' => __('Failed to get IP address details.', 'formychat') ]);
			}

			$ip_details = json_decode(wp_remote_retrieve_body($ip_details));

			return rest_ensure_response($ip_details);
		}
	}


	// Run.
	REST::init();
}
