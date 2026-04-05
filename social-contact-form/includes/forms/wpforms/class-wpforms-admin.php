<?php

/**
 * WPForms Settings.
 *
 * @since 1.0.0
 */
// Namespace .
namespace FormyChat\Forms\WPForms;

// Exit if accessed directly.
// phpcs:ignore Universal.PHP.DisallowExitDieParentheses.Found
defined('ABSPATH') || exit();

class Admin extends \FormyChat\Base {



    /**
     * Actions.
     *
     * @since 1.0.0
     */
    public function actions() {
        add_filter('wpforms_builder_settings_sections', [ $this, 'add_settings_section' ]);
        add_action('wpforms_form_settings_panel_content', [ $this, 'add_settings' ]);
    }

    /**
     * Add settings sidebar.
     *
     * @return void
     */
    public function add_settings_section( $sections ) {
        $sections['formychat'] = esc_html__('WhatsApp (FormyChat)', 'social-contact-form');
        return apply_filters('formychat_wpforms_sections', $sections);
    }

    /**
     * Add settings.
     *
     * @return void
     */
    public function add_settings( $settings ) {
        // Output the section title
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-formychat" data-panel="formychat" style="display: none;">';
        echo '<div class="wpforms-panel-content-section-title">';
        echo '<span id="wpforms-builder-settings-notifications-title">';
        esc_html_e('FormyChat Settings', 'social-contact-form');
        echo '</span>';

        echo '</div>';
        echo 'Send messages through this form while submitting directly to your WhatsApp account account while submitting';

        echo '<br/><br/><br/>';

        do_action('formychat_wpforms_settings_before_html', $settings);

        wpforms_panel_field(
            'toggle',
            'settings',
            'formychat_status',
            $settings->form_data,
            esc_html__('Connect WhatsApp', 'social-contact-form'),
            [
                'tooltip' => esc_html__('Enable WhatsApp notifications for this form.', 'social-contact-form'),
            ]
        );

        wpforms_panel_field(
            'select',
            'settings',
            'formychat_destination_type',
            $settings->form_data,
            esc_html__('Destination', 'social-contact-form'),
            [
                'options' => [
                    'phone' => esc_html__('Phone', 'social-contact-form'),
                    'group' => esc_html__('Group', 'social-contact-form') . ( $this->is_ultimate_active() ? '' : ' (Pro)' ),
                ],
                'default' => 'phone',
            ]
        );

        $destination_type = isset($settings->form_data['settings']['formychat_destination_type']) ? $settings->form_data['settings']['formychat_destination_type'] : 'phone';
        $show_phone = ( 'group' !== $destination_type );
        $show_group = ( 'group' === $destination_type );

        echo '<div class="formychat-wpforms-dest-phone" style="' . ( $show_phone ? '' : 'display:none;' ) . '">';
        $args = [
            'country_code' => array_key_exists('formychat_country_code', $settings->form_data['settings']) ? $settings->form_data['settings']['formychat_country_code'] : '',
            'number' => array_key_exists('formychat_number', $settings->form_data['settings']) ? $settings->form_data['settings']['formychat_number'] : '',
            'country_code_name' => 'settings[formychat_country_code]',
            'number_name' => 'settings[formychat_number]',
        ];
        formychat_phone_number_field($args);
        echo '</div>';

        echo '<div class="formychat-wpforms-dest-group" style="' . ( $show_group ? '' : 'display:none;' ) . '">';
        wpforms_panel_field(
            'text',
            'settings',
            'formychat_group_invite_code',
            $settings->form_data,
            esc_html__('Group Invite Link', 'social-contact-form'),
            [
                'placeholder' => 'https://chat.whatsapp.com/XXXXXXXXXX',
            ]
        );
        echo '</div>';

        echo '<br/>';

        $tags = [];

        if ( ! array_key_exists('fields', $settings->form_data) ) {
            $settings->form_data['fields'] = [];
        }

        $default_message = 'Thank you for contacting us. We will get back to you soon.';

        if ( $settings->form_data['fields'] && ! empty($settings->form_data['fields']) ) {

            $form_tags = array_column($settings->form_data['fields'], 'label');

            $tags = [];
            $default_message = '';
            foreach ( $form_tags as $tag ) {
                $tags[] = '<strong>{' . $tag . '}</strong>';
                $default_message .= $tag . ': {' . $tag . '}' . PHP_EOL;
            }

            // Merge if not empty.
            $custom_tags = array_keys(\FormyChat\App::custom_tags());
            if ( is_array($custom_tags) && ! empty($custom_tags) ) {
                $tags = array_merge(
                    $tags, array_map(
                        function ( $tag ) {
                            return '<strong>{' . $tag . '}</strong>';
                        }, $custom_tags
                    )
                );
            }
        }

        // Message body.
        wpforms_panel_field(
            'textarea',
            'settings',
            'formychat_message',
            $settings->form_data,
            esc_html__('WhatsApp Message Body', 'social-contact-form'),
            [
                'tooltip' => esc_html__('Enter the message that will be sent. Note: File Upload field will not support on WhatsApp message body. Use {FIELD_NAME} for dynamic field value.', 'social-contact-form'),
                'default' => $default_message,
            ]
        );

        if ( ! empty($tags) ) {

            echo '<div class="wpforms-panel-content-section-field-description">';
            printf(
                esc_html('Available tags: %s', 'social-contact-form'), // translators: %s - tags.
                implode( ', ', $tags ) // phpcs:ignore
            );
            echo '</div><br/>';
        }

        // Open in a new tab.
        wpforms_panel_field(
            'toggle',
            'settings',
            'formychat_new_tab',
            $settings->form_data,
            esc_html__('Open in a new tab', 'social-contact-form'),
            [
                'tooltip' => esc_html__('Enable to open whatsapp in new tab. Note: This option is for only desktop devices, It will be useful for WhatsApp web on desktop devices.', 'social-contact-form'),
            ]
        );

        do_action('formychat_wpforms_settings_after_html', $settings);

        ?>
        <script>
        (function(){
            var section = document.querySelector('.wpforms-panel-content-section-formychat');
            if (!section) return;
            var select = section.querySelector('select[name="settings[formychat_destination_type]"]') || section.querySelector('select[name*="formychat_destination_type"]');
            if (!select) return;
            function toggle() {
                var v = select.value || 'phone';
                section.querySelectorAll('.formychat-wpforms-dest-phone').forEach(function(el){ el.style.display = (v === 'phone') ? '' : 'none'; });
                section.querySelectorAll('.formychat-wpforms-dest-group').forEach(function(el){ el.style.display = (v === 'group') ? '' : 'none'; });
            }
            select.addEventListener('change', toggle);
            toggle();
        })();
        </script>
        <?php

        echo '</div>';
    }
}


// Init.
Admin::init();
