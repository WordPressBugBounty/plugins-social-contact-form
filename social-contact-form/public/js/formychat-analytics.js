/**
 * FormyChat Analytics JavaScript
 * 
 * Enhanced analytics tracking for FormyChat widgets
 * 
 * @version 1.0.0
 * @package FormyChat
 */

(function($) {
    'use strict';

    // FormyChat Analytics Object
    window.FormyChatAnalytics = {
        
        /**
         * Initialize analytics
         */
        init: function() {
            this.setupEventListeners();
            this.trackPageView();
            this.setupFormTracking();
            this.setupWidgetTracking();
        },

        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            // Track widget interactions
            $(document).on('click', '.formychat-widget', function(e) {
                const widgetId = $(this).data('widget-id') || 'unknown';
                FormyChatAnalytics.trackEvent('widget_clicked', {
                    widget_id: widgetId,
                    click_type: 'widget_icon'
                });
            });

            // Track form field interactions
            $(document).on('focus', '.formychat-widget-form-fields-input', function() {
                const field = $(this);
                const fieldName = field.attr('name') || 'unknown';
                const fieldType = field.attr('type') || 'text';
                const widgetId = field.closest('.formychat-widget').data('widget-id') || 'unknown';

                FormyChatAnalytics.trackEvent('field_focused', {
                    widget_id: widgetId,
                    field_name: fieldName,
                    field_type: fieldType
                });
            });

            // Track form submissions
            $(document).on('submit', '.formychat-widget form', function(e) {
                const form = $(this);
                const widgetId = form.closest('.formychat-widget').data('widget-id') || 'unknown';
                const formData = new FormData(this);
                
                // Get form field count
                const fieldCount = form.find('input, textarea, select').length;
                
                FormyChatAnalytics.trackEvent('form_submitted', {
                    widget_id: widgetId,
                    field_count: fieldCount,
                    form_type: 'formychat'
                });
            });

            // Track agent selections
            $(document).on('click', '.formychat-widget-agents-agent', function() {
                const agent = $(this);
                const agentName = agent.find('.formychat-widget-agents-agent-name').text().trim();
                const widgetId = agent.closest('.formychat-widget').data('widget-id') || 'unknown';

                FormyChatAnalytics.trackEvent('agent_selected', {
                    widget_id: widgetId,
                    agent_name: agentName
                });
            });

            // Track WhatsApp opens
            $(document).on('click', 'a[href*="wa.me"], a[href*="web.whatsapp.com"]', function() {
                const link = $(this);
                const widgetId = link.closest('.formychat-widget').data('widget-id') || 'unknown';
                const whatsappUrl = link.attr('href');

                FormyChatAnalytics.trackEvent('whatsapp_opened', {
                    widget_id: widgetId,
                    whatsapp_url: whatsappUrl
                });
            });
        },

        /**
         * Track page view
         */
        trackPageView: function() {
            if (window.formychatAnalytics && window.formychatAnalytics.trackEvent) {
                window.formychatAnalytics.trackEvent('page_view', {
                    page_url: window.location.href,
                    page_title: document.title,
                    referrer: document.referrer
                });
            }
        },

        /**
         * Setup form tracking
         */
        setupFormTracking: function() {
            // Track form field changes
            $(document).on('change', '.formychat-widget-form-fields-input', function() {
                const field = $(this);
                const fieldName = field.attr('name') || 'unknown';
                const fieldType = field.attr('type') || 'text';
                const fieldValue = field.val();
                const widgetId = field.closest('.formychat-widget').data('widget-id') || 'unknown';

                // Only track if field has a value (privacy consideration)
                if (fieldValue && fieldValue.length > 0) {
                    FormyChatAnalytics.trackEvent('field_changed', {
                        widget_id: widgetId,
                        field_name: fieldName,
                        field_type: fieldType,
                        has_value: 'yes'
                    });
                }
            });

            // Track form validation errors
            $(document).on('invalid', '.formychat-widget-form-fields-input', function() {
                const field = $(this);
                const fieldName = field.attr('name') || 'unknown';
                const fieldType = field.attr('type') || 'text';
                const widgetId = field.closest('.formychat-widget').data('widget-id') || 'unknown';

                FormyChatAnalytics.trackEvent('validation_error', {
                    widget_id: widgetId,
                    field_name: fieldName,
                    field_type: fieldType
                });
            });
        },

        /**
         * Setup widget tracking
         */
        setupWidgetTracking: function() {
            // Track widget visibility
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const widgetId = entry.target.data('widget-id') || 'unknown';
                        FormyChatAnalytics.trackEvent('widget_visible', {
                            widget_id: widgetId
                        });
                    }
                });
            });

            // Observe all FormyChat widgets
            $('.formychat-widget').each(function() {
                observer.observe(this);
            });
        },

        /**
         * Track custom event
         */
        trackEvent: function(eventName, parameters) {
            // Add default parameters
            const defaultParams = {
                event_category: 'FormyChat',
                event_label: 'Widget Interaction',
                timestamp: new Date().toISOString(),
                user_agent: navigator.userAgent,
                screen_resolution: screen.width + 'x' + screen.height,
                viewport_size: window.innerWidth + 'x' + window.innerHeight
            };

            // Merge parameters
            const finalParams = $.extend({}, defaultParams, parameters);

            // Send to analytics
            if (window.formychatAnalytics && window.formychatAnalytics.trackEvent) {
                window.formychatAnalytics.trackEvent(eventName, finalParams);
            }

            // Send via AJAX for server-side tracking
            this.sendToServer(eventName, finalParams);

            // Debug logging
            if (window.formychatAnalyticsData && window.formychatAnalyticsData.debug) {
                console.log('FormyChat Analytics Event:', eventName, finalParams);
            }
        },

        /**
         * Send event to server
         */
        sendToServer: function(eventName, parameters) {
            if (!window.formychatAnalyticsData || !window.formychatAnalyticsData.ajax_url) {
                return;
            }

            $.ajax({
                url: window.formychatAnalyticsData.ajax_url,
                type: 'POST',
                data: {
                    action: 'formychat_track_analytics_event',
                    event_name: eventName,
                    event_params: parameters,
                    nonce: window.formychatAnalyticsData.nonce
                },
                success: function(response) {
                    if (window.formychatAnalyticsData.debug) {
                        console.log('Analytics event sent to server:', response);
                    }
                },
                error: function(xhr, status, error) {
                    if (window.formychatAnalyticsData.debug) {
                        console.error('Failed to send analytics event:', error);
                    }
                }
            });
        },

        /**
         * Get analytics data
         */
        getAnalyticsData: function(widgetId, dateRange) {
            if (!window.formychatAnalyticsData || !window.formychatAnalyticsData.ajax_url) {
                return $.Deferred().reject('Analytics not configured');
            }

            return $.ajax({
                url: window.formychatAnalyticsData.ajax_url,
                type: 'POST',
                data: {
                    action: 'formychat_get_analytics_data',
                    widget_id: widgetId,
                    date_range: dateRange,
                    nonce: window.formychatAnalyticsData.nonce
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        FormyChatAnalytics.init();
    });

})(jQuery);
