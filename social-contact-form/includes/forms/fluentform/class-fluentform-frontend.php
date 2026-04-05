<?php
/**
 * FluentForm Frontend.
 *
 * @package FormyChat
 */
namespace FormyChat\FluentForm;

use FluentForm\App\Helpers\Helper;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();

// phpcs:disable Universal.Operators.DisallowShortTernary -- Short ternary used for concise defaults.

if ( ! class_exists(__NAMESPACE__ . '\Frontend') ) {
    /**
     * FluentForm Frontend.
     *
     * @package FormyChat
     */
    class Frontend extends \FormyChat\Base {

        // Actions.
        public function actions() {
            add_filter('fluentform/submission_confirmation', [ $this, 'submission_confirmation' ], 10, 3);
        }

        public function submission_confirmation( $return_data, $form, $confirmation ) {
            // Get the last submission for this form
            $submission = wpFluent()->table('fluentform_submissions')
                ->select([ 'id', 'response', 'created_at' ])
                ->where('form_id', $form->id)
                ->orderBy('id', 'DESC')
                ->first();

            if ( $submission ) {
                // Last Entry.
                $return_data['last_entry'] = json_decode($submission->response, true);
            }

            // Settings.
            $formychat_status = Helper::getFormMeta($form->id, 'formychat_status', '0');
            $formychat_destination_type = Helper::getFormMeta($form->id, 'formychat_destination_type', 'phone');
            $formychat_group_invite_code = Helper::getFormMeta($form->id, 'formychat_group_invite_code', '');
            $formychat_phone_code = Helper::getFormMeta($form->id, 'formychat_phone_code', '');
            $formychat_whatsapp_number = Helper::getFormMeta($form->id, 'formychat_whatsapp_number', '');
            $formychat_message_template = Helper::getFormMeta($form->id, 'formychat_message_template', '');
            $formychat_new_tab = Helper::getFormMeta($form->id, 'formychat_new_tab', '0');
            $formychat_web_version = Helper::getFormMeta($form->id, 'formychat_web_version', '0');

            // Meta is stored as string '1'/'0'; strict === 1 against int would never match.
            if ( wp_validate_boolean($formychat_status) || '1' === (string) $formychat_status ) {
                $return_data['formychat'] = [
                    'status' => 1,
                    'destination_type' => $formychat_destination_type ?: 'phone',
                    'group_invite_code' => $formychat_group_invite_code ?: '',
                    'whatsapp_number' => $formychat_phone_code . $formychat_whatsapp_number,
                    'message_template' => $formychat_message_template,
                    'new_tab' => wp_validate_boolean($formychat_new_tab),
                    'web_version' => wp_validate_boolean($formychat_web_version),
                ];
            }

            // Unset variables.
            unset($formychat_status);
            unset($formychat_phone_code);
            unset($formychat_whatsapp_number);
            unset($formychat_message_template);
            unset($formychat_new_tab);
            unset($formychat_web_version);

            return $return_data;
        }
    }

    // Init.
    Frontend::init();
}
