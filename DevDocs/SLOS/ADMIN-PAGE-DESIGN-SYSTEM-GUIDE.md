# Admin Page Design System - Conversion Guide

> **Universal prompt for transforming any admin page to match the Module Dashboard design system**

## 🎯 Objective

Convert an existing admin page to use the plugin's global cyberpunk/dark futuristic design system, matching the visual language of [templates/admin/module-dashboard.php](../templates/admin/module-dashboard.php).

---

## 📋 Prerequisites Checklist

Before starting the conversion, ensure:

1. **Page Hook Recognition**
   - Verify the page slug is detected by [includes/Core/Assets.php](../includes/Core/Assets.php) in `is_plugin_page()`
   - Pages with `shahi-legalops-suite` or `slos-` prefix are auto-detected
   - For custom slugs, add detection logic around line 732

2. **Asset Loading Method**
   - Add a new `is_[page_name]_page()` method in `Assets.php` (see line 974 for examples)
   - Add CSS/JS enqueuing in `enqueue_admin_styles()` and `enqueue_admin_scripts()` (lines 156-166 for reference)
   - **NEVER** manually enqueue assets in the page class - let `Assets.php` handle everything

3. **Page Class Structure**
   - Create clean class with `render()` method only
   - Remove any `enqueue_assets()` methods (causes duplicates)
   - Remove `init()` calls for asset loading (handled globally)
   - Reference: [includes/Modules/AccessibilityScanner/Admin/AccessibilityDashboard.php](../includes/Modules/AccessibilityScanner/Admin/AccessibilityDashboard.php)

---

## 🎨 Design System Implementation

### **Step 1: Update Assets.php**

Add your page detection and asset loading to [includes/Core/Assets.php](../includes/Core/Assets.php):

```
Location: enqueue_admin_styles() method (~line 156)

Add condition:
} elseif ($this->is_[your_page]_page($hook)) {
    $this->enqueue_style(
        'shahi-admin-module-dashboard',
        'css/admin-module-dashboard',
        array('shahi-components'),
        $this->version
    );
}

Location: enqueue_admin_scripts() method (~line 430)

Add condition:
} elseif ($this->is_[your_page]_page($hook)) {
    $this->enqueue_script(
        'shahi-admin-module-dashboard',
        'js/admin-module-dashboard',
        array('jquery', 'shahi-components'),
        $this->version,
        true
    );
}

Location: Add detection method (~line 974)

private function is_[your_page]_page($hook) {
    return strpos($hook, '[your-page-slug]') !== false;
}
```

### **Step 2: Template Structure**

Create/update your template in `templates/admin/` following this structure:

**Required Wrapper:**
```html
<div class="wrap shahi-module-dashboard">
```

**Global CSS Classes Available:**
- [assets/css/admin-global.css](../assets/css/admin-global.css) - Base variables, resets
- [assets/css/components.css](../assets/css/components.css) - Reusable UI components
- [assets/css/admin-module-dashboard.css](../assets/css/admin-module-dashboard.css) - Premium card styles

**Global JS Features Available:**
- [assets/js/admin-global.js](../assets/js/admin-global.js) - Core utilities
- [assets/js/components.js](../assets/js/components.js) - Interactive components
- [assets/js/admin-module-dashboard.js](../assets/js/admin-module-dashboard.js) - Card interactions, filters, search

---

## 🏗️ Template Components

### **1. Header Section**

```html
<div class="shahi-dashboard-header">
    <div class="shahi-header-content">
        <div class="shahi-header-text">
            <h1 class="shahi-page-title">
                <span class="shahi-icon-badge">
                    <span class="dashicons dashicons-[your-icon]"></span>
                </span>
                <?php echo esc_html__('[Page Title]', 'shahi-legalops-suite'); ?>
            </h1>
            <p class="shahi-page-subtitle">
                <?php echo esc_html__('[Subtitle description]', 'shahi-legalops-suite'); ?>
            </p>
        </div>
        <div class="shahi-header-actions">
            <!-- Action buttons go here -->
            <button class="shahi-btn shahi-btn-gradient">
                <span class="dashicons dashicons-[icon]"></span>
                <?php echo esc_html__('[Action]', 'shahi-legalops-suite'); ?>
            </button>
        </div>
    </div>
</div>
```

