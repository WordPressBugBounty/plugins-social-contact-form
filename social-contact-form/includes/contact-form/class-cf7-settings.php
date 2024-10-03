<?php

/**
 * Form class.
 * Handles all Form requests.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace.
namespace FormyChat\ContactForm7;

// Exit if accessed directly.
defined('ABSPATH') || exit;


if ( ! class_exists(__NAMESPACE__ . '\Settings') ) {
	/**
	 * Form class.
	 * Handles all Form requests.
	 *
	 * @package FormyChat
	 * @since 1.0.0
	 */
	class Settings extends \FormyChat\Base {



		/**
		 * Register actions.
		 *
		 * @since 1.0.0
		 */
		public function actions() {
			add_filter('wpcf7_editor_panels', [ $this, 'register_formychat_tab' ], 10, 1);
			add_action('wpcf7_save_contact_form', [ $this, 'save_contact_form' ]);
			// Enqueue scripts.
			add_action('admin_enqueue_scripts', [ $this, 'enqueue_scripts' ]);
		}


		/**
		 * Enqueue scripts.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts( $hooks ) {

			// Check if the current page is any of the CF7 page.
			if ( ! in_array($hooks, [ 'toplevel_page_wpcf7', 'contact_page_wpcf7-new' ]) ) {
				return;
			}

			wp_enqueue_script('formychat-cf7-admin', FORMYCHAT_PUBLIC . '/js/admin-cf7.js', [], FORMYCHAT_VERSION, true);
			wp_enqueue_style('formychat-cf7-admin', FORMYCHAT_PUBLIC . '/css/admin-cf7.css', [], FORMYCHAT_VERSION);
		}

		/**
		 * Create tab inside the CF7.
		 *
		 * @param array $panels Plugin Tabs and callback.
		 *
		 * @return mixed
		 */
		public function register_formychat_tab( $panels ) {
			$panels['formychat'] = array(
				'title' => __('WhatsApp (FormyChat)', 'social-contact-form'),
				'callback' => array( $this, 'formychat_cf7_tab_callback' ),
			);
			return $panels;
		}

		public function print_tooltip( $text = '' ) {
			?> <span class="formychat-tooltip" data-tooltip="<?php echo esc_attr($text); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-question-circle-fill" viewBox="0 0 16 16">
					<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.496 6.033h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286a.237.237 0 0 0 .241.247m2.325 6.443c.61 0 1.029-.394 1.029-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94 0 .533.425.927 1.01.927z" />
				</svg>
			</span>
			<?php
		}

		/**
		 * Create tab and all its functionalities in CF7.
		 *
		 * @param array $form Plugin Id and post  meta.
		 *
		 * @return mixed
		 */
		public function formychat_cf7_tab_callback( $form ) {

			$is_ultimate = $this->is_ultimate_active();
			$form = \WPCF7_ContactForm::get_current();
			$ultimate = $this->is_ultimate_active() ? 'ultimate-mail' : 'scf-ultimate';

			$formychat = get_post_meta($form->id(), '_formy_chat_configuration', true);

			if ( ! $formychat ) {
				$formychat = [];
			}

			$form_options = [
				'status' => [ 'formy_chat_status', 'off' ],
				'country_code' => [ 'formy_chat_country_code', '44' ],
				'number' => [ 'formy_chat_number', '' ],
				'message' => [ 'formy_chat_message_fields', '*Name*: [your-name] ' . "\n" . '*Email*: [your-email]' . "\n" . '*Subject*: [your-subject]' . "\n" . '*Message*:' . "\n" . '[your-message]' . "\n" ],
				'new_tab' => [ 'formy_chat_tabs_status', 'off' ],
				'skip_email' => [ 'formy_chat_mail_status', 'off' ],
			];

			// If new key not set, try getting old key, otherwise set default value.
			foreach ( $form_options as $key => $value ) {
				$formychat[ $key ] = isset($formychat[ $key ]) ? $formychat[ $key ] : ( isset($formychat[ $value[0] ]) ? $formychat[ $value[0] ] : $value[1] );
			}
			?>
			<h2><?php esc_html_e('FormyChat', 'social-contact-form'); ?></h2>
			<fieldset>
				<legend>
					<?php echo wp_kses_post('Send messages through this form while submitting <strong>directly to your WhatsApp</strong> account account while submitting', 'social-contact-form'); ?>
				</legend>

				<table class="form-table formychat-settings-cf7">
					<tbody>

						<!-- status  -->
						<tr>
							<th scope="row">
								<div>
									<label for="formychat_status"> <?php esc_html_e('Connect WhatsApp', 'social-contact-form'); ?> </label>
									<?php $this->print_tooltip('Enabling  this feature will allow you to send the CF7 leads to your given WhatsApp number.'); ?>
								</div>
							</th>
							<td>
								<label class="formychat-switch">
									<input type="checkbox" class="formychat-checkbox" id="formychat_status" name="formychat_status" value="on" <?php checked('on', $formychat['status']); ?>
									oninput="t = document.querySelector('#formychat_number'); this.checked ? t.setAttribute('required', true) : t.removeAttribute('required')"	
									/>
									<span class="formychat-slider formychat-round"></span>
								</label>
								<p> <?php esc_html_e('Enable to connect WhatsApp for this contact form.', 'social-contact-form'); ?> </p>
							</td>
						</tr>

						<!-- WhatsApp Number  -->
						<tr>
							<th scope="row">
								<div>
									<label for="formychat_number"><?php esc_html_e('WhatsApp Number', 'social-contact-form'); ?></label>
									<?php $this->print_tooltip('Provide your WhatsApp number to get the leads.'); ?>
								</div>
							</th>

							<td>
								<div class="formychat-phone-field" tabindex="1">
									<div class="formychat-dropdown">
										<!-- input  -->
										<input type="hidden" class="formychat-dropdown-input" name="formychat_country_code" value="<?php echo esc_attr($formychat['country_code']); ?>">

										<!-- placeholder  -->
										<div class="formychat-dropdown-placeholder">
											<span>🇬🇧 +44</span>
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
												<path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
											</svg>
										</div>

										<!-- countries  -->
										<div class="formychat-dropdown-content">

											<input type="search" class="formychat-dropdown-content-search" placeholder="Search country" />

											<div class="formychat-dropdown-content-items">
												<?php
												$countries = \FormyChat\App::countries();

												foreach ( $countries as $country ) {
													?>
													<div class="formychat-dropdown-content-item <?php echo $formychat['country_code'] === $country['code'] ? 'selected' : ''; ?>" data-placeholder="<?php echo wp_sprintf('%s +%s', esc_attr($country['flag']), esc_attr($country['code'])); ?>" data-value="<?php echo esc_attr($country['code']); ?>" data-tags="<?php echo wp_sprintf('%s %s', esc_attr($country['code']), esc_attr($country['name'])); ?>">
														<?php echo wp_sprintf('%s (+%s) - %s', esc_attr($country['flag']), esc_attr($country['code']), esc_html($country['name'])); ?>
													</div>
													<?php
												}
												?>
											</div>

										</div>
									</div>
									<input type="text" <?php echo 'on' === $formychat['status'] ? 'required' : ''; ?> value="<?php echo esc_attr($formychat['number']); ?>" id="formychat_number" name="formychat_number" class="formychat-input-text" pattern="[0-9{8,15}]" placeholder="<?php esc_html_e('Phone number', 'social-contact-form'); ?>">
								</div>

							</td>
						</tr>


						<!-- Message template  -->
						<tr>
							<th scope="row">
								<div>
									<label><?php esc_html_e('WhatsApp message body', 'social-contact-form'); ?></label>
									<?php $this->print_tooltip('Decorate this message field how you want to get your user\'s leads.'); ?>
								</div>
							</th>
							<td>
								<?php esc_html_e('Use these tags in the message body:', 'social-contact-form'); ?>
								<p class="formychat-tags">
									<?php
									$form->suggest_mail_tags();
									$more_tags = [ 'site_title', 'site_description', 'site_url', 'page_url', 'admin_email', 'form_title', 'form_id' ];
									$more_tags = apply_filters('formychat_cf7_more_tags', $more_tags);

									foreach ( $more_tags as $tag ) {
										echo wp_sprintf('<span class="tag mailtag code used">[%s]</span>', esc_html($tag));
									}
									?>
								</p>

								<textarea id="formychat_message" id="formychat_message" name="formychat_message" rows="6" class="large-text code"><?php echo esc_html($formychat['message']); ?></textarea>
								<a target="_blank" style="text-decoration:underline" href="https://faq.whatsapp.com/general/chats/how-to-format-your-messages/?lang=en"><?php esc_html_e('Format your whatsapp message', 'social-contact-form'); ?></a>
								<p class="formychat-notes">
									<?php esc_html_e('Note:', 'social-contact-form'); ?>
									<i><?php esc_html_e('File Upload field will not support on WhatsApp message body.', 'social-contact-form'); ?></i>
								</p>
							</td>
						</tr>

						<!-- Open in new tab  -->
						<tr>
							<th scope="row">
								<div>
									<label><?php esc_html_e('Open in new tab', 'social-contact-form'); ?></label>
									<?php $this->print_tooltip('Enabling this feature will open the WhatsApp web in a new tab.'); ?>
								</div>
							</th>
							<td>
								<label class="formychat-switch">
									<input type="checkbox" class="formychat-checkbox" id="formychat_new_tab" name="formychat_new_tab" value="on" <?php checked('on', $formychat['new_tab']); ?> />
									>
									<span class="formychat-slider formychat-round"></span>
								</label>
								<p> <?php esc_html_e('Enable to open whatsapp in new tab.', 'social-contact-form'); ?></p>

								<div class="formychat-notes">
									<em>
										<?php esc_html_e('Note: This option is for only desktop devices, It will be useful for WhatsApp web on desktop devices.', 'social-contact-form'); ?>
									</em>
								</div>
							</td>
						</tr>

						<!-- Disable email notifications  -->
						<tr>
							<th scope="row">
								<div>
									<label><?php esc_html_e('Disable email notifications', 'social-contact-form'); ?></label>
									<?php $this->print_tooltip('Enabling this feature will disable the email functionality of this form.'); ?>
								</div>
							</th>
							<td>
								<div class="formychat-inline">
									<label class="formychat-switch">
										<input type="checkbox" class="formychat-checkbox" id="formychat_skip_email" name="formychat_skip_email" value="on" <?php checked('on', $formychat['skip_email']); ?> />
										<span class="formychat-slider formychat-round"></span>
									</label>
								</div>

								<p class=" "> <?php esc_html_e('Enable this toggle to stop receiving email notifications from this form.', 'social-contact-form'); ?> </p>

								<p class="formychat-notes">
									<?php esc_html_e('Note:', 'social-contact-form'); ?>
									<em><?php esc_html_e('With this function enabled, you can use this Contact Form 7 form without using any SMTP plugin.', 'social-contact-form'); ?></em>
								</p>
							</td>
						</tr>

						<!-- nonce  -->
						<input type="hidden" name="formychat-cf7-nonce" id="formychat-cf7-nonce" value="<?php echo esc_attr( wp_create_nonce('formychat_cf7_nonce') ); ?> ">
					</tbody>
				</table>
			</fieldset>
			<?php
		}

		/**
		 * Create tab and all its functionalities in CF7.
		 *
		 * @param object $cf7 CF7 Id and get form submitted data.
		 *
		 * @return mixed
		 */
		public function save_contact_form( $cf7 ) {
			// Verify nonce.
			if ( ! isset($_POST['formychat-cf7-nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['formychat-cf7-nonce'])), 'formychat_cf7_nonce') ) {
				return;
			}

			// Get form data.
			$formychat = array(
				'status' => isset($_POST['formychat_status']) ? sanitize_text_field( wp_unslash($_POST['formychat_status'])) : '',
				'country_code' => isset($_POST['formychat_country_code']) ? sanitize_text_field( wp_unslash($_POST['formychat_country_code'])) : '',
				'number' => isset($_POST['formychat_number']) ? sanitize_text_field( wp_unslash($_POST['formychat_number'])) : '',
				'message' => isset($_POST['formychat_message']) ? wp_unslash( $_POST['formychat_message'] ) : '', // phpcs:ignore
				'skip_email' => isset($_POST['formychat_skip_email']) ? sanitize_text_field( wp_unslash($_POST['formychat_skip_email'])) : '',
				'new_tab' => isset($_POST['formychat_new_tab']) ? sanitize_text_field( wp_unslash($_POST['formychat_new_tab'])) : '',
			);

			// Update post meta.
			update_post_meta($cf7->id, '_formy_chat_configuration', $formychat);
		}
	}

	// Initialize Form class. Only if doing Form.
	Settings::init();
}