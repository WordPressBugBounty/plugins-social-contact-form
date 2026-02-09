<?php
/**
 * Translation Strings.
 *
 * Contains all translatable strings for FormyChat plugin.
 * Used with wp_localize_script() to provide translations to JavaScript.
 *
 * @package FormyChat
 * @since 1.0.0
 */

namespace FormyChat;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Strings class.
 * Returns all translatable strings for the plugin.
 *
 * @package FormyChat
 * @since 1.0.0
 */
class Strings {

	/**
	 * Get all translatable strings.
	 *
	 * This method returns an associative array of all strings used in
	 * the plugin's Vue components and admin interface.
	 *
	 * @since 1.0.0
	 * @return array Array of translation strings
	 */
	public static function get() {
		return [
			// Widget Management.
			'widget_name'        => __( 'Widget name', 'social-contact-form' ),
			'floating_widgets'   => __( 'Floating Widgets', 'social-contact-form' ),
			'create_new'         => __( 'Create new', 'social-contact-form' ),
			'upgrade_now'        => __( 'Upgrade now', 'social-contact-form' ),
			'loading_widgets'    => __( 'Please wait while we load your widgets.', 'social-contact-form' ),
			'loading_widget'     => __( 'Please wait while we load your widget', 'social-contact-form' ),
			'form_mode'          => __( 'Form mode', 'social-contact-form' ),
			'created_at'         => __( 'Created at', 'social-contact-form' ),
			'untitled_widget'    => __( 'Untitled widget', 'social-contact-form' ),
			'duplicate_widget_title' => __( 'Duplicate Widget?', 'social-contact-form' ),
			'duplicate_widget_message' => __( 'Please select a name for your new duplicated widget', 'social-contact-form' ),
			'creating'           => __( 'Creating', 'social-contact-form' ),
			'create_widget'      => __( 'Create Widget', 'social-contact-form' ),
			'edit_widget'        => __( 'Edit Widget', 'social-contact-form' ),
			'delete_widget'      => __( 'Delete Widget', 'social-contact-form' ),
			'duplicate_widget'   => __( 'Duplicate Widget', 'social-contact-form' ),
			'widget_list'        => __( 'Widget List', 'social-contact-form' ),
			'no_widgets'         => __( 'No widgets found', 'social-contact-form' ),
			'widgets'            => __( 'Widgets', 'social-contact-form' ),
			'formychat_widgets'  => __( 'FormyChat Widgets', 'social-contact-form' ),
			'get_started_with_formychat' => __( 'Get Started with FormyChat', 'social-contact-form' ),

			// Common Actions.
			'save'               => __( 'Save', 'social-contact-form' ),
			'save_changes'       => __( 'Save Changes', 'social-contact-form' ),
			'cancel'             => __( 'Cancel', 'social-contact-form' ),
			'delete'             => __( 'Delete', 'social-contact-form' ),
			'edit'               => __( 'Edit', 'social-contact-form' ),
			'close'              => __( 'Close', 'social-contact-form' ),
			'confirm'            => __( 'Confirm', 'social-contact-form' ),
			'back'               => __( 'Back', 'social-contact-form' ),
			'next'               => __( 'Next', 'social-contact-form' ),
			'next_arrow'         => __( 'Next →', 'social-contact-form' ),
			'prev_arrow'         => __( '← Prev', 'social-contact-form' ),
			'step'               => __( 'Step', 'social-contact-form' ),
			'create'             => __( 'Create', 'social-contact-form' ),
			'continue'           => __( 'Continue', 'social-contact-form' ),
			'duplicate'          => __( 'Duplicate', 'social-contact-form' ),
			'copy'               => __( 'Copy', 'social-contact-form' ),
			'add'                => __( 'Add', 'social-contact-form' ),
			'remove'             => __( 'Remove', 'social-contact-form' ),
			'update'             => __( 'Update', 'social-contact-form' ),
			'apply'              => __( 'Apply', 'social-contact-form' ),
			'reset'              => __( 'Reset', 'social-contact-form' ),

			// Agent Configuration.
			'agent_name'         => __( 'Agent Name', 'social-contact-form' ),
			'agent_default_name' => __( 'Agent {number}', 'social-contact-form' ),
			'whatsapp_number'    => __( 'WhatsApp Number', 'social-contact-form' ),
			'whatsapp_number_placeholder' => __( 'WhatsApp number', 'social-contact-form' ),
			'whatsapp_widget_display' => __( 'Choose how you prefer to display your widget', 'social-contact-form' ),
			'agent_name_example' => __( 'Example: John Doe', 'social-contact-form' ),
			'subtitle_designation' => __( 'Subtitle / Designation', 'social-contact-form' ),
			'subtitle_example'   => __( 'Example: Technical Support Executive', 'social-contact-form' ),
			'agent_whatsapp_number' => __( 'Agent WhatsApp number', 'social-contact-form' ),
			'move_up'            => __( 'Move up', 'social-contact-form' ),
			'move_down'          => __( 'Move down', 'social-contact-form' ),
			'add_one_agent_error' => __( 'Please add at least one agent', 'social-contact-form' ),
			'add_new_agent'      => __( 'Add new agent', 'social-contact-form' ),
			'behaviors'          => __( 'Behaviors', 'social-contact-form' ),
			'open_whatsapp_new_tab' => __( 'Open WhatsApp in a new tab', 'social-contact-form' ),
			'open_whatsapp_new_tab_hint' => __( 'Open the WhatsApp link in a new tab on Desktop. <br/><b>Note:</b> In Safari, WhatsApp will be opened in current tab.', 'social-contact-form' ),
			'whatsapp_web_version' => __( 'Navigate to WhatsApp Web from desktop', 'social-contact-form' ),
			'whatsapp_web_version_hint' => __( 'By enabling this feature, WhatsApp will be automatically opened on your web browser when accessed from a laptop/PC.', 'social-contact-form' ),
			'on_click_agent'     => __( 'On click Agent', 'social-contact-form' ),
			'agent_photo'        => __( 'Agent Photo', 'social-contact-form' ),
			'add_agent'          => __( 'Add Agent', 'social-contact-form' ),
			'delete_agent'       => __( 'Delete Agent', 'social-contact-form' ),
			'agent_title'        => __( 'Agent Title', 'social-contact-form' ),
			'agents'             => __( 'Agents', 'social-contact-form' ),
			'agent_designation'  => __( 'Agent Designation', 'social-contact-form' ),

			// Widget Settings Tabs.
			'customize'          => __( 'Customize', 'social-contact-form' ),
			'greetings'          => __( 'Greetings', 'social-contact-form' ),
			'triggers'           => __( 'Triggers', 'social-contact-form' ),
			'whatsapp'           => __( 'WhatsApp', 'social-contact-form' ),
			'header'             => __( 'Header', 'social-contact-form' ),
			'preview'            => __( 'Preview', 'social-contact-form' ),

			// Greetings & Messages.
			'greeting_message'   => __( 'Greeting Message', 'social-contact-form' ),
			'welcome_message'    => __( 'Welcome Message', 'social-contact-form' ),
			'offline_message'    => __( 'Offline Message', 'social-contact-form' ),
			'online_message'     => __( 'Online Message', 'social-contact-form' ),
			'reply_time'         => __( 'Reply Time', 'social-contact-form' ),

			// Customization Options.
			'widget_style'       => __( 'Widget Style', 'social-contact-form' ),
			'widget_position'    => __( 'Widget Position', 'social-contact-form' ),
			'button_color'       => __( 'Button Color', 'social-contact-form' ),
			'text_color'         => __( 'Text Color', 'social-contact-form' ),
			'background_color'   => __( 'Background Color', 'social-contact-form' ),
			'header_color'       => __( 'Header Color', 'social-contact-form' ),
			'border_radius'      => __( 'Border Radius', 'social-contact-form' ),
			'widget_size'        => __( 'Widget Size', 'social-contact-form' ),

			// Triggers.
			'trigger_settings'   => __( 'Trigger Settings', 'social-contact-form' ),
			'show_on_pages'      => __( 'Show on Pages', 'social-contact-form' ),
			'hide_on_pages'      => __( 'Hide on Pages', 'social-contact-form' ),
			'show_after'         => __( 'Show After', 'social-contact-form' ),
			'hide_after'         => __( 'Hide After', 'social-contact-form' ),
			'display_rules'      => __( 'Display Rules', 'social-contact-form' ),
			'activate_widget' => __( 'Activate Widget', 'social-contact-form' ),
			'time_delay' => __( 'Time delay', 'social-contact-form' ),
			'page_scroll' => __( 'Page Scroll', 'social-contact-form' ),
			'exit_intent' => __( 'Exit Intent', 'social-contact-form' ),
			'display_after' => __( 'Display after', 'social-contact-form' ),
			'seconds_when_page_loaded' => __( 'seconds when page is loaded', 'social-contact-form' ),
			'scrolling_on_the_page' => __( 'scrolling on the page', 'social-contact-form' ),
			'display_when_visitor_leave' => __( 'Display when visitor is about to leave', 'social-contact-form' ),
			'widget_turned_off' => __( 'Widget turned off', 'social-contact-form' ),
			'widget_turned_off_message' => __( 'This floating widget is currently turned off, would you like to turn it ON and save to show it on your site?', 'social-contact-form' ),
			'save_and_show' => __( 'Save & Show on my site', 'social-contact-form' ),
			'save_keep_off' => __( 'Just save and keep it off', 'social-contact-form' ),
			'targets' => __( 'Targets', 'social-contact-form' ),
			'exclude_pages' => __( 'Exclude pages', 'social-contact-form' ),
			'exclude_all_pages_except' => __( 'Exclude all pages excepts:', 'social-contact-form' ),
			'fluentcrm_settings' => __( 'FluentCRM Settings', 'social-contact-form' ),
			'send_leads_to_fluentcrm' => __( 'Send leads to FluentCRM', 'social-contact-form' ),
			'send_leads_to_mailchimp' => __( 'Send leads to Mailchimp', 'social-contact-form' ),

			// Success/Error Messages.
			'success_save'       => __( 'Successfully saved!', 'social-contact-form' ),
			'success_update'     => __( 'Successfully updated!', 'social-contact-form' ),
			'success_delete'     => __( 'Successfully deleted!', 'social-contact-form' ),
			'success_duplicate'  => __( 'Successfully duplicated!', 'social-contact-form' ),
			'error_save'         => __( 'Error saving changes', 'social-contact-form' ),
			'error_delete'       => __( 'Error deleting item', 'social-contact-form' ),
			'error_occurred'     => __( 'An error occurred', 'social-contact-form' ),

			// Confirmation Messages.
			'confirm_delete'     => __( 'Are you sure you want to delete this?', 'social-contact-form' ),
			'untitled_page'      => __( 'Untitled Page', 'social-contact-form' ),
			'confirm_delete_widget' => __( 'Are you sure you want to delete this widget?', 'social-contact-form' ),
			'confirm_delete_active_widget' => __( 'Deleting this will remove the widget permanently from your site. Are you sure you want to delete this widget permanently?', 'social-contact-form'),
			'widget_in_use'      => __( 'This widget is currently in use!', 'social-contact-form' ),
			'confirm_delete_agent' => __( 'Are you sure you want to delete this agent?', 'social-contact-form' ),
			'cannot_undo'        => __( 'This action cannot be undone.', 'social-contact-form' ),
			'widget_deleted'     => __( 'Widget deleted successfully', 'social-contact-form' ),
			'copy_of'            => __( 'Copy of {name}', 'social-contact-form' ),

			// Upgrade & Premium.
			'upgrade_to_pro'     => __( 'Upgrade to Pro', 'social-contact-form' ),
			'pro_feature'        => __( 'This is a Pro feature', 'social-contact-form' ),
			'try_premium'        => __( 'Try all the premium features for free.', 'social-contact-form' ),
			'unlock_feature'     => __( 'Unlock this feature', 'social-contact-form' ),
			'premium_only'       => __( 'Premium Only', 'social-contact-form' ),
			'get_pro'            => __( 'Get Pro', 'social-contact-form' ),

			// Integrations.
			'integrations'       => __( 'Integrations', 'social-contact-form' ),
			'integration_settings' => __( 'Integration Settings', 'social-contact-form' ),
			'connect'            => __( 'Connect', 'social-contact-form' ),
			'disconnect'         => __( 'Disconnect', 'social-contact-form' ),
			'connected'          => __( 'Connected', 'social-contact-form' ),
			'not_connected'      => __( 'Not Connected', 'social-contact-form' ),
			'configure'          => __( 'Configure', 'social-contact-form' ),

			// Leads.
			'leads'              => __( 'Leads', 'social-contact-form' ),
			'lead_name'          => __( 'Lead Name', 'social-contact-form' ),
			'lead_email'         => __( 'Lead Email', 'social-contact-form' ),
			'lead_phone'         => __( 'Lead Phone', 'social-contact-form' ),
			'lead_message'       => __( 'Lead Message', 'social-contact-form' ),
			'no_leads'           => __( 'No leads found', 'social-contact-form' ),
			'total_leads'        => __( 'Total Leads', 'social-contact-form' ),
			'view_lead'          => __( 'View Lead', 'social-contact-form' ),
			'delete_lead'        => __( 'Delete Lead', 'social-contact-form' ),

			// WooCommerce.
			'woocommerce'        => __( 'WooCommerce', 'social-contact-form' ),
			'product_page'       => __( 'Product Page', 'social-contact-form' ),
			'cart_page'          => __( 'Cart Page', 'social-contact-form' ),
			'checkout_page'      => __( 'Checkout Page', 'social-contact-form' ),
			'shop_page'          => __( 'Shop Page', 'social-contact-form' ),
			'enable_on_product'  => __( 'Enable on Product Pages', 'social-contact-form' ),
			'enable_on_cart'     => __( 'Enable on Cart Page', 'social-contact-form' ),
			'enable_on_checkout' => __( 'Enable on Checkout Page', 'social-contact-form' ),

			// Form Labels.
			'contact_form_7'     => __( 'Contact Form 7', 'social-contact-form' ),
			'name'               => __( 'Name', 'social-contact-form' ),
			'email'              => __( 'Email', 'social-contact-form' ),
			'phone'              => __( 'Phone', 'social-contact-form' ),
			'message'            => __( 'Message', 'social-contact-form' ),
			'subject'            => __( 'Subject', 'social-contact-form' ),
			'title'              => __( 'Title', 'social-contact-form' ),
			'description'        => __( 'Description', 'social-contact-form' ),
			'status'             => __( 'Status', 'social-contact-form' ),
			'date'               => __( 'Date', 'social-contact-form' ),
			'actions'            => __( 'Actions', 'social-contact-form' ),

			// Status & States.
			'active'             => __( 'Active', 'social-contact-form' ),
			'inactive'           => __( 'Inactive', 'social-contact-form' ),
			'enabled'            => __( 'Enabled', 'social-contact-form' ),
			'disabled'           => __( 'Disabled', 'social-contact-form' ),
			'online'             => __( 'Online', 'social-contact-form' ),
			'offline'            => __( 'Offline', 'social-contact-form' ),
			'loading'            => __( 'Loading...', 'social-contact-form' ),
			'saving'             => __( 'Saving...', 'social-contact-form' ),

			// Pagination.
			'previous'           => __( 'Previous', 'social-contact-form' ),
			'first'              => __( 'First', 'social-contact-form' ),
			'last'               => __( 'Last', 'social-contact-form' ),
			'showing'            => __( 'Showing', 'social-contact-form' ),
			'of'                 => __( 'of', 'social-contact-form' ),
			'results'            => __( 'results', 'social-contact-form' ),
			'per_page'           => __( 'per page', 'social-contact-form' ),

			// Search & Filter.
			'search'             => __( 'Search', 'social-contact-form' ),
			'filter'             => __( 'Filter', 'social-contact-form' ),
			'sort_by'            => __( 'Sort by', 'social-contact-form' ),
			'search_widgets'     => __( 'Search widgets...', 'social-contact-form' ),
			'search_leads'       => __( 'Search leads...', 'social-contact-form' ),
			'no_results'         => __( 'No results found', 'social-contact-form' ),
			'small'              => __( 'Small', 'social-contact-form' ),
			'medium'             => __( 'Medium', 'social-contact-form' ),
			'large'              => __( 'Large', 'social-contact-form' ),
			'custom'             => __( 'Custom', 'social-contact-form' ),
			'position'           => __( 'Position', 'social-contact-form' ),

			// Tooltips & Help.
			'help'               => __( 'Help', 'social-contact-form' ),
			'learn_more'         => __( 'Learn more', 'social-contact-form' ),
			'documentation'      => __( 'Documentation', 'social-contact-form' ),
			'support'            => __( 'Support', 'social-contact-form' ),

			// Custom CSS.
			'custom_css'         => __( 'Custom CSS', 'social-contact-form' ),
			'add_custom_css'     => __( 'Add Custom CSS', 'social-contact-form' ),
			'css_saved'          => __( 'CSS saved successfully', 'social-contact-form' ),

			// General.
			'settings'           => __( 'Settings', 'social-contact-form' ),
			'general'            => __( 'General', 'social-contact-form' ),
			'advanced'           => __( 'Advanced', 'social-contact-form' ),
			'appearance'         => __( 'Appearance', 'social-contact-form' ),
			'behavior'           => __( 'Behavior', 'social-contact-form' ),
			'display'            => __( 'Display', 'social-contact-form' ),
			'options'            => __( 'Options', 'social-contact-form' ),
			'select'             => __( 'Select', 'social-contact-form' ),
			'choose'             => __( 'Choose', 'social-contact-form' ),
			'upload'             => __( 'Upload', 'social-contact-form' ),
			'upload_image'       => __( 'Upload Image', 'social-contact-form' ),
			'remove_image'       => __( 'Remove Image', 'social-contact-form' ),
			'change_image'       => __( 'Change Image', 'social-contact-form' ),

			// Empty Widget.
			'empty_widget_message' => __( "Let's create your first widget and bring your dashboard to life!", 'social-contact-form' ),
			'watch_tutorial'     => __( 'Watch Tutorial', 'social-contact-form' ),
			'create_floating_widget' => __( 'Create Floating Widget', 'social-contact-form' ),

			// Not Found.
			'not_found'          => __( 'Not found', 'social-contact-form' ),

			// Integrations.
			'formychat_modules'  => __( 'FormyChat Modules', 'social-contact-form' ),
			'integrate_modules_subtitle' => __( 'Integrate your favorite modules and supercharge your workflow', 'social-contact-form' ),
			'tutorial'           => __( 'Tutorial', 'social-contact-form' ),
			'review_us'          => __( 'Review Us', 'social-contact-form' ),
			'search_modules'     => __( 'Search Modules', 'social-contact-form' ),
			'dont_see_tool'      => __( "Don't see your tool?", 'social-contact-form' ),
			'suggest_integration_text' => __( "Your favorite tool could be our next big integration. Don't miss the chance to get it added!", 'social-contact-form' ),
			'suggest_now'        => __( 'Suggest Now', 'social-contact-form' ),

			// Leads.
			'contact_form_leads' => __( 'Contact Form Leads', 'social-contact-form' ),
			'delete_lead'        => __( 'Delete Lead', 'social-contact-form' ),
			'delete_selected_leads' => __( 'Delete selected {count} Leads', 'social-contact-form' ),
			'confirm_delete_lead' => __( 'Are you sure you want to delete this lead?', 'social-contact-form' ),
			'confirm_delete_leads' => __( 'Are you sure you want to delete selected {count} leads?', 'social-contact-form' ),
			'export'             => __( 'Export', 'social-contact-form' ),
			'select_source'      => __( 'Select Source', 'social-contact-form' ),
			'select_widget'      => __( 'Select Widget', 'social-contact-form' ),
			'select_form'        => __( 'Select Form', 'social-contact-form' ),
			'to'                 => __( 'to', 'social-contact-form' ),
			'loading_leads'      => __( 'Please wait while we load your leads.', 'social-contact-form' ),
			'widget'             => __( 'Widget', 'social-contact-form' ),
			'sync'               => __( 'Sync', 'social-contact-form' ),
			'no_leads_found'     => __( 'No leads found.', 'social-contact-form' ),

			// Custom CSS.
			'custom_css_editor'  => __( 'Custom CSS Editor', 'social-contact-form' ),
			'save_changes'       => __( 'Save Changes', 'social-contact-form' ),
			'custom_css_description' => __( "Add custom CSS to customize your site's appearance", 'social-contact-form' ),
			'css_styling_guide'  => __( 'CSS Styling Guide', 'social-contact-form' ),
			'lines'              => __( 'lines', 'social-contact-form' ),
			'characters'         => __( 'characters', 'social-contact-form' ),
			'target_all_widgets' => __( 'Target all widgets', 'social-contact-form' ),
			'target_specific_widget' => __( 'Target specific widget', 'social-contact-form' ),
			'tip'                => __( 'Tip', 'social-contact-form' ),
			'css_inspector_tip'  => __( 'Use browser developer tools to inspect widget elements and find the exact selectors you need.', 'social-contact-form' ),
			'css_placeholder'    => __( '/** Enter your custom CSS here */', 'social-contact-form' ),
			'custom_css_saved'   => __( 'Custom CSS saved successfully!', 'social-contact-form' ),
			'error_saving_css'   => __( 'Error saving CSS. Please try again.', 'social-contact-form' ),
			'add_custom_css_hint' => __( 'Add custom CSS to customize your site\'s appearance', 'social-contact-form' ),
			'css_dev_tools_hint' => __( 'Use browser developer tools to inspect widget elements and find the exact selectors you need.', 'social-contact-form' ),
			'enter_custom_css_placeholder' => __( '/** Enter your custom CSS here */', 'social-contact-form' ),
			'failed_to_save_css' => __( 'Failed to save CSS', 'social-contact-form' ),

			// Customize.
			'customize_icon'     => __( 'Customize Icon', 'social-contact-form' ),
			'custom_icon'        => __( 'Custom Icon', 'social-contact-form' ),
			'icon_size'          => __( 'Icon Size', 'social-contact-form' ),
			'icon_size_hint'     => __( 'Select the size of the icon', 'social-contact-form' ),
			'custom_icon_size'   => __( 'Custom Icon Size', 'social-contact-form' ),
			'icon_position'      => __( 'Icon Position', 'social-contact-form' ),
			'icon_position_hint' => __( 'Select the position of the icon', 'social-contact-form' ),
			'custom_icon_position' => __( 'Custom Icon Position', 'social-contact-form' ),
			'display_floating_widget_on' => __( 'Display floating widget on', 'social-contact-form' ),
			'display_widget_hint' => __( 'Choose where the floating widget will be visible - Desktop or Mobile.', 'social-contact-form' ),
			'desktop'            => __( 'Desktop', 'social-contact-form' ),
			'mobile'             => __( 'Mobile', 'social-contact-form' ),
			'call_to_action'     => __( 'Call to action', 'social-contact-form' ),
			'show_call_to_action' => __( 'Show Call to action', 'social-contact-form' ),
			'call_to_action_text' => __( 'Call to action text', 'social-contact-form' ),
			'customize_form'     => __( 'Customize Form', 'social-contact-form' ),
			'choose_message_source' => __( 'Choose the source through which you want to send message to WhatsApp', 'social-contact-form' ),
			'select_form'        => __( 'Select a form', 'social-contact-form' ),
			'message_template'   => __( 'Message Template', 'social-contact-form' ),
			'message_template_hint' => __( 'Message template which will be sent to WhatsApp.', 'social-contact-form' ),

			// Lead.
			'synced'             => __( 'Synced', 'social-contact-form' ),
			'pending'            => __( 'Pending', 'social-contact-form' ),
			'form_submitted_data' => __( 'Form Submitted data', 'social-contact-form' ),
			'others_information' => __( 'Others Information', 'social-contact-form' ),
			'show_less'          => __( '− Show less', 'social-contact-form' ),
			'show_more'          => __( '+ Show more', 'social-contact-form' ),

			// WooCommerce.
			'formychat_woocommerce_buttons' => __( 'FormyChat WooCommerce Buttons', 'social-contact-form' ),
			'woocommerce_subtitle' => __( 'Connect your WooCommerce store with WhatsApp to enable one-click product inquiries and instant order requests.', 'social-contact-form' ),
			'menu'               => __( 'Menu', 'social-contact-form' ),
			'shop_page'          => __( 'Shop Page', 'social-contact-form' ),
			'product_page'       => __( 'Product Page', 'social-contact-form' ),
			'cart_page'          => __( 'Cart Page', 'social-contact-form' ),
			'checkout_page'      => __( 'Checkout Page', 'social-contact-form' ),

			// Preview & Agent.
			'create_first_agent' => __( 'Create your first agent', 'social-contact-form' ),

			// Upgrade/Campaign.
			'days'               => __( 'days', 'social-contact-form' ),
			'hours'              => __( 'hours', 'social-contact-form' ),
			'minutes'            => __( 'minutes', 'social-contact-form' ),
			'seconds'            => __( 'seconds', 'social-contact-form' ),
			'try_free_demo'      => __( 'Try a free demo', 'social-contact-form' ),

			// Greetings.
			'display_greeting_popup' => __( 'Display Greeting Popup', 'social-contact-form' ),
			'choose_greetings_template' => __( 'Choose Greetings Template', 'social-contact-form' ),
			'customize_greetings' => __( 'Customize Greetings', 'social-contact-form' ),
			'greeting_heading'   => __( 'Greeting Heading', 'social-contact-form' ),
			'heading_size'       => __( 'Heading Size', 'social-contact-form' ),
			'custom_heading_size' => __( 'Custom Heading Size', 'social-contact-form' ),
			'message_size'       => __( 'Message Size', 'social-contact-form' ),
			'custom_message_size' => __( 'Custom Message Size', 'social-contact-form' ),
			'greeting_colors'    => __( 'Greeting Colors', 'social-contact-form' ),
			'heading_color'      => __( 'Heading Color', 'social-contact-form' ),
			'message_color'      => __( 'Message Color', 'social-contact-form' ),
			'greetings_font_family' => __( 'Greetings Font Family', 'social-contact-form' ),
			'show_main_content'  => __( 'Show Main Content', 'social-contact-form' ),
			'greeting_message'   => __( 'Greeting Message', 'social-contact-form' ),
			'greeting_font_family' => __( 'Greeting Font Family', 'social-contact-form' ),
			'show_greetings_cta' => __( 'Show Greetings CTA', 'social-contact-form' ),
			'greeting_cta_text'  => __( 'Greeting CTA Text', 'social-contact-form' ),
			'cta_icon'           => __( 'CTA Icon', 'social-contact-form' ),
			'cta_style'          => __( 'CTA Style', 'social-contact-form' ),
			'show_greeting_cta'  => __( 'Show Greeting CTA', 'social-contact-form' ),
			'greeting_cta_heading' => __( 'Greeting CTA Heading', 'social-contact-form' ),
			'cta_heading_size'   => __( 'CTA Heading Size', 'social-contact-form' ),
			'custom_cta_heading_size' => __( 'Custom CTA Heading Size', 'social-contact-form' ),
			'greeting_cta_message' => __( 'Greeting CTA Message', 'social-contact-form' ),
			'cta_message_size'   => __( 'CTA Message Size', 'social-contact-form' ),
			'custom_cta_message_size' => __( 'Custom CTA Message Size', 'social-contact-form' ),
			'cta_colors'         => __( 'CTA Colors', 'social-contact-form' ),
			'greeting_behavior'  => __( 'Greeting Behavior', 'social-contact-form' ),
			'on_click_action'    => __( 'On click Action', 'social-contact-form' ),
			'enable_greetings'   => __( 'Enable Greetings', 'social-contact-form' ),
			'before_heading'     => __( 'Before Heading', 'social-contact-form' ),
			'after_heading'      => __( 'After Heading', 'social-contact-form' ),
			'after_message'      => __( 'After Message', 'social-contact-form' ),
			'before_cta'         => __( 'Before CTA', 'social-contact-form' ),
			'after_cta'          => __( 'After CTA', 'social-contact-form' ),
			'style_number'       => __( 'Style {number}', 'social-contact-form' ),
			'load_selected_form' => __( 'Load The Selected Form', 'social-contact-form' ),
			'redirect_whatsapp_directly' => __( 'Redirected to WhatsApp directly', 'social-contact-form' ),
			'cta_text_color'     => __( 'CTA Text Color', 'social-contact-form' ),
			'cta_background_color' => __( 'CTA Background Color', 'social-contact-form' ),
			'left'               => __( 'Left', 'social-contact-form' ),
			'right'              => __( 'Right', 'social-contact-form' ),
			'top'                => __( 'Top', 'social-contact-form' ),
			'bottom'             => __( 'Bottom', 'social-contact-form' ),

			// Pagination.
			'showing'            => __( 'Showing', 'social-contact-form' ),

			// WooCommerce Cart/Checkout.
			'coming_soon'        => __( 'Coming Soon', 'social-contact-form' ),
			'cart_page_button'   => __( 'Cart Page Button', 'social-contact-form' ),
			'checkout_page_button' => __( 'Checkout Page Button', 'social-contact-form' ),
			'wc_cart_coming_soon_desc' => __( "We're working on WhatsApp integration for the cart page. Stay tuned for updates!", 'social-contact-form' ),
			'wc_checkout_coming_soon_desc' => __( "We're working on WhatsApp integration for the checkout page. Stay tuned for updates!", 'social-contact-form' ),

			// WooCommerce Shop/Product Pages.
			'wc_shop_whatsapp_button' => __( 'WhatsApp Button for Shop Page', 'social-contact-form' ),
			'wc_shop_description' => __( 'Let customers contact you or place orders via WhatsApp directly from the shop page.', 'social-contact-form' ),
			'wc_show_button_shop' => __( 'Show WhatsApp Button on Shop Page', 'social-contact-form' ),
			'whatsapp_number'    => __( 'WhatsApp Number', 'social-contact-form' ),
			'valid_whatsapp_number_error' => __( 'Please enter a valid WhatsApp number (7-15 digits)', 'social-contact-form' ),
			'button_position'    => __( 'Button Position', 'social-contact-form' ),
			'button_text'        => __( 'Button Text', 'social-contact-form' ),
			'buy_on_whatsapp'    => __( 'Buy on WhatsApp', 'social-contact-form' ),
			'pre_filled_message' => __( 'Pre-filled Message', 'social-contact-form' ),
			'wc_message_tooltip' => __( 'Customize what message customers send when they click.', 'social-contact-form' ),
			'button_style'       => __( 'Button Style', 'social-contact-form' ),
			'wc_customize_button_appearance' => __( 'Customize the WhatsApp button appearance however you want.', 'social-contact-form' ),
			'reset'              => __( 'Reset', 'social-contact-form' ),
			'background_color'   => __( 'Background color', 'social-contact-form' ),
			'default_button_bg_color' => __( 'Default button background color', 'social-contact-form' ),
			'background_hover_color' => __( 'Background hover color', 'social-contact-form' ),
			'button_bg_hover_tooltip' => __( 'Button background color on hover', 'social-contact-form' ),
			'button_text_color_tooltip' => __( 'Button text color', 'social-contact-form' ),
			'text_hover_color'   => __( 'Text hover color', 'social-contact-form' ),
			'button_text_hover_tooltip' => __( 'Button text color on hover', 'social-contact-form' ),
			'button_border_radius' => __( 'Button Border Radius', 'social-contact-form' ),
			'open_whatsapp_new_tab' => __( 'Open WhatsApp in a new tab', 'social-contact-form' ),
			'hide_add_to_cart'   => __( 'Hide "Add to Cart" button', 'social-contact-form' ),
			'display_button_on'  => __( 'Display button on', 'social-contact-form' ),
			'desktop'            => __( 'Desktop', 'social-contact-form' ),
			'mobile'             => __( 'Mobile', 'social-contact-form' ),
			'less'               => __( 'Less', 'social-contact-form' ),
			'advanced'           => __( 'Advanced', 'social-contact-form' ),
			'discard'            => __( 'Discard', 'social-contact-form' ),
			'save_changes'       => __( 'Save Changes', 'social-contact-form' ),
			'reset_colors'       => __( 'Reset Colors', 'social-contact-form' ),
			'reset_colors_confirmation' => __( 'Are you sure you want to reset all colors to default?', 'social-contact-form' ),
			'cancel'             => __( 'Cancel', 'social-contact-form' ),
			'above_add_to_cart'  => __( 'Above "Add to Cart" button', 'social-contact-form' ),
			'below_add_to_cart'  => __( 'Below "Add to Cart" button', 'social-contact-form' ),

			// WooCommerce Product Page specific.
			'wc_product_whatsapp_button' => __( 'WhatsApp Button for Single Product Page', 'social-contact-form' ),
			'wc_product_description' => __( 'Let customers contact you or place orders via WhatsApp directly from the single product page.', 'social-contact-form' ),
			'wc_show_button_product' => __( 'Show WhatsApp Button on Single Product Page', 'social-contact-form' ),
			'advance'            => __( 'Advance', 'social-contact-form' ),
			'after_product_meta' => __( 'After product meta', 'social-contact-form' ),
			'after_short_description' => __( 'After product short description', 'social-contact-form' ),

			// WooCommerce Variables.
			'available_variables' => __( 'Available Variables', 'social-contact-form' ),
			'woocommerce_vars'   => __( 'WooCommerce Vars:', 'social-contact-form' ),
			'conditional_blocks' => __( 'Conditional Blocks:', 'social-contact-form' ),
			'global_vars'        => __( 'Global Vars:', 'social-contact-form' ),

			// FluentCRM Integration.
			'fluentcrm_description' => __( 'No extra setup needed. Just keep it enabled, and you can instantly connect any FormyChat widget with FluentCRM.', 'social-contact-form' ),
			'instructions'       => __( 'Instructions:', 'social-contact-form' ),
			'fluentcrm_step1'    => __( 'Create or edit a FormyChat widget.', 'social-contact-form' ),
			'fluentcrm_step2'    => __( 'Go to {step} stepper.', 'social-contact-form' ),
			'fluentcrm_step3'    => __( 'Search for "{option}" and {action} it.', 'social-contact-form' ),
			'fluentcrm_step4'    => __( 'Then setup with your preferred lists, tags, and other options.', 'social-contact-form' ),
			'fluentcrm_success'  => __( '🎉 That\'s it - your leads will start flowing directly into FluentCRM!', 'social-contact-form' ),
			'watch_tutorial'     => __( 'Watch Tutorial', 'social-contact-form' ),

			// Mailchimp Integration.
			'connect_mailchimp'  => __( 'Connect Mailchimp', 'social-contact-form' ),
			'mailchimp_description' => __( 'Login to your {account} to get your API key.', 'social-contact-form' ),
			'api_key'            => __( 'API Key', 'social-contact-form' ),
			'mailchimp_api_key_tooltip' => __( 'Get your API key from Mailchimp profile > Extras > API keys', 'social-contact-form' ),
			'mailchimp_api_key_help' => __( 'Get your API key from Mailchimp profile > Extras > API keys. {link}', 'social-contact-form' ),
			'connect'            => __( 'Connect', 'social-contact-form' ),
			'connecting'         => __( 'Connecting...', 'social-contact-form' ),
			'remove'             => __( 'Remove', 'social-contact-form' ),
			'connected_successfully' => __( 'Connected successfully', 'social-contact-form' ),
			'connection_failed'  => __( 'Connection failed. Please check your API key.', 'social-contact-form' ),
			'remove_api_key'     => __( 'Remove API Key', 'social-contact-form' ),
			'remove_api_key_confirmation' => __( 'Are you sure you want to remove the API key? This will disconnect your integration.', 'social-contact-form' ),
			'yes_remove_it'      => __( 'Yes, remove it!', 'social-contact-form' ),
			'removing'           => __( 'Removing...', 'social-contact-form' ),

			// WooCommerce MessageTemplateField.
			'default_whatsapp_message' => __( 'Default WhatsApp Message', 'social-contact-form' ),

			// Google Sheets SyncSettingsModal.
			'google_sheets_sync' => __( 'Google Sheets Sync', 'social-contact-form' ),
			'sheets_connect_first_warning' => __( 'Please connect your Google account in the <a href="admin.php?page=formychat-integrations" class="text-blue-600 hover:underline font-medium">Integrations</a> page first.', 'social-contact-form' ),
			'settings'           => __( 'Settings', 'social-contact-form' ),
			'spreadsheet'        => __( 'Spreadsheet', 'social-contact-form' ),
			'open'               => __( 'Open', 'social-contact-form' ),
			'change'             => __( 'Change', 'social-contact-form' ),
			'loading'            => __( 'Loading...', 'social-contact-form' ),
			'select_a_spreadsheet' => __( 'Select a spreadsheet', 'social-contact-form' ),
			'setting_up'         => __( 'Setting up...', 'social-contact-form' ),
			'select'             => __( 'Select', 'social-contact-form' ),
			'select_sheet_warning' => __( 'Note: Selecting a sheet will clear its existing data.', 'social-contact-form' ),
			'create_new_spreadsheet' => __( '+ Create a new spreadsheet', 'social-contact-form' ),
			'enter_spreadsheet_name' => __( 'Enter spreadsheet name', 'social-contact-form' ),
			'creating'           => __( 'Creating...', 'social-contact-form' ),
			'save_check'         => __( '✔ Save', 'social-contact-form' ),
			'tab_whatsapp'       => __( 'WhatsApp', 'social-contact-form' ),
			'tab_customize'      => __( 'Customize', 'social-contact-form' ),
			'tab_greetings'      => __( 'Greetings', 'social-contact-form' ),
			'tab_triggers'       => __( 'Triggers & Targeting', 'social-contact-form' ),
			'step'               => __( 'Step', 'social-contact-form' ),
			'create'             => __( 'Create', 'social-contact-form' ),
			'or_select_from_existing_spreadsheets' => __( 'Or select from existing spreadsheets', 'social-contact-form' ),
			'sync_frequency'     => __( 'Sync Frequency', 'social-contact-form' ),
			'sync_interval'      => __( 'Sync Interval', 'social-contact-form' ),
			'more_settings_arrow' => __( 'More settings \u2192', 'social-contact-form' ),
			'synced'             => __( 'synced', 'social-contact-form' ),
			'last'               => __( 'Last', 'social-contact-form' ),
			'syncing'            => __( 'Syncing...', 'social-contact-form' ),
			'synced_exclamation' => __( 'Synced!', 'social-contact-form' ),
			'sync_now'           => __( 'Sync Now', 'social-contact-form' ),
			'free_version_limit_reached' => __( 'Free version limit reached ({limit} rows). Upgrade to sync unlimited leads.', 'social-contact-form' ),
			'full_resync'        => __( 'Full Resync', 'social-contact-form' ),
			'full_resync_warning' => __( 'This will clear all existing data in the spreadsheet and re-sync all leads from scratch.', 'social-contact-form' ),
			'backup_spreadsheet_warning' => __( 'Please backup your spreadsheet before proceeding.', 'social-contact-form' ),
			'yes_resync'         => __( 'Yes, resync!', 'social-contact-form' ),

			// Google Sheets Integration (GoogleSheets.vue).
			'connect_google_sheets' => __( 'Connect Google Sheets', 'social-contact-form' ),
			'google_sheets_description' => __( 'Automatically sync your leads to Google Sheets in real-time. Keep track of all conversations in one place.', 'social-contact-form' ),
			'continue_with_google' => __( 'Continue with Google', 'social-contact-form' ),
			'connection_expired' => __( '<strong>{account}</strong> connection expired. Please reconnect.', 'social-contact-form' ),
			'your_account'       => __( 'Your account', 'social-contact-form' ),
			'google_oauth_tooltip' => __( 'You\'ll be redirected to Google to authorize access to your spreadsheets', 'social-contact-form' ),
			'account_connected_emoji' => __( 'Account connected 🎉', 'social-contact-form' ),
			'choose_one'         => __( '- choose one -', 'social-contact-form' ),
			'search_spreadsheets' => __( 'Search spreadsheets...', 'social-contact-form' ),
			'no_spreadsheets_found' => __( 'No spreadsheets found', 'social-contact-form' ),
			'select_sheet_will_clear_data' => __( 'Note: Selecting a sheet will clear its existing data.', 'social-contact-form' ),
			'or'                 => __( 'Or', 'social-contact-form' ),
			'create_new'         => __( 'Create new', 'social-contact-form' ),
			'formychat_leads_placeholder' => __( 'FormyChat Leads', 'social-contact-form' ),
			'select_existing'    => __( 'select existing', 'social-contact-form' ),
			'select_sync_mode'   => __( 'Select sync mode', 'social-contact-form' ),
			'select_interval'    => __( 'Select interval', 'social-contact-form' ),
			'saving'             => __( 'Saving...', 'social-contact-form' ),
			'save_and_sync'      => __( 'Save & Sync', 'social-contact-form' ),
			'connection_failed_try_again' => __( 'Connection failed. Please try again.', 'social-contact-form' ),
			'disconnect_google_account' => __( 'Disconnect Google Account', 'social-contact-form' ),
			'disconnect_google_warning' => __( 'Are you sure you want to disconnect? This will stop syncing form submissions to Google Sheets.', 'social-contact-form' ),
			'yes_disconnect'     => __( 'Yes, disconnect!', 'social-contact-form' ),
			'disconnecting'      => __( 'Disconnecting...', 'social-contact-form' ),
			'google_sheets_title' => __( 'Google Sheets', 'social-contact-form' ),
			'connect_google_desc' => __( 'Connect your Google account to sync all form submissions directly to {link} in real time.', 'social-contact-form' ),
			'choose_spreadsheet' => __( 'Choose Spreadsheet', 'social-contact-form' ),
			'scheduled_recommended' => __( 'Scheduled (Recommended)', 'social-contact-form' ),
			'real_time'          => __( 'Real-time', 'social-contact-form' ),
			'manual'             => __( 'Manual', 'social-contact-form' ),
			'never'              => __( 'Never', 'social-contact-form' ),
			'just_now'           => __( 'Just now', 'social-contact-form' ),
			'minute_ago'         => __( '1 minute ago', 'social-contact-form' ),
			'minutes_ago'        => __( '{minutes} minutes ago', 'social-contact-form' ),
			'failed_to_load_status' => __( 'Failed to load Google Sheets status', 'social-contact-form' ),
			'failed_to_disconnect' => __( 'Failed to disconnect.', 'social-contact-form' ),
			'failed_to_setup_spreadsheet' => __( 'Failed to setup spreadsheet.', 'social-contact-form' ),
			'failed_to_load_spreadsheets_error' => __( 'Failed to load spreadsheets. Please disconnect and reconnect your Google account.', 'social-contact-form' ),
			'failed_to_create_spreadsheet' => __( 'Failed to create spreadsheet.', 'social-contact-form' ),
			'settings_saved_success' => __( 'Settings saved successfully!', 'social-contact-form' ),
			'failed_to_save_settings' => __( 'Failed to save settings.', 'social-contact-form' ),
			'spreadsheet_initialized_success' => __( 'Spreadsheet "{name}" initialized and {count} lead(s) synced!', 'social-contact-form' ),
			'spreadsheet_created_success' => __( 'Spreadsheet "{name}" created and {count} lead(s) synced!', 'social-contact-form' ),
			'settings_saved'     => __( 'Settings saved!', 'social-contact-form' ),
			'full_resync_complete' => __( 'Full resync complete! Synced {count} lead(s).', 'social-contact-form' ),
			'sync_failed'        => __( 'Sync failed.', 'social-contact-form' ),
			'resync_failed'      => __( 'Resync failed.', 'social-contact-form' ),
			'failed_to_load_spreadsheets' => __( 'Failed to load spreadsheets', 'social-contact-form' ),

			// Triggers.vue - Integration Notices.
			'mailchimp_module_not_active' => __( 'Mailchimp module is not active.', 'social-contact-form' ),
			'activate_mailchimp_first' => __( 'To connect your leads with Mailchimp, please activate the module first.', 'social-contact-form' ),
			'activate_mailchimp_arrow' => __( 'Activate Mailchimp \u2192', 'social-contact-form' ),
			'mailchimp_api_not_connected' => __( 'Mailchimp API key is not connected.', 'social-contact-form' ),
			'connect_mailchimp_in_integrations' => __( 'Please connect your Mailchimp account by adding your API key in the integrations page.', 'social-contact-form' ),
			'go_to_mailchimp_module' => __( 'Go to Mailchimp Module \u2192', 'social-contact-form' ),
			'fluentcrm_not_installed' => __( 'FluentCRM is not installed.', 'social-contact-form' ),
			'install_fluentcrm_first' => __( 'To connect your leads with FluentCRM, please install and activate the plugin first.', 'social-contact-form' ),
			'install_fluentcrm_arrow' => __( 'Install FluentCRM \u2192', 'social-contact-form' ),
			'fluentcrm_not_active' => __( 'FluentCRM is not active.', 'social-contact-form' ),
			'activate_fluentcrm_plugin' => __( 'Please activate the FluentCRM plugin to send leads directly from FormyChat.', 'social-contact-form' ),
			'activate_fluentcrm_arrow' => __( 'Activate FluentCRM \u2192', 'social-contact-form' ),
			'fluentcrm_not_enabled' => __( 'FluentCRM is not enabled.', 'social-contact-form' ),
			'enable_fluentcrm_integration' => __( 'Please enable the FluentCRM integration to send leads directly from FormyChat.', 'social-contact-form' ),
			'enable_fluentcrm_arrow' => __( 'Enable FluentCRM \u2192', 'social-contact-form' ),

			// WooCommerce.vue - Header Buttons.
			'tutorial'           => __( 'Tutorial', 'social-contact-form' ),
			'documentation'      => __( 'Documentation', 'social-contact-form' ),
			'review_us'          => __( 'Review Us', 'social-contact-form' ),

			// Component Strings.
			// IntegrationCard.vue.
			'upcoming'           => __( 'Upcoming', 'social-contact-form' ),
			'installing'         => __(  'Installing...', 'social-contact-form' ),
			'activating'         => __( 'Activating...', 'social-contact-form' ),
			'not_connected'      => __( 'Not Connected', 'social-contact-form' ),
			'activate'           => __( 'Activate', 'social-contact-form' ),
			'install'            => __( 'Install', 'social-contact-form' ),
			
			// Upgrade.vue.
			'get_premium'        => __( 'GET PREMIUM', 'social-contact-form' ),
			
			// MessagePreset.vue.
			'reset_message_template' => __( 'Reset Message Template', 'social-contact-form' ),
			'reset_message_template_confirm' => __( 'Are you sure you want to reset the message template?', 'social-contact-form' ),
			'yes_reset'          => __( 'Yes, reset!', 'social-contact-form' ),
			'toggle_preview_preset' => __( 'Toggle Preview of Preset Message', 'social-contact-form' ),
			'keyboard_shortcuts' => __( 'Keyboard Shortcuts', 'social-contact-form' ),
			'whatsapp_formatting_note' => __( 'Some formatting rules are not supported in the WhatsApp Windows', 'social-contact-form' ),
			'preview_message'    => __( 'Preview Message', 'social-contact-form' ),

			// Customize.vue - Call to Action Section.
			'select_icon_size_hint' => __( 'Select the size of the icon', 'social-contact-form' ),
			'cta_style'          => __( 'Call to action style', 'social-contact-form' ),
			'text_size'          => __( 'Text Size', 'social-contact-form' ),
			'custom_text_size'   => __( 'Custom Text Size', 'social-contact-form' ),
			
			// Customize.vue - Email & Form Section.
			'receive_leads_email_hint' => __( 'Enable this option to receive all lead information to your given email address', 'social-contact-form' ),
			'receive_leads_via_email' => __( 'Receive leads via email', 'social-contact-form' ),
			'use_admin_email_receive' => __( 'Use admin email to receive leads', 'social-contact-form' ),
			'enter_email_address' => __( 'Enter email address', 'social-contact-form' ),
			'fluentsmtp'         => __( 'FluentSMTP', 'social-contact-form' ),
			'smtp_plugin_note'   => __( 'plugin to receive mail.', 'social-contact-form' ),
			'form_size'          => __( 'Form Size', 'social-contact-form' ),
			'custom_form_size'   => __( 'Custom Form Size', 'social-contact-form' ),
			'form_title_label'   => __( 'Form Title', 'social-contact-form' ),
			'form_title_placeholder' => __( 'Form Title (Ex: Contact via WhatsApp)', 'social-contact-form' ),
			'leave_blank_disable' => __( 'Leave blank to disable', 'social-contact-form' ),
			'header_text'        => __( 'Header Text', 'social-contact-form' ),
			'header_text_placeholder' => __( 'Header Text (Ex: Please fill up the fields below)', 'social-contact-form' ),
			'footer_text'        => __( 'Footer Text', 'social-contact-form' ),
			'footer_text_placeholder' => __( 'Footer Text (Ex: Powered by FormyChat)', 'social-contact-form' ),
			'submit_button_text' => __( 'Submit Button Text', 'social-contact-form' ),
			'submit_button_placeholder' => __( 'Button Text (Ex: Send on WhatsApp)', 'social-contact-form' ),
			'please_configure_smtp' => __( 'Please configure any SMTP e.g.', 'social-contact-form' ),
			'plugin_to_receive_mail' => __( 'plugin to receive mail.', 'social-contact-form' ),
			'or_text'            => __( 'or', 'social-contact-form' ),
			'smtp_config_note'   => __( 'Please configure any SMTP e.g. {wp_mail_smtp} or {fluent_smtp} plugin to receive mail.', 'social-contact-form' ),

			// Plugin Status Errors.
			'plugin_not_ready_error' => __( '{plugin} is not {status}! Please {action} it first to send unlimited messages through any of your {plugin} forms', 'social-contact-form' ),
			'install_and_activate' => __( 'Install & Activate', 'social-contact-form' ),
			'installed_status'   => __( 'installed', 'social-contact-form' ),
			'activated_status'   => __( 'activated', 'social-contact-form' ),
			'activate_plugin_forms' => __( '{plugin} is not {status}! Please activate it first to send unlimited messages through any of your {plugin} forms', 'social-contact-form' ),
			'country_code_hint'  => __( 'This will add country code with number to choose.', 'social-contact-form' ),
			'allow_country_code_select' => __( 'Allow users to select', 'social-contact-form' ),
			'country_code'       => __( 'country code', 'social-contact-form' ),
			'form_open_default_hint' => __( 'Open the form by default when the page is loaded.', 'social-contact-form' ),
			'open_by_default'    => __( 'Open by default', 'social-contact-form' ),
			'form_close_submit_hint' => __( 'Close the form after a successful submission.', 'social-contact-form' ),
			'close_form_on_submit' => __( 'Close', 'social-contact-form' ),
			'on_submit'          => __( 'on Submit', 'social-contact-form' ),
			'select_form'        => __( 'Select form', 'social-contact-form' ),
			'select_country'     => __( 'Select country', 'social-contact-form' ),
			'select_font'        => __( 'Select font', 'social-contact-form' ),

			// Triggers.vue - Integration Automation.
			'google_analytics'   => __( 'Google Analytics', 'social-contact-form' ),
			'google_analytics_hint' => __( 'Enable this option to track form submissions with Google Analytics.', 'social-contact-form' ),
			'fluentcrm_automation_hint' => __( 'Automatically send form leads to FluentCRM for email marketing campaigns and customer relationship management.', 'social-contact-form' ),
			'mailchimp_automation_hint' => __( 'Automatically send form leads to Mailchimp for email marketing campaigns.', 'social-contact-form' ),
			'select_lists_placeholder' => __( '- Select lists -', 'social-contact-form' ),
			'select_tag_placeholder' => __( '- Select tag -', 'social-contact-form' ),
			'select_field'       => __( 'Select field', 'social-contact-form' ),
			'select_fluentcrm_field' => __( 'Select FluentCRM field', 'social-contact-form' ),
			'skip_existing_contacts_hint' => __( 'If enabled, existing contacts won\'t be updated/duplicated.', 'social-contact-form' ),
			'skip_if_contacts_exist' => __( 'Skip if contacts already exist in', 'social-contact-form' ),
			'select_audiences_placeholder' => __( '- Select audiences -', 'social-contact-form' ),
			'select_tags_placeholder' => __( '- Select tags -', 'social-contact-form' ),
			'select_mailchimp_field' => __( 'Select Mailchimp field', 'social-contact-form' ),

			// Greetings.vue.
			'hi_any_queries_placeholder' => __( 'Hi! Have any queries?', 'social-contact-form' ),

			// Preview.vue.
			'contact_via_whatsapp' => __( 'Contact via WhatsApp', 'social-contact-form' ),
			'contact_via_whatsapp_placeholder' => __( 'Contact via WhatsApp', 'social-contact-form' ),
			'agent_name_placeholder' => __( 'Agent name', 'social-contact-form' ),
			'agent_subtitle_placeholder' => __( 'Agent subtitle', 'social-contact-form' ),
			'enter_phone_placeholder' => __( 'Enter your phone', 'social-contact-form' ),
			'enter_phone'        => __( 'Enter your phone', 'social-contact-form' ),
			'create_your_first_agent' => __( 'Create your first agent', 'social-contact-form' ),

			// Miscellaneous Placeholders.
			'default'            => __( 'Default', 'social-contact-form' ),
			'from'               => __( 'From', 'social-contact-form' ),
			'mailchimp_api_placeholder' => __( 'Ex: ca7*****************************-us14', 'social-contact-form' ),

			// Already defined above but included here for SyncSettingsModal (already in file).
			// 'select_sync_mode' => __( 'Select sync mode', 'social-contact-form' ),
			// 'select_interval' => __( 'Select interval', 'social-contact-form' ),

			// WooCommerce Pages (ProductPage, ShopPage, CartPage, CheckoutPage).
			'button_bg_color_tooltip' => __( 'Default button background color', 'social-contact-form' ),
			'hide_add_to_cart' => __( 'Hide "Add to Cart" button', 'social-contact-form' ),
			'display_button_on' => __( 'Display button on', 'social-contact-form' ),
			'desktop'            => __( 'Desktop', 'social-contact-form' ),
			'mobile'             => __( 'Mobile', 'social-contact-form' ),
			'open_whatsapp_new_tab' => __( 'Open WhatsApp in a new tab', 'social-contact-form' ),

			// Customize.vue - Form Integrations.
			'you_can'            => __( 'You can', 'social-contact-form' ),
			'disable_email_notifications' => __( 'disable email notifications', 'social-contact-form' ),
			'from_selected_cf7_form' => __( 'from the selected CF7 form.', 'social-contact-form' ),
			'select_a_form'      => __( 'Select a form', 'social-contact-form' ),

			// Triggers.vue - FluentCRM Field Mapping.
			'fluentcrm_lists_label' => __( 'FluentCRM List(s):', 'social-contact-form' ),
			'fluentcrm_tags_label' => __( 'FluentCRM Tag(s):', 'social-contact-form' ),
			'map_fields'         => __( 'Map Fields', 'social-contact-form' ),
			'map_email_fluentcrm_error' => __( 'Please map your form\'s email field to FluentCRM Email before saving.', 'social-contact-form' ),
			'your_form_fields'   => __( 'Your Form Fields', 'social-contact-form' ),
			'fluentcrm_field_label' => __( 'FluentCRM field', 'social-contact-form' ),

			// Triggers.vue - Mailchimp Field Mapping.
			'mailchimp_settings' => __( 'Mailchimp Settings', 'social-contact-form' ),
			'mailchimp_audiences_label' => __( 'Mailchimp Audience(s):', 'social-contact-form' ),
			'mailchimp_tags_label' => __( 'Mailchimp Tag(s):', 'social-contact-form' ),
			'map_email_mailchimp_error' => __( 'Please map your form\'s email field to Mailchimp Email before saving.', 'social-contact-form' ),
			'mailchimp_field_label' => __( 'Mailchimp field', 'social-contact-form' ),
			'note'               => __( 'Note:', 'social-contact-form' ),
			'address_mapping_note' => __( 'Address will be added only when all of the required fields are mapped (Street Address, City, State, Postal Code).', 'social-contact-form' ),
			'skip_if_contacts_exist_mailchimp' => __( 'Skip if contacts already exist in Mailchimp', 'social-contact-form' ),

			// Success Modal & Buttons (Triggers.vue, WooCommerce pages).
			'setup_finished'     => __( 'Setup Finished!', 'social-contact-form' ),
			'widget_updated'     => __( 'Widget Updated!', 'social-contact-form' ),
			'now_you_can_use'    => __( 'Now you can use', 'social-contact-form' ),
			'formychat'          => __( 'FormyChat', 'social-contact-form' ),
			'done'               => __( 'Done', 'social-contact-form' ),
			'upgrade_all_features' => __( 'Upgrade all features', 'social-contact-form' ),
			'save_changes'       => __( 'Save Changes', 'social-contact-form' ),
			'discard'            => __( 'Discard', 'social-contact-form' ),
		];
	}
}