**Button Variants:**
- `.shahi-btn-gradient` - Primary action (cyan/purple gradient)
- `.shahi-btn-outline` - Secondary action (transparent with border)
- `.shahi-btn-primary` - Solid button

### **2. Stats Cards Grid**

```html
<div class="shahi-stats-container">
    <div class="shahi-stat-card shahi-stat-[type]">
        <div class="shahi-stat-icon">
            <span class="dashicons dashicons-[icon]"></span>
        </div>
        <div class="shahi-stat-content">
            <div class="shahi-stat-value">[Value]</div>
            <div class="shahi-stat-label">[Label]</div>
        </div>
        <div class="shahi-stat-progress">
            <div class="shahi-stat-progress-bar" style="width: [%]%"></div>
        </div>
        <div class="shahi-stat-glow"></div>
    </div>
</div>
```

**Card Types:**
- `.shahi-stat-total` - Blue theme (total counts)
- `.shahi-stat-active` - Green theme (active/success)
- `.shahi-stat-inactive` - Red theme (inactive/errors)
- `.shahi-stat-performance` - Purple theme (metrics/scores)

### **3. Filter & Search Bar**

```html
<div class="shahi-controls-bar">
    <div class="shahi-search-wrapper">
        <span class="dashicons dashicons-search"></span>
        <input type="text" 
               id="shahi-[page]-search" 
               class="shahi-search-input" 
               placeholder="<?php echo esc_attr__('Search...', 'shahi-legalops-suite'); ?>">
        <span class="shahi-search-clear dashicons dashicons-no-alt" style="display: none;"></span>
    </div>
    
    <div class="shahi-filter-group">
        <button class="shahi-filter-btn active" data-filter="all">
            <?php echo esc_html__('All', 'shahi-legalops-suite'); ?>
            <span class="shahi-filter-count">[count]</span>
        </button>
        <!-- More filter buttons -->
    </div>

    <div class="shahi-view-toggle">
        <button class="shahi-view-btn active" data-view="grid">
            <span class="dashicons dashicons-grid-view"></span>
        </button>
        <button class="shahi-view-btn" data-view="list">
            <span class="dashicons dashicons-list-view"></span>
        </button>
    </div>
</div>
```

### **4. Content Cards Grid**

```html
<div class="shahi-modules-grid-premium" data-view="grid">
    <div class="shahi-module-card-premium [status-class]" 
         data-[attribute]="[value]"
         data-status="[status]">
        
        <!-- Background Effects -->
        <div class="shahi-card-bg-effect"></div>
        <div class="shahi-card-glow"></div>
        
        <!-- Card Header -->
        <div class="shahi-module-card-header">
            <div class="shahi-module-icon-wrapper">
                <span class="dashicons dashicons-[icon]"></span>
                <div class="shahi-icon-pulse"></div>
            </div>
            <div class="shahi-module-meta">
                <span class="shahi-module-category">[CATEGORY]</span>
                <span class="shahi-module-priority shahi-priority-[level]">[PRIORITY]</span>
            </div>
        </div>

        <!-- Card Body -->
        <div class="shahi-module-card-body">
            <h3 class="shahi-module-title">[Title]</h3>
            <p class="shahi-module-description">[Description]</p>
            
            <!-- Stats -->
            <div class="shahi-module-stats">
                <div class="shahi-stat-item">
                    <span class="dashicons dashicons-[icon]"></span>
                    <span class="shahi-stat-value">[value]</span>
                    <span class="shahi-stat-text">[label]</span>
                </div>
            </div>
        </div>

        <!-- Card Footer -->
        <div class="shahi-module-card-footer">
            <!-- Toggle Switch -->
            <label class="shahi-toggle-switch-premium">
                <input type="checkbox" class="shahi-[feature]-toggle-input">
                <span class="shahi-toggle-slider">
                    <span class="shahi-toggle-icon shahi-toggle-icon-on">
                        <span class="dashicons dashicons-yes"></span>
                    </span>
                    <span class="shahi-toggle-icon shahi-toggle-icon-off">
                        <span class="dashicons dashicons-no"></span>
                    </span>
                </span>
            </label>
            
            <!-- Status Badge -->
            <div class="shahi-module-status-badge">
                <span class="shahi-status-active">
                    <span class="shahi-status-dot"></span>
                    [Status Text]
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="shahi-module-actions">
                <button class="shahi-action-btn" title="[Tooltip]">
                    <span class="dashicons dashicons-[icon]"></span>
                </button>
            </div>
        </div>

        <!-- Status Border -->
        <div class="shahi-card-status-border"></div>
    </div>
</div>
```

