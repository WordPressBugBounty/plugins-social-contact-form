# WooCommerce WhatsApp Button Feature - Implementation Plan

## Feature Overview

Add an isolated WooCommerce integration that places customizable WhatsApp buttons on product pages, allowing customers to inquire about products directly via WhatsApp with pre-filled messages containing product details.

## Goals

1. **Seamless Integration**: Add WhatsApp buttons to WooCommerce shop and single product pages without disrupting existing functionality
2. **Flexible Configuration**: Separate settings for shop pages vs single product pages with independent styling and messaging
3. **Rich Data Context**: Support comprehensive product data placeholders (name, price, variations, images, stock, etc.)
4. **Multi-Agent Support**: Allow different WhatsApp numbers for different page contexts with rotation/random agent selection
5. **Visual Consistency**: Use existing FormyChat UI components for admin interface
6. **Live Preview**: Provide real-time preview for both shop and single product page contexts

## Architecture Approach

### Backend Structure

Create new integration module at `/includes/integrations/woocommerce/` following FormyChat's established patterns:

**Three Core Classes:**
1. **Helper Class** - Centralized placeholder parsing and product data extraction
2. **Admin Class** - Settings management, REST API endpoints, admin menu registration
3. **Frontend Class** - Button rendering, WooCommerce hook integration (classic + Blocks)

**Integration Pattern:**
- Extends `FormyChat\Base` for singleton pattern
- Loaded via `Boot::include_common_file()` when WooCommerce is active
- **Standalone settings page** under FormyChat admin menu (separate from widgets)
- Settings stored in `wp_options` as `formychat_woocommerce_settings`
- No database schema changes required

**WooCommerce Blocks Support:**
- Support both classic WooCommerce templates (via hooks)
- Support WooCommerce Blocks (via block filters and render callbacks)

### Frontend Assets

**CSS (`/public/css/woo-cart-button.css`):**
- Button styling with hover effects
- Responsive design for mobile/desktop
- Position variants (before/after/beside/replace)

**JavaScript (`/public/js/woo-cart-button.js`):**
- Dynamic message updates when product variations change
- Quantity tracking
- Analytics event tracking
- Progressive enhancement (works without JS)

### Admin Interface (Vue.js)

**Pinia Store (`/src/admin/stores/woocommerce.js`):**
- Manages shop and single product settings separately
- Handles REST API calls for save/load
- Loads available placeholders

**Vue Components:**
- `WooCommerce.vue` - Main settings page with tabs
- `WooCommerceContext.vue` - Reusable settings form for shop/single contexts
- `WooCommercePreview.vue` - Live preview with separate shop/single tabs
- `PlaceholdersList.vue` - Display available placeholders with descriptions

## Configuration Structure

```php
'woocommerce' => [
    'shop' => [
        'enabled' => false,
        'button_text' => 'Chat on WhatsApp',
        'button_position' => 'after', // before|after|beside|replace
        'message_template' => 'Hi, I\'m interested in *{product_name}*...',
        'whatsapp_number' => '',
        'country_code' => '',
        'agent_mode' => 'single', // single|multiple|random|rotation
        'agents' => [],
        'styling' => [
            'background_color' => '#25D366',
            'text_color' => '#ffffff',
            'icon_size' => 'medium',
            'border_radius' => '4px',
            'show_icon' => true,
        ],
    ],
    'single' => [
        // Same structure as shop, independent settings
    ],
]
```

## Placeholder System

Comprehensive product data extraction with placeholders grouped by category:

**Basic Data:**
- `{product_name}`, `{product_price}`, `{product_link}`, `{product_id}`

**Variations:**
- `{variation_attributes}`, `{selected_size}`, `{selected_color}`

**Media:**
- `{product_image}`, `{product_gallery}`

**Stock & Meta:**
- `{stock_status}`, `{stock_quantity}`, `{sku}`, `{categories}`, `{tags}`

**Global Data:**
- `{site_url}`, `{page_url}`, `{site_name}`

