# FormyChat Analytics Developer Reference

## Quick Reference

### Event Names
```javascript
// Core Events
'widget_opened' | 'widget_closed' | 'widget_visible' | 'widget_clicked'

// Form Events  
'form_loaded' | 'form_submitted' | 'field_focused' | 'field_changed' | 'validation_error'

// Agent Events
'agent_selected' | 'greetings_clicked'

// WhatsApp Events
'whatsapp_opened' | 'page_view'
```

### Global Objects
```javascript
// Main analytics object
window.formychatAnalytics

// Enhanced tracking object
window.FormyChatAnalytics

// Data for AJAX calls
window.formychatAnalyticsData
```

## Technical Implementation

### Class Structure

```php
namespace FormyChat\Classes;

class Google_Analytics extends \FormyChat\Base {
    // Premium check
    private function is_premium_active() {
        return $this->is_ultimate_active();
    }
    
    // Event tracking
    public function add_analytics_script() { /* ... */ }
    public function add_analytics_events() { /* ... */ }
    public function enqueue_analytics_assets() { /* ... */ }
}
```

### Database Schema

```sql
-- Widgets table with analytics config
SELECT id, name, config FROM wp_scf_widgets 
WHERE is_active = 1 AND deleted_at IS NULL

-- Config JSON structure
{
    "form": {
        "google_analytics": true,
        "open_by_default": false,
        "close_on_submit": true
    }
}
```

## JavaScript API Reference

### Core Tracking Function

```javascript
// Basic event tracking
window.formychatAnalytics.trackEvent(eventName, parameters)

// Example usage
window.formychatAnalytics.trackEvent('custom_action', {
    widget_id: '123',
    custom_param: 'value'
});
```

### Enhanced Tracking Object

```javascript
// Initialize analytics
FormyChatAnalytics.init()

// Track custom event
FormyChatAnalytics.trackEvent('event_name', {
    widget_id: '123',
    timestamp: new Date().toISOString()
})

// Send to server
FormyChatAnalytics.sendToServer('event_name', parameters)

// Get analytics data
FormyChatAnalytics.getAnalyticsData(widgetId, dateRange)
```

### Event Listeners

```javascript
// Listen for FormyChat events
document.addEventListener('formychat_widget_opened', function(event) {
    const detail = event.detail;
    // detail.widget_id, detail.widget_name, etc.
});

document.addEventListener('formychat_form_submitted', function(event) {
    const detail = event.detail;
    // detail.field, detail.form, detail.widget_id, etc.
});

document.addEventListener('formychat_agent_selected', function(event) {
    const detail = event.detail;
    // detail.agent, detail.widget_id, etc.
});
```

## DataLayer Structure

### Standard Event Format

```javascript
dataLayer.push({
    event: 'formychat_widget_opened',
    event_category: 'FormyChat',
    event_label: 'Widget Interaction',
    widget_id: '123',
    widget_name: 'Contact Widget',
    page_url: window.location.href,
    page_title: document.title,
    user_agent: navigator.userAgent,
    timestamp: new Date().toISOString(),
    screen_resolution: screen.width + 'x' + screen.height,
    viewport_size: window.innerWidth + 'x' + window.innerHeight
});
```

### Form Submission Event

```javascript
dataLayer.push({
    event: 'formychat_form_submitted',
    event_category: 'FormyChat',
    event_label: 'Form Submission',
    widget_id: '123',
    form_type: 'formychat',
    form_id: '456',
    field_count: 5,
    has_phone: 'yes',
    has_email: 'yes',
    has_name: 'yes',
    submission_time: new Date().toISOString()
});
```

## WordPress Hooks & Filters

### Available Filters

```php
// Modify event mapping
add_filter('formychat_analytics_event_mapping', function($mapping) {
    $mapping['formychat_form_submitted'] = 'lead_generated';
    return $mapping;
});

// Modify event parameters
add_filter('formychat_analytics_event_params', function($params, $event_name) {
    if ($event_name === 'form_submitted') {
        $params['conversion_value'] = 10;
    }
    return $params;
}, 10, 2);

// Conditional tracking
add_filter('formychat_analytics_should_track', function($should_track, $event_name) {
    if (is_admin()) return false;
    return $should_track;
}, 10, 2);

// Modify widget data
add_filter('formychat_analytics_widget_data', function($widget_data) {
    $widget_data['custom_field'] = 'value';
    return $widget_data;
});
```

### Available Actions

```php
// Before analytics script loads
do_action('formychat_before_analytics_script');

// After analytics script loads
do_action('formychat_after_analytics_script');

// Before event tracking
do_action('formychat_before_event_track', $event_name, $parameters);

// After event tracking
do_action('formychat_after_event_track', $event_name, $parameters);
```

## AJAX Endpoints

### Track Custom Event

```php
// Endpoint: admin-ajax.php
// Action: formychat_track_analytics_event

// Request
{
    action: 'formychat_track_analytics_event',
    event_name: 'custom_event',
    event_params: { widget_id: '123' },
    nonce: 'formychat_analytics_nonce'
}

// Response
{
    success: true,
    message: 'Event tracked successfully'
}
```

### Get Analytics Data

