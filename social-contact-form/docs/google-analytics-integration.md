# FormyChat Google Analytics Integration Guide

## Overview

FormyChat provides comprehensive Google Analytics integration to track user interactions with your chat widgets and forms. This integration is available as a **Premium Feature** and requires an active FormyChat Ultimate license.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Setup Instructions](#setup-instructions)
3. [Tracked Events](#tracked-events)
4. [Event Parameters](#event-parameters)
5. [GTM Integration](#gtm-integration)
6. [Custom Tracking](#custom-tracking)
7. [Analytics Dashboard](#analytics-dashboard)
8. [Troubleshooting](#troubleshooting)
9. [Privacy Considerations](#privacy-considerations)

## Prerequisites

- **FormyChat Ultimate License**: Active premium license required
- **Google Analytics**: GA4 or Universal Analytics setup
- **Google Tag Manager** (Optional): For advanced tracking
- **WordPress Admin Access**: To configure widget settings

## Setup Instructions

### 1. Enable Google Analytics in Widget Settings

1. Go to **FormyChat > Widgets** in your WordPress admin
2. Edit an existing widget or create a new one
3. Navigate to **Customize > Form Behavior**
4. Enable the **Google Analytics** toggle
5. Save the widget

### 2. Verify Integration

Once enabled, FormyChat will automatically:
- Initialize the `dataLayer` if it doesn't exist
- Push events to Google Analytics
- Track all widget interactions
- Send enhanced event data

## Tracked Events

FormyChat tracks the following events automatically:

### Core Widget Events

| Event Name | Description | When Triggered |
|------------|-------------|----------------|
| `widget_opened` | Widget form opened | User clicks widget icon |
| `widget_closed` | Widget form closed | User closes form or clicks outside |
| `widget_visible` | Widget becomes visible | Widget enters viewport |
| `widget_clicked` | Widget icon clicked | User clicks widget icon |

### Form Interaction Events

| Event Name | Description | When Triggered |
|------------|-------------|----------------|
| `form_loaded` | Form content loaded | Form fields are ready |
| `form_submitted` | Form successfully submitted | User submits form data |
| `field_focused` | Form field focused | User clicks/tabs to field |
| `field_changed` | Form field value changed | User modifies field content |
| `validation_error` | Form validation failed | Invalid data entered |

### Agent Selection Events

| Event Name | Description | When Triggered |
|------------|-------------|----------------|
| `agent_selected` | Agent chosen by user | User selects specific agent |
| `greetings_clicked` | Greetings message clicked | User interacts with greeting |

### WhatsApp Integration Events

| Event Name | Description | When Triggered |
|------------|-------------|----------------|
| `whatsapp_opened` | WhatsApp link opened | User redirected to WhatsApp |
| `page_view` | Page with widget loaded | Page containing widget loads |

## Event Parameters

All events include comprehensive parameters for detailed analytics:

### Standard Parameters

```javascript
{
    event_category: 'FormyChat',
    event_label: 'Widget Interaction',
    widget_id: '123',
    widget_name: 'Contact Widget',
    page_url: 'https://example.com/contact',
    page_title: 'Contact Us - Example.com',
    user_agent: 'Mozilla/5.0...',
    timestamp: '2024-01-15T10:30:00.000Z',
    screen_resolution: '1920x1080',
    viewport_size: '1200x800'
}
```

### Form-Specific Parameters

```javascript
{
    form_type: 'formychat', // or 'cf7', 'wpforms', etc.
    form_id: '456',
    field_count: 5,
    has_phone: 'yes',
    has_email: 'yes',
    has_name: 'yes',
    submission_time: '2024-01-15T10:30:00.000Z'
}
```

### Agent-Specific Parameters

```javascript
{
    agent_name: 'John Doe',
    agent_id: 'agent_123',
    agent_subtitle: 'Customer Support'
}
```

### Field-Specific Parameters

```javascript
{
    field_name: 'email',
    field_type: 'email',
    has_value: 'yes'
}
```

## GTM Integration

FormyChat automatically integrates with Google Tag Manager:

### DataLayer Events

All events are pushed to the `dataLayer` for GTM processing:

```javascript
dataLayer.push({
    event: 'formychat_widget_opened',
    event_category: 'FormyChat',
    widget_id: '123',
    // ... additional parameters
});
```

### GTM Trigger Setup

1. **Create Custom Event Triggers**:
   - Event name: `formychat_widget_opened`
   - Event name: `formychat_form_submitted`
   - Event name: `formychat_agent_selected`

2. **Create Variables**:
   - `FormyChat Widget ID`: `{{DLV - widget_id}}`
   - `FormyChat Form Type`: `{{DLV - form_type}}`
   - `FormyChat Agent Name`: `{{DLV - agent_name}}`

3. **Create Tags**:
   - Google Analytics 4 Event Tag
   - Enhanced Ecommerce Tag
   - Custom HTML Tag for external tools

### Example GTM Configuration

```javascript
// Custom HTML Tag for FormyChat Events
<script>
(function() {
    // Listen for FormyChat events
    window.addEventListener('formychat_form_submitted', function(e) {
        // Send to external analytics
        gtag('event', 'form_submission', {
            widget_id: e.detail.widget_id,
            form_type: e.detail.form,
            value: 1
        });
    });
})();
</script>
```

## Custom Tracking

### JavaScript API

FormyChat provides a JavaScript API for custom tracking:

```javascript
// Check if analytics is available
if (window.formychatAnalytics) {
    // Track custom event
    window.formychatAnalytics.trackEvent('custom_action', {
        widget_id: '123',
        custom_parameter: 'value'
    });
}
```

### Event Listeners

Listen for FormyChat events in your custom code:

```javascript
// Listen for form submissions
document.addEventListener('formychat_form_submitted', function(event) {
    const detail = event.detail;
    console.log('Form submitted:', detail);
    
    // Send to your analytics
    yourAnalytics.track('form_submission', detail);
});

// Listen for agent selections
document.addEventListener('formychat_agent_selected', function(event) {
    const detail = event.detail;
    console.log('Agent selected:', detail);
});
```

### AJAX Tracking

Send events to server-side analytics:

```javascript
// Using FormyChat's AJAX tracking
FormyChatAnalytics.sendToServer('custom_event', {
    widget_id: '123',
    user_action: 'button_click',
    timestamp: new Date().toISOString()
});
```

## Analytics Dashboard

### Google Analytics Reports

Create custom reports in Google Analytics:

1. **Widget Performance Report**:
   - Metric: Event count
   - Dimension: Widget ID
   - Filter: Event category = "FormyChat"

2. **Form Conversion Report**:
   - Metric: Event count
   - Dimension: Form type
   - Filter: Event name = "form_submitted"

3. **Agent Performance Report**:
   - Metric: Event count
   - Dimension: Agent name
   - Filter: Event name = "agent_selected"

### Custom Dimensions

Set up custom dimensions in GA4:

1. **Widget ID**: `formychat_widget_id`
2. **Form Type**: `formychat_form_type`
3. **Agent Name**: `formychat_agent_name`
4. **Field Type**: `formychat_field_type`

### Conversion Tracking

Track conversions and goals:

1. **Form Submission Goal**:
   - Event: `formychat_form_submitted`
   - Value: 1

2. **Agent Selection Goal**:
   - Event: `formychat_agent_selected`
   - Value: 1

3. **WhatsApp Redirect Goal**:
   - Event: `formychat_whatsapp_opened`
   - Value: 1

## Troubleshooting

### Common Issues

1. **Events Not Appearing**:
   - Check if premium license is active
   - Verify Google Analytics is enabled in widget settings
   - Check browser console for errors

2. **Missing Parameters**:
   - Ensure widget has proper configuration
   - Check if form fields are properly named
   - Verify agent data is complete

3. **GTM Not Receiving Events**:
   - Check dataLayer initialization
   - Verify GTM container is active
   - Test with GTM preview mode

### Debug Mode

Enable debug mode for development:

```javascript
// Debug mode is automatically enabled when WP_DEBUG is true
// Check browser console for detailed event logs
```

### Testing Checklist

- [ ] Premium license active
- [ ] Google Analytics enabled in widget
- [ ] Widget is active and visible
- [ ] Browser console shows no errors
- [ ] Events appear in Google Analytics
- [ ] GTM receives dataLayer events

## Privacy Considerations

### Data Collection

FormyChat collects the following data:
- Widget interaction events
- Form submission metadata (no personal data)
- Page and device information
- User agent and screen resolution

### GDPR Compliance

- No personal data is sent to analytics
- Form field values are not tracked
- User consent can be managed through GTM
- Data retention follows Google Analytics policies

### Opt-out Options

Users can opt-out through:
- Browser privacy settings
- Google Analytics opt-out
- Custom JavaScript implementation

## Advanced Configuration

### Custom Event Mapping

Modify event names in your theme:

```php
// In functions.php
add_filter('formychat_analytics_event_mapping', function($mapping) {
    $mapping['formychat_form_submitted'] = 'lead_generated';
    return $mapping;
});
```

### Enhanced Parameters

Add custom parameters to events:

```php
add_filter('formychat_analytics_event_params', function($params, $event_name) {
    if ($event_name === 'form_submitted') {
        $params['conversion_value'] = 10;
        $params['currency'] = 'USD';
    }
    return $params;
}, 10, 2);
```

### Conditional Tracking

Track events conditionally:

```php
add_filter('formychat_analytics_should_track', function($should_track, $event_name) {
    // Don't track on admin pages
    if (is_admin()) {
        return false;
    }
    return $should_track;
}, 10, 2);
```

## Support

For technical support:
- Check the troubleshooting section above
- Review browser console for errors
- Contact FormyChat support with event logs
- Provide widget configuration details

---

**Note**: This integration requires FormyChat Ultimate license. Free users will not have access to Google Analytics tracking features.