**Priority Classes:**
- `.shahi-priority-low` - Green badge (low priority/passed)
- `.shahi-priority-medium` - Orange badge (medium priority/warning)
- `.shahi-priority-high` - Red badge (high priority/critical)

### **5. Empty State**

```html
<div class="shahi-empty-state" style="display: none;">
    <div class="shahi-empty-icon">
        <span class="dashicons dashicons-search"></span>
    </div>
    <h3><?php echo esc_html__('No results found', 'shahi-legalops-suite'); ?></h3>
    <p><?php echo esc_html__('Try adjusting your search or filter criteria', 'shahi-legalops-suite'); ?></p>
</div>
```

### **6. Loading Overlay**

```html
<div class="shahi-loading-overlay" style="display: none;">
    <div class="shahi-spinner">
        <div class="shahi-spinner-ring"></div>
        <div class="shahi-spinner-ring"></div>
        <div class="shahi-spinner-ring"></div>
    </div>
</div>
```

---

## 🎭 Color System

Reference [assets/css/admin-global.css](../assets/css/admin-global.css) for CSS variables:

### **Primary Colors:**
- `--shahi-primary`: `#00d4ff` (Cyan)
- `--shahi-secondary`: `#7c3aed` (Purple)
- `--shahi-accent`: `#ec4899` (Pink)

### **Status Colors:**
- `--shahi-success`: `#10b981` (Green)
- `--shahi-warning`: `#f59e0b` (Orange)
- `--shahi-danger`: `#ef4444` (Red)

### **Background:**
- `--shahi-bg-dark`: `#0a0e27` (Main background)
- `--shahi-bg-card`: `#1e2542` (Card background)
- `--shahi-bg-hover`: `#252d50` (Hover state)

### **Text:**
- `--shahi-text-primary`: `#ffffff`
- `--shahi-text-secondary`: `#94a3b8`
- `--shahi-text-muted`: `#64748b`

---

## 📝 JavaScript Functionality

The [assets/js/admin-module-dashboard.js](../assets/js/admin-module-dashboard.js) provides automatic:

### **Search Filtering:**
Automatically works with any element having:
- Input: `#shahi-[anything]-search`
- Cards: `.shahi-module-card-premium` with `data-[searchable-attribute]`

### **Tab Filtering:**
Automatically works with:
- Buttons: `.shahi-filter-btn` with `data-filter="[value]"`
- Cards: `.shahi-module-card-premium` with `data-status="[value]"`

### **View Toggle:**
Automatically works with:
- Buttons: `.shahi-view-btn` with `data-view="grid|list"`
- Container: `.shahi-modules-grid-premium` with `data-view` attribute

### **Custom Interactions:**
Add page-specific JavaScript in template footer or create dedicated JS file loaded via `Assets.php`.

---

## ⚠️ Common Mistakes to Avoid

### **❌ DON'T:**
1. Manually enqueue assets in page class
2. Add `admin_enqueue_scripts` hooks in module classes
3. Create separate CSS files (reuse global system)
4. Use inline styles (use utility classes instead)
5. Hardcode colors (use CSS variables)

### **✅ DO:**
1. Let [includes/Core/Assets.php](../includes/Core/Assets.php) handle all asset loading
2. Use existing `.shahi-*` classes from [assets/css/components.css](../assets/css/components.css)
3. Follow the wrapper structure (`<div class="wrap shahi-module-dashboard">`)
4. Use data attributes for JavaScript interactions
5. Reference [templates/admin/module-dashboard.php](../templates/admin/module-dashboard.php) and [templates/admin/accessibility-dashboard.php](../templates/admin/accessibility-dashboard.php) as examples

---

## 🔍 Reference Files