```php
// Endpoint: admin-ajax.php
// Action: formychat_get_analytics_data

// Request
{
    action: 'formychat_get_analytics_data',
    widget_id: '123',
    date_range: '7days',
    nonce: 'formychat_analytics_nonce'
}

// Response
{
    success: true,
    data: {
        total_submissions: 0,
        total_clicks: 0,
        total_agents_selected: 0,
        conversion_rate: 0,
        popular_agents: [],
        popular_pages: []
    }
}
```

## GTM Configuration

### Trigger Setup

```javascript
// Custom Event Trigger
Event name: formychat_widget_opened
Event name: formychat_form_submitted
Event name: formychat_agent_selected
```

### Variable Setup

```javascript
// Data Layer Variables
{{DLV - widget_id}}
{{DLV - form_type}}
{{DLV - agent_name}}
{{DLV - field_name}}
{{DLV - page_url}}
{{DLV - timestamp}}
```

### Tag Configuration

```javascript
// GA4 Event Tag
Event name: {{Event}}
Parameters:
- widget_id: {{DLV - widget_id}}
- form_type: {{DLV - form_type}}
- agent_name: {{DLV - agent_name}}
```

## Error Handling

### JavaScript Errors

```javascript
// Check if analytics is available
if (typeof window.formychatAnalytics === 'undefined') {
    console.warn('FormyChat Analytics not available');
    return;
}

// Check if tracking is enabled
if (!window.formychatAnalytics.enabled) {
    console.log('FormyChat Analytics disabled');
    return;
}

// Handle tracking errors
try {
    window.formychatAnalytics.trackEvent('event_name', params);
} catch (error) {
    console.error('Analytics tracking failed:', error);
}
```

### PHP Errors

```php
// Check premium status
if (!$this->is_premium_active()) {
    return; // Analytics not available
}

// Check widget configuration
$config = json_decode($widget->config, true);
if (!isset($config['form']['google_analytics']) || !$config['form']['google_analytics']) {
    return; // Analytics not enabled for this widget
}
```

## Performance Considerations

### Script Loading

```php
// Analytics script is loaded in footer
wp_enqueue_script('formychat-analytics', $url, ['jquery'], $version, true);

// Only loads when analytics is enabled
if ($this->is_premium_active() && !empty($active_widgets)) {
    // Load analytics
}
```

### Event Batching

```javascript
// Events are sent immediately by default
// For high-traffic sites, consider batching:

let eventQueue = [];
let batchTimeout;

function queueEvent(eventName, params) {
    eventQueue.push({ eventName, params });
    
    if (batchTimeout) clearTimeout(batchTimeout);
    batchTimeout = setTimeout(sendBatch, 1000);
}

function sendBatch() {
    if (eventQueue.length > 0) {
        // Send batch to server
        FormyChatAnalytics.sendToServer('batch_events', {
            events: eventQueue
        });
        eventQueue = [];
    }
}
```

## Testing

### Unit Tests

```php
// Test premium check
public function test_premium_check() {
    $analytics = new Google_Analytics();
    $this->assertTrue($analytics->is_premium_active());
}

// Test widget filtering
public function test_widget_filtering() {
    $widgets = $this->get_active_widgets_with_analytics();
    $this->assertNotEmpty($widgets);
}
```

### Integration Tests

```javascript
// Test event tracking
test('tracks widget opened event', () => {
    const mockGtag = jest.fn();
    window.gtag = mockGtag;
    
    // Trigger widget open
    document.dispatchEvent(new CustomEvent('formychat_widget_opened', {
        detail: { widget_id: '123' }
    }));
    
    expect(mockGtag).toHaveBeenCalledWith('event', 'widget_opened', expect.any(Object));
});

// Test dataLayer push
test('pushes to dataLayer', () => {
    window.dataLayer = [];
    
    document.dispatchEvent(new CustomEvent('formychat_form_submitted', {
        detail: { widget_id: '123' }
    }));
    
    expect(window.dataLayer).toContainEqual(
        expect.objectContaining({
            event: 'formychat_form_submitted'
        })
    );
});
```

## Security

### Nonce Verification

```php
// Verify AJAX requests
if (!wp_verify_nonce($_POST['nonce'], 'formychat_analytics_nonce')) {
    wp_die('Security check failed');
}
```

### Data Sanitization

```php
// Sanitize event parameters
$event_name = sanitize_text_field($_POST['event_name']);
$event_params = array_map('sanitize_text_field', $_POST['event_params']);
```

### XSS Prevention

```javascript
// Sanitize user input before tracking
function sanitizeForAnalytics(input) {
    return input.replace(/[<>]/g, '');
}

// Use sanitized values
window.formychatAnalytics.trackEvent('user_input', {
    sanitized_value: sanitizeForAnalytics(userInput)
});
```

## Migration Guide

### From Custom Tracking

```javascript
// Old custom tracking
gtag('event', 'form_submit', { form_id: '123' });

// New FormyChat tracking
document.addEventListener('formychat_form_submitted', function(event) {
    gtag('event', 'form_submit', {
        form_id: event.detail.form_id,
        widget_id: event.detail.widget_id
    });
});
```

### From GTM Variables

```javascript
// Old GTM variable
{{Form ID}}

// New FormyChat variable
{{DLV - form_id}}
```

---

**Version**: 1.0.0  
**Last Updated**: January 2024  
**Compatibility**: FormyChat Ultimate 2.10.8+
