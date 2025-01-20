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
				'/submit-form',
				[
					'methods'  => 'POST',
					'callback' => [ $this, 'handle_form_submission' ],
					'permission_callback' => '__return_true',
				]
			);

			// Get form.
			register_rest_route(
				'formychat/v1',
				'/get-form',
				[
					'methods'  => 'GET',
					'callback' => [ $this, 'get_form' ],
					'permission_callback' => '__return_true',
				]
			);
		}


		/**
		 * Handle form submission.
		 *
		 * @return void
		 */
		public function handle_form_submission( $request ) {
			$form_data = [
				'field' => $request->has_param('field') ? $request->get_param('field') : [],
				'meta' => $request->has_param('meta') ? $request->get_param('meta') : [],
				'widget_id' => $request->has_param('widget_id') ? $request->get_param('widget_id') : 0,
				'form_id' => $request->has_param('form_id') ? $request->get_param('form_id') : 0,
				'form' => $request->has_param('form') ? $request->get_param('form') : 'formychat',
			];

			do_action('formychat_form_submitted', $form_data, $request);

			$form_data = apply_filters('formychat_form_data', $form_data);

			$lead_id = Lead::create($form_data);

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

			// If widget is not found, return.
			if ( ! $widget ) {
				return;
			}

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
	}


	// Run.
	REST::init();
}