Dynamic parsing handled by `Woo_Cart_Button_Helper::parse_message_template()` which replaces placeholders with actual product data at render time.

## Button Position Control

Flexible positioning via dynamic hook priorities and CSS classes:

- **Before**: Priority 5 on WooCommerce hooks
- **After**: Priority 15 (default)
- **Beside**: Inline display with CSS
- **Replace**: Remove add-to-cart button via filters

Position managed from settings per context (shop/single).

## Data Flow

1. **Settings Save**: Vue component → Pinia store → REST API → WordPress options table (`formychat_woocommerce_settings`)
2. **Frontend Load**: WordPress options → Frontend class `load_config()` → Enqueue assets with localized data
3. **Button Render**:
   - **Classic**: WooCommerce hook fires → Get product data → Parse template → Render HTML
   - **Blocks**: Block filter/render callback → Get product data → Parse template → Render HTML
4. **User Click**: WhatsApp opens with pre-filled message
5. **Analytics**: Designed to be extensible - hooks available for future tracking implementation

## REST API Endpoints

- `GET /wp-json/formychat/v1/woocommerce/settings` - Load settings
- `POST /wp-json/formychat/v1/woocommerce/settings` - Save settings
- `GET /wp-json/formychat/v1/woocommerce/placeholders` - Get available placeholders

All endpoints require `manage_options` capability.

## WooCommerce Integration (Classic + Blocks)

### Classic Templates

**Shop/Archive Pages:**
- Hook: `woocommerce_after_shop_loop_item`
- Context: Product listings, category pages, search results
- Data: Basic product info, stock status

**Single Product Pages:**
- Hook: `woocommerce_after_add_to_cart_button`
- Context: Individual product pages
- Data: Full product details, variations, custom fields
- Dynamic Updates: JavaScript updates message when variation selected

### WooCommerce Blocks

**Product Grid/Collection Blocks:**
- Filter: `render_block_woocommerce/product-template`
- Inject button HTML after add-to-cart button in block output

**Single Product Block:**
- Filter: `render_block_woocommerce/add-to-cart-form`
- Inject button HTML after add-to-cart form in block output

**Implementation:**
```php
add_filter( 'render_block', [ $this, 'inject_button_in_blocks' ], 10, 2 );
```

## Implementation Sequence

### Phase 1: Backend Foundation
1. Create `/includes/integrations/woocommerce/class-woo-cart-button-helper.php`
   - Implement placeholder system
   - Product data extraction methods
   - Message template parser

2. Create `/includes/integrations/woocommerce/class-woo-cart-button-admin.php`
   - Register admin menu page under FormyChat
   - REST API endpoints for settings CRUD
   - Settings stored in `wp_options` table
   - Admin asset enqueue for Vue app

3. Create `/includes/integrations/woocommerce/class-woo-cart-button-frontend.php`
   - Classic WooCommerce hook integration
   - WooCommerce Blocks support via `render_block` filter
   - Button rendering logic (shared between classic/blocks)
   - Asset enqueue

4. Modify `/includes/class-boot.php`
   - Add WooCommerce integration loading (conditional on WooCommerce active)

### Phase 2: Frontend Assets
1. Create `/public/css/woo-cart-button.css`
   - Button styles, responsive design, position variants

2. Create `/public/js/woo-cart-button.js`
   - Variation change handlers
   - Analytics tracking
   - Dynamic message updates

### Phase 3: Admin UI
1. Create `/src/admin/stores/woocommerce.js`
   - Pinia store for state management
   - REST API integration

2. Create `/src/admin/views/WooCommerce.vue`
   - Main settings page with tabs

3. Create `/src/admin/views/woocommerce/WooCommerceContext.vue`
   - Reusable settings component for shop/single contexts
   - Form inputs using existing FormyChat components

4. Create `/src/admin/views/woocommerce/WooCommercePreview.vue`
   - Live preview with sample product data
   - Separate tabs for shop/single previews

