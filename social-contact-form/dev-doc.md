# FormyChat Developer Documentation

<div align="center">

**Complete Developer Reference for WordPress WhatsApp Integration & Lead Generation**

[![WordPress](https://img.shields.io/badge/WordPress-5.6+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

</div>

---

## 📋 Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [WordPress Hooks](#wordpress-hooks)
- [JavaScript Events](#javascript-events)
- [Frontend Variables](#frontend-variables)
- [Integration Examples](#integration-examples)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)
- [API Reference](#api-reference)

---

## 🚀 Overview

FormyChat is a powerful WordPress plugin that provides **WhatsApp integration** and **lead generation** capabilities through customizable chat widgets and form integrations. This documentation covers all developer interfaces for extending and customizing the plugin.

### Key Features
- ✅ **WhatsApp Integration** - Direct messaging and lead capture
- ✅ **Multi-Form Support** - Contact Form 7, Gravity Forms, WPForms, and more
- ✅ **Customizable Widgets** - Fully customizable chat interfaces
- ✅ **Lead Management** - Built-in lead tracking and management
- ✅ **Developer Friendly** - Extensive hooks, events, and variables

### Version Compatibility
| Component | Version |
|-----------|---------|
| WordPress | 5.6+ |
| PHP | 7.4+ |
| FormyChat | 2.0.0+ |
| Browser Support | Modern browsers (ES6+) |

---

## ⚡ Quick Start

### For WordPress Developers
```php
// Add custom action when form is submitted
add_action('formychat_form_submitted', function($form_data, $request) {
    // Your custom logic here
    sendToCRM($form_data);
}, 10, 2);

// Filter lead data before saving
add_filter('formychat_lead_data', function($form_data, $request) {
    $form_data['source'] = 'website';
    return $form_data;
}, 10, 2);
```

### For Frontend Developers
```javascript
// Listen for form submission events
document.addEventListener('formychat_form_submitted', function(event) {
    console.log('Form submitted:', event.detail);
    // Your custom logic here
});

// Access global variables
if (formychat_vars.user && formychat_vars.user.id) {
    console.log('User:', formychat_vars.user.name);
}
```

### For Integration Developers
```javascript
// Location-based customization
window.addEventListener('formychat_ip_loaded', function(event) {
    const ipData = event.detail;
    if (ipData.country === 'United States') {
        customizeForUS();
    }
});
```

---

## 🔧 WordPress Hooks

**When to use**: Server-side customization, data filtering, admin interface modifications  
**File location**: PHP files in your theme or plugin  
**Priority**: Use standard WordPress hook priorities (10, 20, etc.)

### Hook Categories
- [Form Submission Hooks](#form-submission-hooks) - Handle form data and submissions
- [Widget Management Hooks](#widget-management-hooks) - Manage widget lifecycle
- [Lead Management Hooks](#lead-management-hooks) - Handle lead creation and updates
- [Email Hooks](#email-hooks) - Customize email notifications
- [Form Integration Hooks](#form-integration-hooks) - Integrate with form plugins
- [Asset and Display Hooks](#asset-and-display-hooks) - Customize frontend assets
- [Admin Interface Hooks](#admin-interface-hooks) - Extend admin interface
- [Database Hooks](#database-hooks) - Handle database operations
- [Configuration Hooks](#configuration-hooks) - Customize plugin configuration
- [Plugin Integration Hooks](#plugin-integration-hooks) - Integrate with other plugins

---

## Table of Contents

1. [Form Submission Hooks](#form-submission-hooks)
2. [Widget Management Hooks](#widget-management-hooks)
3. [Lead Management Hooks](#lead-management-hooks)
4. [Email Hooks](#email-hooks)
5. [Form Integration Hooks](#form-integration-hooks)
6. [Asset and Display Hooks](#asset-and-display-hooks)
7. [Admin Interface Hooks](#admin-interface-hooks)
8. [Database Hooks](#database-hooks)
9. [Configuration Hooks](#configuration-hooks)
10. [Plugin Integration Hooks](#plugin-integration-hooks)

---

## 📝 Form Submission Hooks

**Use Cases**: [CRM Integration] [Analytics] [Email Notifications] [Lead Processing]  
**Complexity**: Beginner  
**Dependencies**: None

### `do_action('formychat_form_submitted', $form_data, $request)`

**Description:** Fired when a form is submitted through FormyChat.

**Parameters:**
- `$form_data` (array): Form submission data including field values, meta data, widget_id, form_id, and form type
- `$request` (WP_REST_Request): WordPress REST API request object

**Data Structure:**
```php
$form_data = [
    'field' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890'
    ],
    'meta' => [
        'Device' => 'Desktop',
        'Browser' => 'Chrome',
        'IP' => '192.168.1.1'
    ],
    'widget_id' => 123,
    'form_id' => 456,
    'form' => 'cf7'
];
```

**Example Usage:**
```php
add_action('formychat_form_submitted', function($form_data, $request) {
    try {
        // Log form submission
        error_log('Form submitted: ' . print_r($form_data, true));
        
        // Send to external service
        wp_remote_post('https://api.example.com/leads', [
            'body' => $form_data,
            'timeout' => 30
        ]);
        
        // Custom notification
        sendCustomNotification($form_data);
        
    } catch (Exception $e) {
        error_log('FormyChat submission error: ' . $e->getMessage());
    }
}, 10, 2);
```

**Common Use Cases:**
- Send leads to CRM systems
- Trigger email notifications
- Log form submissions
- Integrate with marketing tools

### `apply_filters('formychat_lead_data', $form_data, $request)`

**Description:** Filter the lead data before saving to database.

**Parameters:**
- `$form_data` (array): Form submission data
- `$request` (WP_REST_Request): WordPress REST API request object

**Returns:** (array) Modified form data

**Example Usage:**
```php
add_filter('formychat_lead_data', function($form_data, $request) {
    try {
        // Add custom field
        $form_data['field']['source'] = 'website';
        $form_data['field']['timestamp'] = current_time('mysql');
        
        // Sanitize and validate email
        if (isset($form_data['field']['email'])) {
            $email = sanitize_email($form_data['field']['email']);
            if (is_email($email)) {
                $form_data['field']['email'] = strtolower($email);
            }
        }
        
        // Add user agent information
        $form_data['meta']['User_Agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        return $form_data;
        
    } catch (Exception $e) {
        error_log('FormyChat lead data filter error: ' . $e->getMessage());
        return $form_data; // Return original data on error
    }
}, 10, 2);
```

**Common Use Cases:**
- Data sanitization and validation
- Add custom fields or metadata
- Format data before saving
- Enrich lead information

---

## Widget Management Hooks

### `do_action('formychat_print_widgets')`

**Description:** Fired before printing the widget container div.

**Parameters:** None

**Example Usage:**
```php
add_action('formychat_print_widgets', function() {
    echo '<div class="formychat-custom-header">Custom Header</div>';
});
```

### `do_action('formychat_widget_not_found_error', $widget_id)`

**Description:** Fired when a widget is not found during form rendering.

**Parameters:**
- `$widget_id` (int): The widget ID that was not found

**Example Usage:**
```php
add_action('formychat_widget_not_found_error', function($widget_id) {
    error_log("FormyChat widget not found: {$widget_id}");
    
    // Redirect to custom error page
    wp_redirect(home_url('/widget-error'));
    exit;
});
```

**Note:** Legacy hook `formychat_widget_not_found` is still supported for backward compatibility.

### `apply_filters('formychat_get_widgets', $widgets)`

**Description:** Filter the list of widgets returned by the admin API.

**Parameters:**
- `$widgets` (array): Array of widget objects

**Returns:** (array) Modified widgets array

**Example Usage:**
```php
add_filter('formychat_get_widgets', function($widgets) {
    // Filter out inactive widgets
    return array_filter($widgets, function($widget) {
        return $widget->is_active;
    });
});
```

### `apply_filters('formychat_get_widget', $widget)`

**Description:** Filter individual widget data.

**Parameters:**
- `$widget` (object): Widget object

**Returns:** (object) Modified widget object

**Example Usage:**
```php
add_filter('formychat_get_widget', function($widget) {
    // Add custom data to widget
    $widget->custom_field = 'custom_value';
    return $widget;
});
```

### `apply_filters('formychat_update_widget', $data, $widget_id)`

**Description:** Filter widget data before updating.

**Parameters:**
- `$data` (array): Widget data to be updated
- `$widget_id` (int): Widget ID being updated

**Returns:** (array) Modified widget data

**Example Usage:**
```php
add_filter('formychat_update_widget', function($data, $widget_id) {
    // Validate widget data
    if (isset($data['config']['form']['title']) && empty($data['config']['form']['title'])) {
        $data['config']['form']['title'] = 'Default Title';
    }
    
    return $data;
}, 10, 2);
```

### `do_action('formychat_widget_deleted', $ids)`

**Description:** Fired when widgets are deleted.

**Parameters:**
- `$ids` (array): Array of deleted widget IDs

**Example Usage:**
```php
add_action('formychat_widget_deleted', function($ids) {
    foreach ($ids as $widget_id) {
        // Clean up widget-specific data
        delete_option("formychat_widget_{$widget_id}_cache");
    }
});
```

---

## Lead Management Hooks

### `do_action('formychat_lead_created', $form_data, $lead_id, $request)`

**Description:** Fired when a new lead is created in the database.

**Parameters:**
- `$form_data` (array): Form submission data
- `$lead_id` (int): The ID of the created lead
- `$request` (WP_REST_Request): WordPress REST API request object

**Example Usage:**
```php
add_action('formychat_lead_created', function($form_data, $lead_id, $request) {
    // Send to CRM
    $crm_data = [
        'lead_id' => $lead_id,
        'email' => $form_data['field']['email'] ?? '',
        'name' => $form_data['field']['name'] ?? '',
        'phone' => $form_data['field']['phone'] ?? ''
    ];
    
    wp_remote_post('https://crm.example.com/api/leads', [
        'body' => $crm_data
    ]);
}, 10, 3);
```

### `apply_filters('formychat_get_leads', $leads, $filter)`

**Description:** Filter the leads returned by the admin API.

**Parameters:**
- `$leads` (array): Array of lead objects
- `$filter` (array): Filter parameters

**Returns:** (array) Modified leads array

**Example Usage:**
```php
add_filter('formychat_get_leads', function($leads, $filter) {
    // Add custom sorting
    if (isset($filter['sort_by']) && $filter['sort_by'] === 'custom') {
        usort($leads, function($a, $b) {
            return strcmp($a->field['name'], $b->field['name']);
        });
    }
    
    return $leads;
}, 10, 2);
```

### `do_action('formychat_leads_deleted', $ids, $form)`

**Description:** Fired when leads are deleted.

**Parameters:**
- `$ids` (array): Array of deleted lead IDs
- `$form` (string): Form type

**Example Usage:**
```php
add_action('formychat_leads_deleted', function($ids, $form) {
    // Log deletion for audit
    foreach ($ids as $lead_id) {
        error_log("Lead {$lead_id} deleted from form {$form}");
    }
    
    // Clean up external data
    foreach ($ids as $lead_id) {
        wp_remote_post('https://crm.example.com/api/leads/' . $lead_id, [
            'method' => 'DELETE'
        ]);
    }
}, 10, 2);
```

---

## Email Hooks

### `apply_filters('formychat_email_subject', $subject, $form_data, $lead_id, $request)`

**Description:** Filter the email subject for lead notifications.

**Parameters:**
- `$subject` (string): Default email subject
- `$form_data` (array): Form submission data
- `$lead_id` (int): Lead ID
- `$request` (WP_REST_Request): WordPress REST API request object

**Returns:** (string) Modified email subject

**Example Usage:**
```php
add_filter('formychat_email_subject', function($subject, $form_data, $lead_id, $request) {
    $form_type = $form_data['form'] ?? 'unknown';
    return "New {$form_type} Lead - {$subject}";
}, 10, 4);
```

### `apply_filters('formychat_email_body', $body, $form_data, $lead_id, $request)`

**Description:** Filter the email body for lead notifications.

**Parameters:**
- `$body` (string): Default email body
- `$form_data` (array): Form submission data
- `$lead_id` (int): Lead ID
- `$request` (WP_REST_Request): WordPress REST API request object

**Returns:** (string) Modified email body

**Example Usage:**
```php
add_filter('formychat_email_body', function($body, $form_data, $lead_id, $request) {
    // Add custom styling
    $body = str_replace('<br/>', '<br style="margin: 5px 0;"/>', $body);
    
    // Add lead ID to body
    $body .= "<br><br><strong>Lead ID:</strong> {$lead_id}";
    
    return $body;
}, 10, 4);
```

### `apply_filters('formychat_email_headers', $headers, $form_data, $lead_id, $request)`

**Description:** Filter the email headers for lead notifications.

**Parameters:**
- `$headers` (array): Default email headers
- `$form_data` (array): Form submission data
- `$lead_id` (int): Lead ID
- `$request` (WP_REST_Request): WordPress REST API request object

**Returns:** (array) Modified email headers

**Example Usage:**
```php
add_filter('formychat_email_headers', function($headers, $form_data, $lead_id, $request) {
    // Add custom headers
    $headers[] = 'X-Lead-ID: ' . $lead_id;
    $headers[] = 'X-Form-Type: ' . ($form_data['form'] ?? 'unknown');
    
    return $headers;
}, 10, 4);
```

---

## Form Integration Hooks

### `do_action('formychat_form_not_found_error', $form, $form_id, $widget)`

**Description:** Fired when a form is not found during rendering.

**Parameters:**
- `$form` (string): Form type (cf7, wpforms, etc.)
- `$form_id` (int): Form ID
- `$widget` (object): Widget object

**Example Usage:**
```php
add_action('formychat_form_not_found_error', function($form, $form_id, $widget) {
    echo '<div class="formychat-error">';
    echo '<h3>Form Not Found</h3>';
    echo '<p>The requested form (ID: ' . $form_id . ') could not be found.</p>';
    echo '</div>';
}, 10, 3);
```

**Note:** Legacy hook `formychat_form_not_found` is still supported for backward compatibility.

### `do_action('formychat_before_form', $form, $form_id, $widget)`

**Description:** Fired before rendering the form content.

**Parameters:**
- `$form` (string): Form type
- `$form_id` (int): Form ID
- `$widget` (object): Widget object

**Example Usage:**
```php
add_action('formychat_before_form', function($form, $form_id, $widget) {
    echo '<div class="formychat-form-header">';
    echo '<h2>' . esc_html($widget->config['form']['title']) . '</h2>';
    echo '<p>' . esc_html($widget->config['form']['subtitle']) . '</p>';
    echo '</div>';
}, 10, 3);
```

### `do_action('formychat_form_content', $form, $form_id, $widget)`

**Description:** Fired when rendering the main form content.

**Parameters:**
- `$form` (string): Form type
- `$form_id` (int): Form ID
- `$widget` (object): Widget object

**Example Usage:**
```php
add_action('formychat_form_content', function($form, $form_id, $widget) {
    // Custom form rendering logic
    if ($form === 'custom') {
        echo do_shortcode('[custom_form id="' . $form_id . '"]');
    }
}, 10, 3);
```

### `do_action('formychat_after_form', $form, $form_id, $widget)`

**Description:** Fired after rendering the form content.

**Parameters:**
- `$form` (string): Form type
- `$form_id` (int): Form ID
- `$widget` (object): Widget object

**Example Usage:**
```php
add_action('formychat_after_form', function($form, $form_id, $widget) {
    echo '<div class="formychat-form-footer">';
    echo '<p>By submitting this form, you agree to our terms and conditions.</p>';
    echo '</div>';
}, 10, 3);
```

### `do_action('formychat_head', $form, $form_id, $widget)`

**Description:** Fired in the head section of the form page.

**Parameters:**
- `$form` (string): Form type
- `$form_id` (int): Form ID
- `$widget` (object): Widget object

**Example Usage:**
```php
add_action('formychat_head', function($form, $form_id, $widget) {
    // Add custom meta tags
    echo '<meta name="formychat-widget" content="' . $widget->id . '">';
    echo '<meta name="formychat-form" content="' . $form . '">';
}, 10, 3);
```

### `do_action('formychat_footer', $form, $form_id, $widget)`

**Description:** Fired in the footer section of the form page.

**Parameters:**
- `$form` (string): Form type
- `$form_id` (int): Form ID
- `$widget` (object): Widget object

**Example Usage:**
```php
add_action('formychat_footer', function($form, $form_id, $widget) {
    // Add custom scripts
    echo '<script>';
    echo 'console.log("FormyChat form loaded: ' . $form . '");';
    echo '</script>';
}, 10, 3);
```

### `do_action("formychat_form_{$form}", $form_id, $widget)`

**Description:** Form-specific action hook for custom form types.

**Parameters:**
- `$form_id` (int): Form ID
- `$widget` (object): Widget object

**Example Usage:**
```php
add_action('formychat_form_custom', function($form_id, $widget) {
    // Handle custom form type
    echo '<div class="custom-form" data-form-id="' . $form_id . '">';
    echo '<input type="text" name="custom_field" placeholder="Custom Field">';
    echo '</div>';
}, 10, 2);
```

---

## Asset and Display Hooks

### `apply_filters('formychat_vars', $vars)`

**Description:** Filter the JavaScript variables passed to the frontend.

**Parameters:**
- `$vars` (array): JavaScript variables array

**Returns:** (array) Modified variables array

**Example Usage:**
```php
add_filter('formychat_vars', function($vars) {
    // Add custom variables
    $vars['custom_setting'] = get_option('formychat_custom_setting');
    $vars['user_role'] = wp_get_current_user()->roles[0] ?? 'guest';
    
    return $vars;
});
```

### `apply_filters('formychat_inline_css', $font_css)`

**Description:** Filter the inline CSS styles.

**Parameters:**
- `$font_css` (string): Font CSS styles

**Returns:** (string) Modified CSS styles

**Example Usage:**
```php
add_filter('formychat_inline_css', function($font_css) {
    // Add custom styles
    $custom_css = '
        .formychat-widget {
            z-index: 999999 !important;
        }
        .formychat-widget-form {
            border-radius: 10px !important;
        }
    ';
    
    return $font_css . $custom_css;
});
```

### `apply_filters('formychat_user_data', $user_data)`

**Description:** Filter the user data passed to the frontend.

**Parameters:**
- `$user_data` (array): User data array

**Returns:** (array) Modified user data array

**Example Usage:**
```php
add_filter('formychat_user_data', function($user_data) {
    $current_user = wp_get_current_user();
    
    if ($current_user->ID) {
        $user_data['is_logged_in'] = true;
        $user_data['user_id'] = $current_user->ID;
        $user_data['user_email'] = $current_user->user_email;
    }
    
    return $user_data;
});
```

### `apply_filters('formychat_custom_css', $css)`

**Description:** Filter the custom CSS for admin interface.

**Parameters:**
- `$css` (string): Custom CSS string

**Returns:** (string) Modified CSS string

**Example Usage:**
```php
add_filter('formychat_custom_css', function($css) {
    // Add admin-specific styles
    $admin_css = '
        .formychat-admin .formychat-widget {
            border: 2px solid #0073aa;
        }
    ';
    
    return $css . $admin_css;
});
```

### `apply_filters('formychat_admin_vars', $vars)`

**Description:** Filter the JavaScript variables for admin interface.

**Parameters:**
- `$vars` (array): Admin JavaScript variables

**Returns:** (array) Modified variables array

**Example Usage:**
```php
add_filter('formychat_admin_vars', function($vars) {
    // Add admin-specific variables
    $vars['admin_nonce'] = wp_create_nonce('formychat_admin');
    $vars['ajax_url'] = admin_url('admin-ajax.php');
    
    return $vars;
});
```

### `apply_filters('formychat_show_admin_bar', $show)`

**Description:** Filter whether to show admin bar on form pages.

**Parameters:**
- `$show` (bool): Whether to show admin bar

**Returns:** (bool) Modified show value

**Example Usage:**
```php
add_filter('formychat_show_admin_bar', function($show) {
    // Hide admin bar for non-admin users
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    return $show;
});
```

---

## Admin Interface Hooks

### `do_action('formychat_admin_menu')`

**Description:** Fired when building the admin menu.

**Parameters:** None

**Example Usage:**
```php
add_action('formychat_admin_menu', function() {
    // Add custom admin menu item
    add_submenu_page(
        'formychat',
        'Custom Page',
        'Custom Page',
        'manage_options',
        'formychat-custom',
        'formychat_custom_page_callback'
    );
});
```

**Note:** Legacy hook `formychat_admin_menu` is still supported for backward compatibility.

### `apply_filters('formychat_admin_rest_routes', $routes)`

**Description:** Filter the admin REST API routes.

**Parameters:**
- `$routes` (array): REST API routes array

**Returns:** (array) Modified routes array

**Example Usage:**
```php
add_filter('formychat_admin_rest_routes', function($routes) {
    // Add custom REST route
    $routes['custom'] = [
        'methods' => 'GET',
        'callback' => 'formychat_custom_route_callback',
    ];
    
    return $routes;
});
```

---

## Database Hooks

### `do_action('formychat_lead_table_created')`

**Description:** Fired when the leads table is created.

**Parameters:** None

**Example Usage:**
```php
add_action('formychat_lead_table_created', function() {
    // Add custom indexes or columns
    global $wpdb;
    $wpdb->query("ALTER TABLE {$wpdb->prefix}formychat_leads ADD INDEX idx_created_at (created_at)");
});
```

### `do_action('formychat_widget_table_created')`

**Description:** Fired when the widgets table is created.

**Parameters:** None

**Example Usage:**
```php
add_action('formychat_widget_table_created', function() {
    // Add custom indexes
    global $wpdb;
    $wpdb->query("ALTER TABLE {$wpdb->prefix}formychat_widgets ADD INDEX idx_is_active (is_active)");
});
```

### `do_action('formychat_widget_migrated', $payload)`

**Description:** Fired when widget data is migrated.

**Parameters:**
- `$payload` (array): Migration payload data

**Example Usage:**
```php
add_action('formychat_widget_migrated', function($payload) {
    // Log migration
    error_log('FormyChat widget migrated: ' . print_r($payload, true));
    
    // Update external systems
    if (isset($payload['widget_id'])) {
        wp_remote_post('https://api.example.com/migrate-widget', [
            'body' => $payload
        ]);
    }
});
```

---

## Configuration Hooks

### `apply_filters('formychat_fonts', $fonts)`

**Description:** Filter the available fonts list.

**Parameters:**
- `$fonts` (array): Array of font options

**Returns:** (array) Modified fonts array

**Example Usage:**
```php
add_filter('formychat_fonts', function($fonts) {
    // Add custom fonts
    $fonts['Custom Font'] = 'Custom Font';
    $fonts['Another Font'] = 'Another Font';
    
    return $fonts;
});
```

### `apply_filters('formychat_countries', $countries)`

**Description:** Filter the available countries list.

**Parameters:**
- `$countries` (array): Array of country options

**Returns:** (array) Modified countries array

**Example Usage:**
```php
add_filter('formychat_countries', function($countries) {
    // Add custom country
    $countries[] = [
        'name' => 'Custom Country',
        'code' => '999',
        'flag' => '🏳️',
    ];
    
    return $countries;
});
```

### `apply_filters('formychat_fonts_css', $css)`

**Description:** Filter the fonts CSS.

**Parameters:**
- `$css` (string): Fonts CSS string

**Returns:** (string) Modified CSS string

**Example Usage:**
```php
add_filter('formychat_fonts_css', function($css) {
    // Add custom font imports
    $custom_css = '@import url("https://fonts.googleapis.com/css2?family=Custom+Font&display=swap");';
    
    return $custom_css . $css;
});
```

### `apply_filters('formychat_widget_configuration', $configuration)`

**Description:** Filter the widget configuration options.

**Parameters:**
- `$configuration` (array): Widget configuration array

**Returns:** (array) Modified configuration array

**Example Usage:**
```php
add_filter('formychat_widget_configuration', function($configuration) {
    // Add custom configuration section
    $configuration['custom_section'] = [
        'title' => 'Custom Settings',
        'fields' => [
            'custom_field' => [
                'type' => 'text',
                'label' => 'Custom Field',
                'default' => '',
            ],
        ],
    ];
    
    return $configuration;
});
```

### `apply_filters('formychat_custom_tags', $tags)`

**Description:** Filter the custom tags available for message templates.

**Parameters:**
- `$tags` (array): Custom tags array

**Returns:** (array) Modified tags array

**Example Usage:**
```php
add_filter('formychat_custom_tags', function($tags) {
    // Add custom tags
    $tags['{custom_tag}'] = 'Custom Tag Description';
    $tags['{user_role}'] = 'Current User Role';
    
    return $tags;
});
```

### `apply_filters('formychat_forms', $forms)`

**Description:** Filter the available form types.

**Parameters:**
- `$forms` (array): Forms array

**Returns:** (array) Modified forms array

**Example Usage:**
```php
add_filter('formychat_forms', function($forms) {
    // Add custom form type
    $forms['custom_form'] = [
        'name' => 'Custom Form',
        'description' => 'Custom form integration',
        'fields' => ['name', 'email', 'message'],
    ];
    
    return $forms;
});
```

### `apply_filters('formychat_form_fields', $fields)`

**Description:** Filter the form fields.

**Parameters:**
- `$fields` (array): Form fields array

**Returns:** (array) Modified fields array

**Example Usage:**
```php
add_filter('formychat_form_fields', function($fields) {
    // Add custom field type
    $fields['custom_field'] = [
        'type' => 'custom',
        'label' => 'Custom Field',
        'required' => false,
    ];
    
    return $fields;
});
```

---

## Plugin Integration Hooks

### `apply_filters('formychat_form_plugins', $plugins)`

**Description:** Filter the list of supported form plugins.

**Parameters:**
- `$plugins` (array): Supported plugins array

**Returns:** (array) Modified plugins array

**Example Usage:**
```php
add_filter('formychat_form_plugins', function($plugins) {
    // Add custom plugin
    $plugins['custom_plugin'] = [
        'name' => 'Custom Plugin',
        'file' => 'custom-plugin/custom-plugin.php',
        'active' => is_plugin_active('custom-plugin/custom-plugin.php'),
    ];
    
    return $plugins;
});
```

### `do_action('formychat_plugin_activated', $plugin)`

**Description:** Fired when a form plugin is activated.

**Parameters:**
- `$plugin` (string): Plugin name

**Example Usage:**
```php
add_action('formychat_plugin_activated', function($plugin) {
    // Log plugin activation
    error_log("FormyChat: Plugin {$plugin} activated");
    
    // Send notification
    wp_mail(
        get_option('admin_email'),
        'Form Plugin Activated',
        "The plugin {$plugin} has been activated and is now available in FormyChat."
    );
});
```

### `do_action('formychat_country_code_updated', $code)`

**Description:** Fired when the default country code is updated.

**Parameters:**
- `$code` (string): New country code

**Example Usage:**
```php
add_action('formychat_country_code_updated', function($code) {
    // Update external systems
    update_option('external_country_code', $code);
    
    // Clear cache
    wp_cache_delete('formychat_country_settings');
});
```

---

## Form-Specific Field Hooks

### `apply_filters('formychat_form_fields_cf7', $fields, $form_id)`

**Description:** Filter Contact Form 7 fields.

**Parameters:**
- `$fields` (array): CF7 form fields
- `$form_id` (int): CF7 form ID

**Returns:** (array) Modified fields array

### `apply_filters('formychat_form_fields_gravity', $fields, $form_id)`

**Description:** Filter Gravity Forms fields.

**Parameters:**
- `$fields` (array): Gravity Forms fields
- `$form_id` (int): Gravity Forms form ID

**Returns:** (array) Modified fields array

### `apply_filters('formychat_form_fields_wpforms', $fields, $form_id)`

**Description:** Filter WPForms fields.

**Parameters:**
- `$fields` (array): WPForms fields
- `$form_id` (int): WPForms form ID

**Returns:** (array) Modified fields array

### `apply_filters('formychat_form_fields_fluentform', $fields, $form_id)`

**Description:** Filter Fluent Forms fields.

**Parameters:**
- `$fields` (array): Fluent Forms fields
- `$form_id` (int): Fluent Forms form ID

**Returns:** (array) Modified fields array

### `apply_filters('formychat_form_fields_forminator', $fields, $form_id)`

**Description:** Filter Forminator fields.

**Parameters:**
- `$fields` (array): Forminator fields
- `$form_id` (int): Forminator form ID

**Returns:** (array) Modified fields array

### `apply_filters('formychat_form_fields_formidable', $fields, $form_id)`

**Description:** Filter Formidable Forms fields.

**Parameters:**
- `$fields` (array): Formidable Forms fields
- `$form_id` (int): Formidable Forms form ID

**Returns:** (array) Modified fields array

### `apply_filters('formychat_form_fields_ninja', $fields, $form_id)`

**Description:** Filter Ninja Forms fields.

**Parameters:**
- `$fields` (array): Ninja Forms fields
- `$form_id` (int): Ninja Forms form ID

**Returns:** (array) Modified fields array

---

## Form Plugin Integration Hooks

### `apply_filters('formychat_get_cf7_forms', $forms)`

**Description:** Filter Contact Form 7 forms list.

**Parameters:**
- `$forms` (array): CF7 forms array

**Returns:** (array) Modified forms array

### `apply_filters('formychat_get_gravity_forms', $gravity_forms)`

**Description:** Filter Gravity Forms list.

**Parameters:**
- `$gravity_forms` (array): Gravity Forms array

**Returns:** (array) Modified forms array

### `apply_filters('formychat_get_wpforms_forms', $wpforms)`

**Description:** Filter WPForms list.

**Parameters:**
- `$wpforms` (array): WPForms array

**Returns:** (array) Modified forms array

### `apply_filters('formychat_get_fluentform_forms', $fluentform_forms)`

**Description:** Filter Fluent Forms list.

**Parameters:**
- `$fluentform_forms` (array): Fluent Forms array

**Returns:** (array) Modified forms array

### `apply_filters('formychat_get_forminator_forms', $forminator_forms)`

**Description:** Filter Forminator forms list.

**Parameters:**
- `$forminator_forms` (array): Forminator forms array

**Returns:** (array) Modified forms array

### `apply_filters('formychat_get_formidable_forms', $formidable_forms)`

**Description:** Filter Formidable Forms list.

**Parameters:**
- `$formidable_forms` (array): Formidable Forms array

**Returns:** (array) Modified forms array

### `apply_filters('formychat_get_ninja_forms', $ninja_forms)`

**Description:** Filter Ninja Forms list.

**Parameters:**
- `$ninja_forms` (array): Ninja Forms array

**Returns:** (array) Modified forms array

---

## Admin Panel Hooks

### `apply_filters('formychat_cf7_panels', $panels)`

**Description:** Filter Contact Form 7 admin panels.

**Parameters:**
- `$panels` (array): CF7 panels array

**Returns:** (array) Modified panels array

### `apply_filters('formychat_wpforms_sections', $sections)`

**Description:** Filter WPForms admin sections.

**Parameters:**
- `$sections` (array): WPForms sections array

**Returns:** (array) Modified sections array

### `do_action('formychat_wpforms_settings_before_html', $settings)`

**Description:** Fired before WPForms settings HTML.

**Parameters:**
- `$settings` (array): WPForms settings

### `do_action('formychat_wpforms_settings_after_html', $settings)`

**Description:** Fired after WPForms settings HTML.

**Parameters:**
- `$settings` (array): WPForms settings

### `apply_filters('formychat_fluentform_menu_items', $menu_items)`

**Description:** Filter Fluent Forms menu items.

**Parameters:**
- `$menu_items` (array): Fluent Forms menu items

**Returns:** (array) Modified menu items array

### `apply_filters('formychat_formidable_sections', $sections)`

**Description:** Filter Formidable Forms sections.

**Parameters:**
- `$sections` (array): Formidable Forms sections

**Returns:** (array) Modified sections array

### `apply_filters('formychat_gravityforms_menu_items', $menu_items)`

**Description:** Filter Gravity Forms menu items.

**Parameters:**
- `$menu_items` (array): Gravity Forms menu items

**Returns:** (array) Modified menu items array

---


## Best Practices

### 1. **Hook Priority**
Always specify the priority and number of arguments for your hooks:
```php
add_action('formychat_form_submitted', 'my_callback_function', 10, 2);
add_filter('formychat_lead_data', 'my_filter_function', 10, 2);
```

### 2. **Error Handling**
Always include error handling in your hook callbacks:
```php
add_action('formychat_lead_created', function($form_data, $lead_id, $request) {
    try {
        // Your code here
    } catch (Exception $e) {
        error_log('FormyChat hook error: ' . $e->getMessage());
    }
}, 10, 3);
```

### 3. **Data Validation**
Always validate and sanitize data in filter callbacks:
```php
add_filter('formychat_lead_data', function($form_data, $request) {
    if (isset($form_data['field']['email'])) {
        $form_data['field']['email'] = sanitize_email($form_data['field']['email']);
    }
    return $form_data;
}, 10, 2);
```

### 4. **Performance Considerations**
- Avoid heavy operations in action hooks that run frequently
- Use caching for expensive operations
- Consider using `wp_schedule_single_event()` for heavy tasks

### 5. **Security**
- Always validate user capabilities before performing admin actions
- Sanitize and validate all data
- Use nonces for form submissions

---

## Common Use Cases

### 1. **Custom Form Integration**
```php
// Add custom form type
add_filter('formychat_forms', function($forms) {
    $forms['my_custom_form'] = [
        'name' => 'My Custom Form',
        'description' => 'Integration with my custom form plugin',
    ];
    return $forms;
});

// Handle custom form fields
add_filter('formychat_form_fields_my_custom_form', function($fields, $form_id) {
    // Get fields from your custom form
    $custom_fields = get_my_custom_form_fields($form_id);
    
    // Convert to FormyChat format
    foreach ($custom_fields as $field) {
        $fields[] = [
            'name' => $field->name,
            'label' => $field->label,
            'type' => $field->type,
            'required' => $field->required,
        ];
    }
    
    return $fields;
}, 10, 2);
```

### 2. **CRM Integration**
```php
// Send leads to CRM
add_action('formychat_lead_created', function($form_data, $lead_id, $request) {
    $crm_data = [
        'lead_id' => $lead_id,
        'email' => $form_data['field']['email'] ?? '',
        'name' => $form_data['field']['name'] ?? '',
        'phone' => $form_data['field']['phone'] ?? '',
        'source' => 'formychat',
        'created_at' => current_time('mysql'),
    ];
    
    wp_remote_post('https://your-crm.com/api/leads', [
        'body' => $crm_data,
        'timeout' => 30,
    ]);
}, 10, 3);
```

### 3. **Custom Email Templates**
```php
// Customize email subject
add_filter('formychat_email_subject', function($subject, $form_data, $lead_id, $request) {
    $form_type = $form_data['form'] ?? 'unknown';
    $name = $form_data['field']['name'] ?? 'Unknown';
    
    return "New {$form_type} Lead from {$name}";
}, 10, 4);

// Customize email body
add_filter('formychat_email_body', function($body, $form_data, $lead_id, $request) {
    $custom_body = '<h2>New Lead Received</h2>';
    $custom_body .= '<p><strong>Lead ID:</strong> ' . $lead_id . '</p>';
    $custom_body .= '<p><strong>Form Type:</strong> ' . ($form_data['form'] ?? 'Unknown') . '</p>';
    
    foreach ($form_data['field'] as $field_name => $field_value) {
        $custom_body .= '<p><strong>' . ucfirst($field_name) . ':</strong> ' . esc_html($field_value) . '</p>';
    }
    
    return $custom_body;
}, 10, 4);
```

### 4. **Custom Validation**
```php
// Add custom validation
add_filter('formychat_lead_data', function($form_data, $request) {
    // Validate email format
    if (isset($form_data['field']['email']) && !is_email($form_data['field']['email'])) {
        wp_send_json_error('Invalid email format');
        wp_die();
    }
    
    // Validate phone number
    if (isset($form_data['field']['phone']) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $form_data['field']['phone'])) {
        wp_send_json_error('Invalid phone number format');
        wp_die();
    }
    
    return $form_data;
}, 10, 2);
```

---

## JavaScript Events

FormyChat dispatches several custom JavaScript events that allow developers to integrate with the plugin's frontend functionality. These events provide real-time information about widget states, form interactions, and user actions.

---

### Event Listening

All FormyChat events are dispatched on the `document` object and can be listened to using standard JavaScript event listeners:

```javascript
document.addEventListener('formychat_event_name', function(event) {
    console.log('Event detail:', event.detail);
});
```

---

## Core Events

### `formychat_app_loaded`

**Description:** Fired when the FormyChat application is fully loaded and initialized.

**When Dispatched:** When the main App.vue component is mounted and widgets are ready.

**Event Detail:**
```javascript
{
    formychat_app_loaded: true,
    widgets: Array // Array of widget objects with their configurations
}
```

**Example Usage:**
```javascript
document.addEventListener('formychat_app_loaded', function(event) {
    console.log('FormyChat loaded with', event.detail.widgets.length, 'widgets');
    
    // Initialize custom integrations
    initializeCustomIntegrations(event.detail.widgets);
});
```

**Note:** Legacy event `formychat_loaded` is still supported for backward compatibility.

**Use Cases:**
- Initialize third-party integrations
- Set up analytics tracking
- Perform custom widget modifications
- Load additional resources

---

## Widget State Events

### `formychat_widget_opened`

**Description:** Fired when a widget is opened or becomes visible to the user.

**When Dispatched:** 
- When widget opens by default
- When widget appears after scroll threshold
- When widget appears on mouse leave
- When widget appears after delay
- When popup is opened

**Event Detail:**
```javascript
{
    widget_id: number // The ID of the widget that was opened
}
```

**Example Usage:**
```javascript
document.addEventListener('formychat_widget_opened', function(event) {
    console.log('Widget opened:', event.detail.widget_id);
    
    // Track widget opens in analytics
    gtag('event', 'widget_opened', {
        widget_id: event.detail.widget_id
    });
    
    // Show custom welcome message
    showWelcomeMessage(event.detail.widget_id);
});
```

**Use Cases:**
- Analytics tracking
- User engagement monitoring
- Custom welcome messages
- A/B testing

### `formychat_widget_closed`

**Description:** Fired when a widget is closed or hidden from the user.

**When Dispatched:**
- When popup is closed by user
- When widget is hidden due to scroll position
- When widget is programmatically closed

**Event Detail:**
```javascript
{
    widget_id: number // The ID of the widget that was closed
}
```

**Example Usage:**
```javascript
document.addEventListener('formychat_widget_closed', function(event) {
    console.log('Widget closed:', event.detail.widget_id);
    
    // Track widget closes
    gtag('event', 'widget_closed', {
        widget_id: event.detail.widget_id
    });
    
    // Show exit intent popup
    showExitIntentPopup();
});
```

**Use Cases:**
- Analytics tracking
- Exit intent detection
- User behavior analysis
- Conversion optimization

---

## Form Events

### `formychat_form_loaded`

**Description:** Fired when a form is loaded and ready for user interaction.

**When Dispatched:** When the iframe containing the form is loaded and the form content is ready.

**Event Detail:**
```javascript
{
    widget_id: number, // The ID of the widget
    form: string,      // Form type (cf7, wpforms, gravity, etc.)
    form_id: number    // The ID of the specific form
}
```

**Example Usage:**
```javascript
document.addEventListener('formychat_form_loaded', function(event) {
    console.log('Form loaded:', event.detail.form, 'ID:', event.detail.form_id);
    
    // Initialize form-specific integrations
    if (event.detail.form === 'cf7') {
        initializeCF7Integrations(event.detail.form_id);
    }
    
    // Track form load in analytics
    gtag('event', 'form_loaded', {
        form_type: event.detail.form,
        form_id: event.detail.form_id
    });
});
```

**Use Cases:**
- Form-specific integrations
- Analytics tracking
- Form validation setup
- Custom form styling

### `formychat_form_submitted`

**Description:** Fired when a form is successfully submitted.

**When Dispatched:** When form submission is completed and data is processed.

**Event Detail:**
```javascript
{
    field: object,     // Form field data
    meta: object,      // Meta information (device, browser, etc.)
    widget_id: number, // Widget ID
    form_id: number,   // Form ID
    form: string,      // Form type
    isWidget: boolean  // Whether submitted from widget
}
```

**Example Usage:**
```javascript
document.addEventListener('formychat_form_submitted', function(event) {
    console.log('Form submitted:', event.detail);
    
    // Track conversion
    gtag('event', 'form_submitted', {
        form_type: event.detail.form,
        form_id: event.detail.form_id,
        widget_id: event.detail.widget_id
    });
    
    // Send to CRM
    sendToCRM(event.detail);
    
    // Show thank you message
    showThankYouMessage(event.detail.field);
});
```

**Use Cases:**
- Conversion tracking
- CRM integration
- Lead management
- Thank you page redirects
- Email notifications

---

## Agent Events

### `formychat_agent_selected`

**Description:** Fired when a user selects an agent from the agent list.

**When Dispatched:** When user clicks on an agent in multi-agent mode.

**Event Detail:**
```javascript
{
    widget_id: number, // The ID of the widget
    agent: object      // Agent object with details
}
```

**Agent Object Structure:**
```javascript
{
    id: number,
    name: string,
    subtitle: string,
    avatar: string,
    country_code: string,
    number: string
}
```

**Example Usage:**
```javascript
document.addEventListener('formychat_agent_selected', function(event) {
    console.log('Agent selected:', event.detail.agent.name);
    
    // Track agent selection
    gtag('event', 'agent_selected', {
        agent_id: event.detail.agent.id,
        agent_name: event.detail.agent.name
    });
    
    // Update chat interface
    updateChatInterface(event.detail.agent);
    
    // Send agent info to analytics
    sendAgentAnalytics(event.detail.agent);
});
```

**Use Cases:**
- Agent performance tracking
- Chat interface updates
- Analytics integration
- Customer service optimization

---

## Integration Events

### `formychat_gravity_forms_loaded`

**Description:** Fired when Gravity Forms integration is loaded and ready.

**When Dispatched:** When Gravity Forms integration script is initialized.

**Event Detail:**
```javascript
{
    formychat_gravity_forms_loaded: true
}
```

**Example Usage:**
```javascript
document.addEventListener('formychat_gravity_forms_loaded', function(event) {
    console.log('Gravity Forms integration loaded');
    
    // Initialize Gravity Forms specific features
    initializeGravityFormsFeatures();
    
    // Set up custom validation
    setupCustomValidation();
});
```

**Note:** Legacy event `formychat_gf_loaded` is still supported for backward compatibility.

**Use Cases:**
- Gravity Forms specific integrations
- Custom validation setup
- Form enhancement
- Third-party integrations

---

## Event Best Practices

### 1. **Event Order and Timing**
Events are dispatched in a specific order:
1. `formychat_app_loaded` - Application initialization
2. `formychat_widget_opened` - Widget becomes visible
3. `formychat_form_loaded` - Form is ready
4. `formychat_agent_selected` - Agent selection (if applicable)
5. `formychat_form_submitted` - Form submission
6. `formychat_widget_closed` - Widget closure

### 2. **Error Handling**
Always include error handling in your event listeners:
```javascript
document.addEventListener('formychat_form_submitted', function(event) {
    try {
        // Your code here
        processFormSubmission(event.detail);
    } catch (error) {
        console.error('Error processing form submission:', error);
        // Fallback handling
    }
});
```

### 3. **Performance Considerations**
- Avoid heavy operations in event listeners
- Use debouncing for frequent events
- Consider using `requestAnimationFrame` for UI updates

### 4. **Event Cleanup**
Remove event listeners when they're no longer needed:
```javascript
const handleFormSubmitted = function(event) {
    // Handle event
};

document.addEventListener('formychat_form_submitted', handleFormSubmitted);

// Cleanup when component unmounts
function cleanup() {
    document.removeEventListener('formychat_form_submitted', handleFormSubmitted);
}
```

---

## Common Integration Patterns

### 1. **Analytics Integration**
```javascript
// Google Analytics 4
document.addEventListener('formychat_app_loaded', function(event) {
    gtag('event', 'formychat_app_loaded', {
        widget_count: event.detail.widgets.length
    });
});

document.addEventListener('formychat_form_submitted', function(event) {
    gtag('event', 'form_submitted', {
        form_type: event.detail.form,
        form_id: event.detail.form_id,
        widget_id: event.detail.widget_id
    });
});
```

### 2. **CRM Integration**
```javascript
document.addEventListener('formychat_form_submitted', function(event) {
    const leadData = {
        email: event.detail.field.email,
        name: event.detail.field.name,
        phone: event.detail.field.phone,
        source: 'formychat',
        form_type: event.detail.form,
        widget_id: event.detail.widget_id
    };
    
    // Send to CRM
    fetch('/api/crm/leads', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(leadData)
    });
});
```

### 3. **Custom Form Validation**
```javascript
document.addEventListener('formychat_form_loaded', function(event) {
    if (event.detail.form === 'cf7') {
        // Add custom validation to CF7 forms
        const form = document.querySelector(`#wpcf7-f${event.detail.form_id}-p${event.detail.widget_id}-o1`);
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateCustomFields()) {
                    e.preventDefault();
                    showValidationError();
                }
            });
        }
    }
});
```

### 4. **A/B Testing**
```javascript
document.addEventListener('formychat_widget_opened', function(event) {
    // Track widget opens for A/B testing
    if (window.optimizely) {
        window.optimizely.push({
            type: 'event',
            eventName: 'formychat_widget_opened',
            tags: {
                widget_id: event.detail.widget_id
            }
        });
    }
});
```

### 5. **Custom UI Enhancements**
```javascript
document.addEventListener('formychat_form_loaded', function(event) {
    // Add custom styling to forms
    const iframe = document.querySelector(`iframe[src*="form_id=${event.detail.form_id}"]`);
    if (iframe) {
        iframe.addEventListener('load', function() {
            const style = iframe.contentDocument.createElement('style');
            style.textContent = `
                .formychat-custom-form {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
            `;
            iframe.contentDocument.head.appendChild(style);
        });
    }
});
```
---

## Deprecated Hooks and Events

The following hooks and events are deprecated and will be removed in future versions. Please update your code to use the new names.

### Deprecated WordPress Hooks

| Deprecated Hook | New Hook | Removal Version |
|----------------|----------|----------------|
| `formychat_admin_menu` | `formychat_admin_menu` | 3.0.0 |
| `formychat_widget_not_found` | `formychat_widget_not_found_error` | 3.0.0 |
| `formychat_form_not_found` | `formychat_form_not_found_error` | 3.0.0 |

### Deprecated JavaScript Events

| Deprecated Event | New Event | Removal Version |
|-----------------|-----------|----------------|
| `formychat_loaded` | `formychat_app_loaded` | 3.0.0 |
| `formychat_gf_loaded` | `formychat_gravity_forms_loaded` | 3.0.0 |

### Migration Guide

To update your code, simply replace the deprecated names with the new ones. For example:


```javascript
// Old (deprecated)
document.addEventListener('formychat_loaded', function(event) {
    // Your code
});

// New
document.addEventListener('formychat_app_loaded', function(event) {
    // Your code
});
```

**Note:** Deprecated hooks and events will continue to work until version 3.0.0, but it's recommended to update your code as soon as possible.

### Legacy Support Details

The plugin includes comprehensive legacy support for all deprecated hooks and events:

#### WordPress Hooks Legacy Support
- **`formychat_admin_menu`** → **`formychat_admin_menu`**
  - Both hooks are fired, new hook first, then old hook
  - Deprecation notice shown in debug mode

- **`formychat_widget_not_found`** → **`formychat_widget_not_found_error`**
  - Both hooks are fired, new hook first, then old hook
  - Deprecation notice shown in debug mode

- **`formychat_form_not_found`** → **`formychat_form_not_found_error`**
  - Both hooks are fired, new hook first, then old hook
  - Deprecation notice shown in debug mode

#### JavaScript Events Legacy Support
- **`formychat_loaded`** → **`formychat_app_loaded`**
  - Both events are dispatched, new event first, then old event
  - Console warning shown for deprecated event

- **`formychat_gf_loaded`** → **`formychat_gravity_forms_loaded`**
  - Both events are dispatched, new event first, then old event
  - Console warning shown for deprecated event

#### Content Filters Legacy Support
- **`formychat_widget_not_found_content`** → **`formychat_widget_not_found_error_content`**
  - New filter applied first, then old filter
  - Old filter receives the result from new filter

- **`formychat_form_not_found_content`** → **`formychat_form_not_found_error_content`**
  - New filter applied first, then old filter
  - Old filter receives the result from new filter

---

## Frontend Variables

FormyChat provides several global JavaScript variables on the frontend that contain useful information for integrations and customizations. These variables are automatically loaded and available on all pages where FormyChat is active.

---

## Global Variables

### `formychat_vars`

**Description:** Main configuration object containing all FormyChat settings, data, and user information.

**Availability:** Available on all frontend pages where FormyChat is active.

**Structure:**
```javascript
{
    // AJAX and REST API
    ajax_url: string,        // WordPress AJAX URL
    nonce: string,          // Security nonce for AJAX requests
    rest_url: string,       // WordPress REST API URL
    rest_nonce: string,     // Security nonce for REST API requests
    
    // Current Page Information
    current: {
        post_type: string,  // Current post type
        post_id: number,    // Current post ID
        is_home: boolean,   // Whether current page is home
        is_front_page: boolean // Whether current page is front page
    },
    
    // Plugin Data
    data: {
        countries: Array,   // Available countries list
        widgets: Array,     // Active widgets configuration
        default_config: object, // Default widget configuration
        form_fields: Array  // Available form fields
    },
    
    // Site Information
    site: {
        url: string,        // Site URL
        name: string,       // Site name
        description: string // Site description
    },
    
    // User Information (if logged in)
    user: {
        id: number,         // User ID
        email: string,      // User email
        first_name: string, // User first name
        last_name: string,  // User last name
        name: string,       // User display name
        phone: string       // User phone number
    },
    
    // Custom Tags
    custom_tags: object    // Available custom tags for message templates
}
```

**Example Usage:**
```javascript
// Check if user is logged in
if (formychat_vars.user && formychat_vars.user.id) {
    console.log('User:', formychat_vars.user.name);
}

// Get current page information
console.log('Current post ID:', formychat_vars.current.post_id);

// Get available countries
console.log('Countries:', formychat_vars.data.countries);

// Get active widgets
console.log('Active widgets:', formychat_vars.data.widgets);
```

---

### `formychat_ip`

**Description:** IP geolocation data for the current visitor, automatically fetched and cached.

**Availability:** Available after the `formychat_ip_loaded` event is fired.

**Structure:**
```javascript
{
    ip: string,              // Visitor's IP address
    country: string,         // Country name
    country_code: string,    // Country code (e.g., "US")
    country_phone: string,   // Country phone code (e.g., "+1")
    country_flag: string,    // Country flag emoji
    city: string,           // City name
    region: string,         // Region/State name
    timezone: string,       // Timezone
    timezone_gmt: string,   // GMT offset (e.g., "+00:00")
    currency: string,       // Currency code (e.g., "USD")
    latitude: number,       // Latitude coordinate
    longitude: number,      // Longitude coordinate
    isp: string,           // Internet Service Provider
    org: string            // Organization
}
```

**Example Usage:**
```javascript
// Wait for IP data to load
window.addEventListener('formychat_ip_loaded', (event) => {
    const ipData = event.detail;
    console.log('Visitor location:', ipData.city, ipData.country);
    console.log('IP address:', ipData.ip);
    console.log('Timezone:', ipData.timezone);
});

// Or access directly after loading
if (window.formychat_ip) {
    console.log('Country:', window.formychat_ip.country);
    console.log('City:', window.formychat_ip.city);
}
```

---

### `formychat_country_code`

**Description:** Automatically detected country phone code for the current visitor.

**Availability:** Available after the `formychat_ip_loaded` event is fired.

**Type:** `string` (e.g., "1", "44", "91")

**Example Usage:**
```javascript
// Wait for country code to be set
window.addEventListener('formychat_ip_loaded', (event) => {
    console.log('Country code:', window.formychat_country_code);
});

// Or access directly
if (window.formychat_country_code) {
    console.log('Visitor country code:', window.formychat_country_code);
}
```

---

## Related Events

### `formychat_ip_loaded`

**Description:** Fired when IP geolocation data is loaded and available.

**Event Detail:** Contains the complete IP geolocation data object.

**Example Usage:**
```javascript
window.addEventListener('formychat_ip_loaded', (event) => {
    const ipData = event.detail;
    
    // Set default country code
    window.formychat_country_code = ipData.country_phone?.replace('+', '') || '44';
    
    // Custom integration based on location
    if (ipData.country === 'United States') {
        // US-specific logic
    }
});
```

---

## Integration Examples

### 1. **Location-Based Customization**
```javascript
window.addEventListener('formychat_ip_loaded', (event) => {
    const ipData = event.detail;
    
    // Customize widget based on location
    if (ipData.country === 'United Kingdom') {
        // UK-specific widget configuration
        customizeWidgetForUK();
    } else if (ipData.country === 'United States') {
        // US-specific widget configuration
        customizeWidgetForUS();
    }
});
```

### 2. **User Information Integration**
```javascript
// Use user data for personalization
if (formychat_vars.user && formychat_vars.user.id) {
    const user = formychat_vars.user;
    
    // Pre-fill forms with user data
    prefillFormWithUserData(user);
    
    // Show personalized content
    showPersonalizedContent(user.name);
}
```

### 3. **Analytics Integration**
```javascript
// Track visitor information
window.addEventListener('formychat_ip_loaded', (event) => {
    const ipData = event.detail;
    
    // Send to analytics
    gtag('event', 'visitor_location', {
        country: ipData.country,
        city: ipData.city,
        timezone: ipData.timezone
    });
});
```

### 4. **Dynamic Widget Configuration**
```javascript
// Configure widgets based on current page
if (formychat_vars.current.post_type === 'product') {
    // Product page specific configuration
    configureProductPageWidget();
} else if (formychat_vars.current.is_home) {
    // Home page specific configuration
    configureHomePageWidget();
}
```

### 5. **Custom Form Integration**
```javascript
// Use form fields data
const formFields = formychat_vars.data.form_fields;
console.log('Available form fields:', formFields);

// Create custom form validation
function validateCustomForm(formData) {
    // Use formychat_vars.data.form_fields for validation
    return validateFormFields(formData, formFields);
}
```

---

## Data Caching

### IP Data Caching
- IP geolocation data is cached in localStorage for 24 hours
- Cache key: `formychat_ip_data`
- Automatic refresh when cache expires
- Fallback to default values if API fails

### Widget Data Caching
- Widget configurations are cached server-side
- Automatic refresh when widgets are updated
- Available immediately on page load

---

## Security Considerations

### Nonces
- All AJAX and REST API requests require valid nonces
- Nonces are automatically included in `formychat_vars`
- Always use provided nonces for security

### User Data
- User data is only available for logged-in users
- Sensitive information is properly sanitized
- Respect user privacy settings

---

## Performance Notes

- Variables are loaded asynchronously to avoid blocking page load
- IP data is fetched only once per day per visitor
- Widget data is cached and optimized for performance
- All data is minified in production builds

---

## 🛠️ Troubleshooting

### Common Issues & Solutions

#### WordPress Hooks Not Working
**Problem**: Hooks not firing or filters not working
```php
// ❌ Wrong - Missing priority or parameter count
add_action('formychat_form_submitted', 'my_function');

// ✅ Correct - Proper priority and parameter count
add_action('formychat_form_submitted', 'my_function', 10, 2);
```

**Solutions**:
1. Check hook priority and parameter count
2. Ensure FormyChat is active
3. Clear WordPress cache
4. Check for plugin conflicts

#### JavaScript Events Not Triggering
**Problem**: Events not firing in browser
```javascript
// ❌ Wrong - Event listener added after event dispatch
document.addEventListener('formychat_form_submitted', handler);

// ✅ Correct - Use DOMContentLoaded or add early
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('formychat_form_submitted', handler);
});
```

**Solutions**:
1. Add event listeners in `DOMContentLoaded`
2. Check browser console for errors
3. Verify FormyChat is loaded on the page
4. Use event delegation if needed

#### Variables Undefined
**Problem**: `formychat_vars` or other variables are undefined
```javascript
// ❌ Wrong - Direct access without checking
console.log(formychat_vars.user.name);

// ✅ Correct - Safe access with checks
if (typeof formychat_vars !== 'undefined' && formychat_vars.user) {
    console.log(formychat_vars.user.name);
}
```

**Solutions**:
1. Check if FormyChat is active on the page
2. Wait for `formychat_app_loaded` event
3. Verify script loading order
4. Check for JavaScript errors

### Debug Mode

Enable WordPress debug mode to see deprecation notices and errors:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Browser Console Debugging

```javascript
// Check if FormyChat is loaded
console.log('FormyChat vars:', typeof formychat_vars !== 'undefined' ? formychat_vars : 'Not loaded');

// Listen for all FormyChat events
const events = ['formychat_app_loaded', 'formychat_form_submitted', 'formychat_widget_opened'];
events.forEach(event => {
    document.addEventListener(event, (e) => {
        console.log(`FormyChat Event: ${event}`, e.detail);
    });
});
```

---

## 📚 API Reference

### Quick Reference Tables

#### WordPress Hooks Summary
| Hook | Type | Use Case | Priority |
|------|------|----------|----------|
| `formychat_form_submitted` | Action | Form submission | 10 |
| `formychat_lead_data` | Filter | Data modification | 10 |
| `formychat_widget_opened` | Action | Widget lifecycle | 10 |
| `formychat_email_subject` | Filter | Email customization | 10 |

#### JavaScript Events Summary
| Event | Trigger | Data | Use Case |
|-------|---------|------|----------|
| `formychat_app_loaded` | App initialization | Widgets config | Setup |
| `formychat_form_submitted` | Form submission | Form data | Analytics |
| `formychat_widget_opened` | Widget display | Widget ID | Tracking |
| `formychat_ip_loaded` | IP data loaded | Location data | Customization |

#### Frontend Variables Summary
| Variable | Type | Availability | Content |
|----------|------|--------------|---------|
| `formychat_vars` | Object | Always | Main config |
| `formychat_ip` | Object | After IP load | Location data |
| `formychat_country_code` | String | After IP load | Country code |

---

## 🔍 Search Keywords

**For AI and Search Engines:**
- FormyChat hooks, FormyChat events, FormyChat variables
- WordPress WhatsApp integration, lead generation
- Custom form integration, widget customization
- AJAX integration, REST API, frontend variables
- WordPress plugin development, hook system
- JavaScript custom events, DOM manipulation
- Geolocation integration, IP-based customization
- CRM integration, email notifications, analytics

---

## 📋 Integration Checklist

### Before Development
- [ ] Choose appropriate hooks/events for your use case
- [ ] Review data structures and parameters
- [ ] Plan error handling strategy
- [ ] Consider performance implications

### During Development
- [ ] Implement proper error handling
- [ ] Add data validation and sanitization
- [ ] Test with different user roles
- [ ] Verify browser compatibility

### Before Production
- [ ] Test in staging environment
- [ ] Check browser console for errors
- [ ] Verify WordPress debug log
- [ ] Test with different form types
- [ ] Validate data security measures
- [ ] Performance testing

---

## 🎯 Code Templates

### Basic Hook Template
```php
add_action('formychat_hook_name', function($param1, $param2) {
    try {
        // Your code here
        processData($param1, $param2);
    } catch (Exception $e) {
        error_log('FormyChat hook error: ' . $e->getMessage());
    }
}, 10, 2);
```

### Basic Event Template
```javascript
document.addEventListener('formychat_event_name', function(event) {
    try {
        // Your code here
        console.log('Event data:', event.detail);
        processEventData(event.detail);
    } catch (error) {
        console.error('FormyChat event error:', error);
    }
});
```

### Variable Access Template
```javascript
// Safe variable access
if (typeof formychat_vars !== 'undefined') {
    // Access formychat_vars safely
    if (formychat_vars.user && formychat_vars.user.id) {
        console.log('User:', formychat_vars.user.name);
    }
}

// Wait for IP data
window.addEventListener('formychat_ip_loaded', function(event) {
    const ipData = event.detail;
    console.log('Location:', ipData.country, ipData.city);
});
```

---

## 🤝 Support & Community

### Getting Help
- **Documentation**: This comprehensive guide
- **WordPress.org**: Plugin support forum
- **GitHub**: Issue tracking and contributions
- **Developer Community**: Share integrations and solutions

### Contributing
- Report bugs and suggest features
- Submit pull requests for improvements
- Share integration examples
- Help improve documentation

---

<div align="center">

**FormyChat Developer Documentation**  
*Complete reference for WordPress WhatsApp integration & lead generation*

[Back to Top](#formychat-plugin-developer-documentation)

</div>

---

This documentation provides a comprehensive reference for all WordPress hooks, JavaScript events, and frontend variables available in the FormyChat plugin. Use these resources to extend and customize the plugin's functionality according to your specific needs.