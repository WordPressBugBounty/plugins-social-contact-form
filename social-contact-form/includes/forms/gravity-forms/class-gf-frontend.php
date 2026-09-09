<?php

/**
 * GravityForms Frontend.
 *
 * @since 1.0.0
 */
// Namespace .
namespace FormyChat\Forms\GravityForms;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();

// phpcs:disable Universal.Operators.DisallowShortTernary -- Short ternary used for concise defaults.

class Frontend extends \FormyChat\Base {



    /**
     * Actions.
     *
     * @since 1.0.0
     */
    public function actions() {
        // Add ajax.
        add_action('wp_ajax_formychat_get_gf_entry', [ $this, 'get_entry' ]);
        add_action('wp_ajax_nopriv_formychat_get_gf_entry', [ $this, 'get_entry' ]);

        add_filter('gform_confirmation', [ $this, 'form_confirmation' ], 10, 3);

        // Store the just-submitted entry, bound to the submitter's session, for secure retrieval.
        add_action('gform_after_submission', [ $this, 'store_entry_for_retrieval' ], 10, 2);
    }

    /**
     * Unique-ish identifier for the current visitor.
     *
     * User ID when logged in, otherwise a salted hash of IP + User-Agent. Not full
     * session management, just enough to bind a retrievable entry to the browser that
     * actually submitted the form.
     *
     * @since 2.15.8
     *
     * @return string
     */
    private function get_session_identifier() {
        if ( is_user_logged_in() ) {
            return 'user_' . get_current_user_id();
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Used for hashing only.
        $ip = isset($_SERVER['REMOTE_ADDR']) ? wp_unslash($_SERVER['REMOTE_ADDR']) : '';
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Used for hashing only.
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? wp_unslash($_SERVER['HTTP_USER_AGENT']) : '';

        return 'guest_' . md5($ip . '|' . $ua . '|' . wp_salt('auth'));
    }

    /**
     * Transient key holding the retrievable entry for this visitor + form.
     *
     * @since 2.15.8
     *
     * @param  int $form_id Form ID.
     * @return string
     */
    private function get_entry_transient_key( $form_id ) {
        return 'formychat_gf_entry_' . md5($this->get_session_identifier() . '_' . absint($form_id));
    }

    /**
     * HMAC binding an entry ID to a form ID, verifiable without storing a secret.
     *
     * @since 2.15.8
     *
     * @param  int $entry_id Entry ID.
     * @param  int $form_id  Form ID.
     * @return string
     */
    private function generate_entry_token( $entry_id, $form_id ) {
        return hash_hmac('sha256', absint($entry_id) . '|' . absint($form_id), wp_salt('auth'));
    }

    /**
     * Persist the just-submitted entry ID for one-time, session-bound retrieval.
     *
     * Hooked on gform_after_submission so it runs on the same request the visitor made,
     * letting get_session_identifier() see their IP / User-Agent.
     *
     * @since 2.15.8
     *
     * @param  array $entry Gravity Forms entry.
     * @param  array $form  Gravity Forms form.
     * @return void
     */
    public function store_entry_for_retrieval( $entry, $form ) {
        $form_id  = isset($form['id']) ? absint($form['id']) : 0;
        $entry_id = isset($entry['id']) ? absint($entry['id']) : 0;

        if ( ! $form_id || ! $entry_id ) {
            return;
        }

        set_transient(
            $this->get_entry_transient_key($form_id),
            [
                'entry_id' => $entry_id,
                'token'    => $this->generate_entry_token($entry_id, $form_id),
                'time'     => time(),
            ],
            60
        );
    }

    /**
     * AJAX: return the entry the current visitor just submitted.
     *
     * The requested form ID only locates the caller's own one-time transient; it is
     * never used to fetch an arbitrary entry. Privileged users who can view entries
     * (e.g. previewing from the GF admin) may read the latest entry directly.
     *
     * @return void
     */
    public function get_entry() {

        if ( ! function_exists('rgget') || ! class_exists('\GFAPI') ) {
            wp_send_json_error();
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Session-bound one-time token is verified below.
        $form_id = isset($_REQUEST['id']) ? absint($_REQUEST['id']) : 0;

        if ( ! $form_id ) {
            wp_send_json_error();
        }

        $form = \GFAPI::get_form($form_id);

        if ( ! $form || empty($form['fields']) ) {
            wp_send_json_error();
        }

        // Privileged users (e.g. previewing from GF admin) may bypass the session gate.
        $can_view_any = current_user_can('gravityforms_view_entries');

        $entry = null;

        if ( $can_view_any ) {
            $entries = \GFAPI::get_entries($form_id);
            $entry   = ( is_array($entries) && count($entries) > 0 ) ? $entries[0] : null;
        } else {
            $transient_key = $this->get_entry_transient_key($form_id);
            $stored        = get_transient($transient_key);

            // One-time use: remove regardless of outcome.
            delete_transient($transient_key);

            if ( ! is_array($stored) || empty($stored['entry_id']) ) {
                wp_send_json_error([ 'message' => __('No recent form submission found.', 'social-contact-form') ]);
            }

            $entry_id       = absint($stored['entry_id']);
            $expected_token = $this->generate_entry_token($entry_id, $form_id);

            if ( ! isset($stored['token']) || ! hash_equals($expected_token, (string) $stored['token']) ) {
                wp_send_json_error([ 'message' => __('Invalid retrieval token.', 'social-contact-form') ]);
            }

            $candidate = \GFAPI::get_entry($entry_id);

            // Verify the stored entry really belongs to the requested form.
            if ( is_wp_error($candidate) || ! isset($candidate['form_id']) || absint($candidate['form_id']) !== $form_id ) {
                wp_send_json_error([ 'message' => __('Entry not found.', 'social-contact-form') ]);
            }

            $entry = $candidate;
        }

        if ( ! $entry ) {
            wp_send_json_error([ 'message' => __('No entry available.', 'social-contact-form') ]);
        }

        // Merge entry value acc
        $merged_entry = [];

        // Loop through the original array
        foreach ( $entry as $key => $value ) {
            // Skip non-scalar and empty values
            if ( ! is_scalar($value) || trim( (string) $value ) === '' ) {
                continue;
            }

            // Extract the base key before the dot (or the full key if no dot exists)
            $base_key = explode('.', $key)[0];

            // Merge values with the same base key
            if ( ! isset($merged_entry[ $base_key ]) ) {
                $merged_entry[ $base_key ] = (string) $value;
            } else {
                $merged_entry[ $base_key ] .= ' ' . (string) $value;
            }
        }

        $values = [];
        foreach ( $form['fields'] as $field ) {
            $id = $field->id;
            $label = $field->label;

            if ( array_key_exists($id, $merged_entry) ) {
                $values[ $label ] = $merged_entry[ $id ];
            }
        }

        wp_send_json_success(
            [
				'form' => $form,
				'formychat' => [
					'status' => gform_get_meta($form_id, 'formychat_status'),
					'destination_type' => gform_get_meta($form_id, 'formychat_destination_type') ?: 'phone',
					'group_invite_code' => gform_get_meta($form_id, 'formychat_group_invite_code') ?: '',
					'whatsapp_number' => gform_get_meta($form_id, 'formychat_country_code') . gform_get_meta($form_id, 'formychat_number'),
					'message' => gform_get_meta($form_id, 'formychat_message'),
					'new_tab' => gform_get_meta($form_id, 'formychat_new_tab'),
					'values' => $values,
				],
            ]
        );
    }

    /**
     * After Form confirmation.
     *
     * @param  array $confirmation
     * @param  array $form
     * @param  array $entry
     * @return array
     */
    public function form_confirmation( $confirmation, $form, $entry ) {

        // Bail if form is not enabled.
        if ( ! gform_get_meta($form['id'], 'formychat_status') ) {
            return $confirmation;
        }

        $settings = [
            'destination_type' => gform_get_meta($form['id'], 'formychat_destination_type') ?: 'phone',
            'group_invite_code' => gform_get_meta($form['id'], 'formychat_group_invite_code') ?: '',
            'whatsapp_number' => gform_get_meta($form['id'], 'formychat_country_code') . gform_get_meta($form['id'], 'formychat_number'),
            'message' => gform_get_meta($form['id'], 'formychat_message'),
            'new_tab' => gform_get_meta($form['id'], 'formychat_new_tab'),
        ];

        /**
         * Enqueue the formychat script in the footer.
         *
         * @param array $form     The form data.
         * @param array $entry    The entry data.
         * @param array $settings The settings data.
         */
        function enqueue_formychat_script( $form, $entry, $settings ) {
            // Create a unique ID to prevent potential script collision
            $script_id = 'formychat-script-' . uniqid();
            ?>
            <script id="<?php echo esc_attr($script_id); ?>" type="text/javascript">
            (function () {
                function submit(){
                    
                    window.gform_formychat(
                        <?php echo wp_json_encode($form); ?>, 
                        <?php echo wp_json_encode($entry); ?>, 
                        <?php echo wp_json_encode($settings); ?>
                    );
                }
                if ( window.gform_formychat ) {
                    submit();
                } else {
                    document.addEventListener("formychat_gf_loaded", function (e) {
                        submit();
                    });
                }
                
            })();
            </script>
            <?php
        }

        add_action(
            'wp_footer', function () use ( $form, $entry, $settings ) {
                enqueue_formychat_script($form, $entry, $settings);
            }
        );

        return $confirmation;
    }
}


// Init.
Frontend::init();