### **Must Review:**
1. **Template Examples:**
   - [templates/admin/module-dashboard.php](../templates/admin/module-dashboard.php) - Complete implementation
   - [templates/admin/accessibility-dashboard.php](../templates/admin/accessibility-dashboard.php) - Converted example

2. **Asset Management:**
   - [includes/Core/Assets.php](../includes/Core/Assets.php) - Global asset loader (lines 82-190 for styles, 337-490 for scripts)

3. **Page Class Example:**
   - [includes/Modules/AccessibilityScanner/Admin/AccessibilityDashboard.php](../includes/Modules/AccessibilityScanner/Admin/AccessibilityDashboard.php) - Clean class structure

4. **Design System:**
   - [assets/css/admin-global.css](../assets/css/admin-global.css) - Variables, base styles
   - [assets/css/components.css](../assets/css/components.css) - Reusable components
   - [assets/css/admin-module-dashboard.css](../assets/css/admin-module-dashboard.css) - Premium card styles

5. **JavaScript:**
   - [assets/js/admin-global.js](../assets/js/admin-global.js) - Core utilities
   - [assets/js/components.js](../assets/js/components.js) - Component behaviors
   - [assets/js/admin-module-dashboard.js](../assets/js/admin-module-dashboard.js) - Interactive features

---

## 🚀 Conversion Checklist

- [ ] Add page detection to `Assets.php->is_plugin_page()`
- [ ] Create `is_[your_page]_page()` method in `Assets.php`
- [ ] Add CSS enqueuing in `enqueue_admin_styles()`
- [ ] Add JS enqueuing in `enqueue_admin_scripts()`
- [ ] Remove any manual asset enqueuing from page class
- [ ] Update template with `shahi-module-dashboard` wrapper
- [ ] Implement header section with `.shahi-dashboard-header`
- [ ] Add stats cards with `.shahi-stats-container`
- [ ] Add filter/search bar with `.shahi-controls-bar`
- [ ] Convert content to `.shahi-modules-grid-premium` cards
- [ ] Add empty state with `.shahi-empty-state`
- [ ] Add loading overlay with `.shahi-loading-overlay`
- [ ] Test search functionality
- [ ] Test filter functionality
- [ ] Test grid/list view toggle
- [ ] Clear WordPress cache
- [ ] Hard refresh browser (Ctrl+Shift+R)

---

## 📞 Troubleshooting

### **Styles not loading:**
1. Check `Assets.php->is_plugin_page()` recognizes your hook
2. Verify detection method exists and returns true
3. Check CSS enqueuing condition matches your detection
4. Clear WordPress cache: `wp-content/plugins/Shahi LegalOps Suite/clear-cache.php`

### **JavaScript not working:**
1. Verify JS file is enqueued in `Assets.php`
2. Check browser console for errors
3. Ensure data attributes match expected format
4. Verify jQuery is loaded as dependency

### **Cards look broken:**
1. Confirm wrapper is `<div class="wrap shahi-module-dashboard">`
2. Check card structure matches reference template
3. Verify all required child elements exist
4. Inspect for missing CSS classes

---

## 📄 Example Conversion Prompt

**Template for AI Assistants:**

```
Convert [PAGE_NAME] to use the global design system:

1. Update Assets.php:
   - Add is_[page_slug]_page() detection method
   - Enqueue 'shahi-admin-module-dashboard' CSS and JS

2. Clean Page Class:
   - Remove enqueue_assets() method
   - Keep only render() and data methods
   - Reference: includes/Modules/AccessibilityScanner/Admin/AccessibilityDashboard.php

3. Update Template:
   - Wrapper: <div class="wrap shahi-module-dashboard">
   - Header: Use .shahi-dashboard-header structure
   - Stats: 4 cards in .shahi-stats-container
   - Filters: .shahi-controls-bar with search + filter tabs
   - Content: .shahi-modules-grid-premium with .shahi-module-card-premium items
   - Features: Empty state + loading overlay

4. Data Attributes:
   - Cards: data-[searchable-field]="value" for search
   - Cards: data-status="value" for filtering
   - Preserve existing functionality, wrap in new UI

Reference: templates/admin/module-dashboard.php & accessibility-dashboard.php
```

---

**Last Updated:** December 17, 2025  
**Version:** 1.0  
**Compatibility:** Shahi LegalOps Suite v1.0+