5. Create `/src/admin/views/woocommerce/PlaceholdersList.vue`
   - Display available placeholders with descriptions

6. Add route to `/src/admin/routes/` (if needed)

### Phase 4: Polish & Testing
1. Responsive design testing
2. Theme compatibility testing
3. Variation product testing
4. Agent mode testing (random/rotation)
5. Build assets (compile CSS/JS)

## Critical Files

### Must Create:
- `/includes/integrations/woocommerce/class-woo-cart-button-helper.php`
- `/includes/integrations/woocommerce/class-woo-cart-button-admin.php`
- `/includes/integrations/woocommerce/class-woo-cart-button-frontend.php`
- `/public/css/woo-cart-button.css`
- `/public/js/woo-cart-button.js`
- `/src/admin/stores/woocommerce.js`
- `/src/admin/views/WooCommerce.vue`
- `/src/admin/views/woocommerce/WooCommerceContext.vue`
- `/src/admin/views/woocommerce/WooCommercePreview.vue`
- `/src/admin/views/woocommerce/PlaceholdersList.vue`

### Must Modify:
- `/includes/class-boot.php` - Add WooCommerce integration loading
- `/src/admin/index.js` - Register WooCommerce settings app mount point (if needed)
- `/src/admin/routes/` - Add route for WooCommerce settings page (if using existing router)

## Key Design Decisions

1. **Standalone Admin Page**: Separate settings page under FormyChat menu (not part of widget configuration) - keeps feature isolated as requested

2. **Separate Contexts**: Shop and single product pages have completely independent configurations to allow different messaging strategies and button positioning

3. **WordPress Options Storage**: Settings stored in `wp_options` table as `formychat_woocommerce_settings` - independent of widget system

4. **Dual Template Support**: Support both classic WooCommerce templates (via action hooks) and WooCommerce Blocks (via render_block filter) for maximum compatibility

5. **Reuse UI Components**: Utilize existing FormyChat components (Toggle, ColorPicker, PhoneCode, MessagePreset, etc.) for consistency and faster development

6. **Progressive Enhancement**: Core functionality works without JavaScript, enhanced features (variation updates) require JS

7. **Dynamic Hook Priority**: Button position determines WooCommerce hook priority, allowing flexible placement

8. **Centralized Placeholder Logic**: Single Helper class manages all placeholder parsing for maintainability

9. **Agent Mode Flexibility**: Support single number, multiple agents with random selection, or rotation - different per context

10. **Analytics-Ready Architecture**: Designed with extensibility hooks for future analytics/tracking implementation without core changes

## WordPress & WooCommerce Best Practices

- **PHP 8.4+** with modern syntax (match expressions, typed properties, constructor property promotion)
- **snake_case** for PHP, **camelCase** for JavaScript
- **WPCS** (WordPress Coding Standards) compliance
- **Escaping**: All output escaped (`esc_html`, `esc_url`, `esc_attr`)
- **Nonces**: REST API uses WordPress nonces for CSRF protection
- **Permissions**: All admin endpoints check `manage_options` capability
- **Hooks**: Filterable for extensibility (`formychat_woo_*` prefix)
- **Conditional Loading**: Only loads when WooCommerce is active

## Success Metrics

- ✅ Standalone admin page appears under FormyChat menu
- ✅ Button appears correctly on shop and single product pages when enabled
- ✅ Works with both classic WooCommerce templates and WooCommerce Blocks
- ✅ Settings save and load without errors from wp_options
- ✅ All placeholders populate with correct product data
- ✅ Variation selection updates message dynamically
- ✅ Multiple agent modes work correctly
- ✅ Preview accurately reflects settings for both contexts
- ✅ Mobile responsive on all devices
- ✅ Compatible with popular WooCommerce themes and block themes
- ✅ No conflicts with existing FormyChat functionality
- ✅ Assets build successfully without errors
- ✅ Extensible for future analytics implementation
