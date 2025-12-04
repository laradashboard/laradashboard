# LaraBuilder Documentation

LaraBuilder is an extensible visual content builder for creating email templates, web pages, and custom content. It provides a drag-and-drop interface with undo/redo support, context-aware blocks, and a WordPress-style hook system.

## Table of Contents

- [Features](#features)
- [Quick Start](#quick-start)
- [Architecture Overview](#architecture-overview)
- [Contexts](#contexts)
- [Registering Custom Blocks](#registering-custom-blocks)
  - [From PHP](#registering-blocks-from-php)
  - [From JavaScript](#registering-blocks-from-javascript)
- [Using Hooks](#using-hooks)
  - [JavaScript Hooks](#javascript-hooks)
  - [PHP Hooks](#php-hooks)
- [Output Adapters](#output-adapters)
- [Styling Blocks](#styling-blocks)
- [Keyboard Shortcuts](#keyboard-shortcuts)
- [API Reference](#api-reference)

---

## Features

- **Drag-and-drop** block-based editing
- **Undo/Redo** with full history tracking (50 entries)
- **Context-aware blocks** - Different blocks for email, page, campaign
- **WordPress-style hooks** - Extend functionality via filters and actions
- **Output adapters** - Email-safe HTML (tables) vs modern HTML5
- **Third-party extensibility** - Modules can register custom blocks
- **Responsive** - Works on desktop and mobile

---

## Quick Start

### Using in a Blade View

```blade
<!DOCTYPE html>
<html>
<head>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/lara-builder/entry.jsx', 'resources/js/app.js'])

    {{-- Inject PHP-registered blocks --}}
    {!! app(\App\Services\Builder\BuilderService::class)->injectToFrontend('email') !!}
</head>
<body>
    <div
        id="lara-builder-root"
        data-context="email"
        data-initial-data="{{ json_encode($initialData) }}"
        data-template-data="{{ json_encode($templateData) }}"
        data-save-url="{{ route('admin.email-templates.store') }}"
        data-list-url="{{ route('admin.email-templates.index') }}"
        data-upload-url="{{ route('admin.upload-image') }}"
    ></div>
</body>
</html>
```

### Using the Blade Component

```blade
<x-builder.lara-builder
    context="email"
    :initial-data="$initialData"
    :template-data="$templateData"
    :save-url="route('admin.email-templates.store')"
    :list-url="route('admin.email-templates.index')"
    :upload-url="route('admin.upload-image')"
/>
```

---

## Architecture Overview

```
resources/js/lara-builder/
├── core/                    # Core React components and state
│   ├── LaraBuilder.jsx      # Main builder component
│   ├── BuilderContext.jsx   # React context provider
│   ├── BuilderReducer.js    # State with undo/redo
│   └── hooks/               # Custom React hooks
├── hooks-system/            # WordPress-style hooks
│   ├── LaraHooks.js         # Filter/action system
│   └── HookNames.js         # Hook constants
├── registry/                # Block and adapter registries
│   ├── BlockRegistry.js     # Block registration
│   └── OutputAdapterRegistry.js
├── adapters/                # HTML output generators
│   ├── BaseAdapter.js       # Abstract base
│   ├── EmailAdapter.js      # Email-safe HTML
│   └── WebAdapter.js        # Modern HTML5
├── entry.jsx                # Auto-initialization entry point
└── index.js                 # Main exports

app/
├── Enums/Builder/           # PHP enums
│   ├── BuilderContext.php
│   ├── BuilderFilterHook.php
│   └── BuilderActionHook.php
├── Services/Builder/        # PHP services
│   ├── BuilderService.php
│   └── BlockRegistryService.php
└── Providers/
    └── BuilderServiceProvider.php
```

---

## Contexts

LaraBuilder supports multiple contexts, each with its own block set and output format:

| Context | Adapter | Description |
|---------|---------|-------------|
| `email` | EmailAdapter | Email-safe HTML with tables and inline styles |
| `page` | WebAdapter | Modern HTML5 with CSS classes |
| `campaign` | EmailAdapter | Same as email, with personalization support |

### Using Different Contexts

```blade
{{-- Email Builder --}}
<div id="lara-builder-root" data-context="email"></div>

{{-- Page Builder --}}
<div id="lara-builder-root" data-context="page"></div>

{{-- Campaign Builder --}}
<div id="lara-builder-root" data-context="campaign"></div>
```

---

## Registering Custom Blocks

### Registering Blocks from PHP

Register blocks in a module's ServiceProvider:

```php
<?php

namespace Modules\Crm\Providers;

use App\Services\Builder\BuilderService;
use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $builder = app(BuilderService::class);

        // Register a custom block
        $builder->registerBlock([
            'type' => 'crm-product',        // Unique identifier (lowercase, hyphens)
            'label' => 'Product Card',      // Display name
            'category' => 'CRM',            // Category in block panel
            'icon' => 'mdi:package-variant', // Iconify icon
            'contexts' => ['email', 'campaign'], // Where this block appears
            'defaultProps' => [
                'productId' => null,
                'showPrice' => true,
                'showDescription' => true,
            ],
            'component' => 'CrmProductBlock', // React component name
            'supports' => [
                'align' => true,
                'spacing' => true,
                'colors' => true,
            ],
        ]);
    }
}
```

### Registering Blocks from JavaScript

```javascript
import { blockRegistry } from '@/lara-builder';
import CrmProductBlock from './components/CrmProductBlock';

// Register the block
blockRegistry.register({
    type: 'crm-product',
    label: 'Product Card',
    category: 'CRM',
    icon: 'mdi:package-variant',
    contexts: ['email', 'campaign'],
    defaultProps: {
        productId: null,
        showPrice: true,
        showDescription: true,
    },
    component: CrmProductBlock,

    // Custom HTML generators per context
    htmlGenerator: {
        email: (props, options) => {
            return `
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding: 16px; background: #f8f9fa;">
                            <h3 style="margin: 0;">${props.productName || 'Product'}</h3>
                            ${props.showPrice ? `<p style="color: #635bff;">$${props.price}</p>` : ''}
                            ${props.showDescription ? `<p>${props.description}</p>` : ''}
                        </td>
                    </tr>
                </table>
            `;
        },
        page: (props, options) => {
            return `
                <div class="product-card">
                    <h3>${props.productName || 'Product'}</h3>
                    ${props.showPrice ? `<p class="price">$${props.price}</p>` : ''}
                    ${props.showDescription ? `<p>${props.description}</p>` : ''}
                </div>
            `;
        },
    },
});
```

### Creating the React Component

```jsx
// modules/crm/resources/js/components/CrmProductBlock.jsx
import React from 'react';

export default function CrmProductBlock({
    props,
    isSelected,
    onUpdate,
    blockId
}) {
    return (
        <div
            className={`p-4 bg-gray-50 rounded-lg ${isSelected ? 'ring-2 ring-primary' : ''}`}
        >
            <h3 className="font-semibold text-lg">
                {props.productName || 'Select a product'}
            </h3>
            {props.showPrice && props.price && (
                <p className="text-primary font-bold">${props.price}</p>
            )}
            {props.showDescription && props.description && (
                <p className="text-gray-600">{props.description}</p>
            )}
        </div>
    );
}
```

### Adding Property Editors

The properties panel uses the block's `defaultProps` to render editors. For custom property editors, you can extend the PropertiesPanel or use hooks:

```javascript
import { LaraHooks } from '@/lara-builder';

// Add custom property editor for crm-product block
LaraHooks.addFilter('builder.properties.crm-product', (editors, block) => {
    return [
        ...editors,
        {
            type: 'product-picker',
            prop: 'productId',
            label: 'Select Product',
        },
    ];
});
```

---

## Using Hooks

### JavaScript Hooks

LaraBuilder uses a WordPress-style hook system:

```javascript
import { LaraHooks, BuilderHooks } from '@/lara-builder';

// ===== FILTERS =====
// Modify data before it's used

// Filter available blocks for a context
LaraHooks.addFilter(BuilderHooks.FILTER_BLOCKS, (blocks, context) => {
    if (context === 'email') {
        // Add a custom block
        return [...blocks, myCustomBlock];
    }
    return blocks;
}, 10); // priority (lower runs first)

// Filter generated HTML
LaraHooks.addFilter(BuilderHooks.FILTER_HTML_GENERATED, (html, blocks, settings) => {
    // Add tracking pixel
    return html.replace('</body>', '<img src="/track.gif" /></body>');
});

// Filter block-specific HTML
LaraHooks.addFilter('builder.html.block.heading', (html, props, options) => {
    // Wrap headings in a container
    return `<div class="heading-wrapper">${html}</div>`;
});

// ===== ACTIONS =====
// React to events

// When a block is added
LaraHooks.addAction(BuilderHooks.ACTION_BLOCK_ADDED, (block, index) => {
    console.log(`Block ${block.type} added at index ${index}`);
    analytics.track('block_added', { type: block.type });
});

// Before save
LaraHooks.addAction(BuilderHooks.ACTION_BEFORE_SAVE, (state) => {
    console.log('Saving...', state.blocks.length, 'blocks');
});

// After save
LaraHooks.addAction(BuilderHooks.ACTION_AFTER_SAVE, (result) => {
    console.log('Saved successfully!', result);
});

// On undo/redo
LaraHooks.addAction(BuilderHooks.ACTION_UNDO, () => {
    console.log('Undo performed');
});
```

### Available Hook Names

```javascript
// Filters
BuilderHooks.FILTER_BLOCKS          // Filter available blocks
BuilderHooks.FILTER_CONFIG          // Filter builder configuration
BuilderHooks.FILTER_HTML_GENERATED  // Filter final HTML output
BuilderHooks.FILTER_HTML_BLOCK      // Filter single block HTML
BuilderHooks.FILTER_CANVAS_SETTINGS // Filter canvas settings

// Actions
BuilderHooks.ACTION_INIT            // Builder initialized
BuilderHooks.ACTION_BLOCK_ADDED     // Block added
BuilderHooks.ACTION_BLOCK_UPDATED   // Block properties updated
BuilderHooks.ACTION_BLOCK_DELETED   // Block deleted
BuilderHooks.ACTION_BLOCK_MOVED     // Block position changed
BuilderHooks.ACTION_BEFORE_SAVE     // Before save starts
BuilderHooks.ACTION_AFTER_SAVE      // After successful save
BuilderHooks.ACTION_SAVE_ERROR      // Save failed
BuilderHooks.ACTION_UNDO            // Undo performed
BuilderHooks.ACTION_REDO            // Redo performed
```

### PHP Hooks

```php
use App\Services\Builder\BuilderService;
use App\Enums\Builder\BuilderFilterHook;
use App\Enums\Builder\BuilderActionHook;

$builder = app(BuilderService::class);

// Add a filter
$builder->addFilter(BuilderFilterHook::BUILDER_BLOCKS_EMAIL, function ($blocks) {
    // Add server-side blocks
    $blocks['newsletter-signup'] = [
        'type' => 'newsletter-signup',
        'label' => 'Newsletter Signup',
        'category' => 'Marketing',
    ];
    return $blocks;
});

// Add an action
$builder->addAction(BuilderActionHook::BUILDER_AFTER_SAVE, function ($result) {
    // Log the save
    Log::info('Builder content saved', ['id' => $result['id']]);
});
```

---

## Output Adapters

Adapters generate HTML for different contexts:

### EmailAdapter (email, campaign)

- Uses HTML tables for layout
- Inline CSS styles
- MSO conditionals for Outlook
- Video thumbnails with play button overlay
- No JavaScript

### WebAdapter (page)

- Modern HTML5 semantic elements
- CSS classes (not inline)
- Native video embeds
- Responsive flexbox/grid layouts
- Live countdown timers with JS

### Creating a Custom Adapter

```javascript
import { BaseAdapter, OutputAdapterRegistry } from '@/lara-builder';

class MyCustomAdapter extends BaseAdapter {
    constructor() {
        super('my-context');
    }

    getDefaultSettings() {
        return {
            width: '800px',
            backgroundColor: '#ffffff',
        };
    }

    generateBlockHtml(block, options = {}) {
        const { type, props } = block;

        switch (type) {
            case 'heading':
                return `<h2 class="my-heading">${props.text}</h2>`;
            // ... handle other blocks
            default:
                return '';
        }
    }

    wrapOutput(content, settings) {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <link rel="stylesheet" href="/my-styles.css">
            </head>
            <body>
                ${content}
            </body>
            </html>
        `;
    }
}

// Register the adapter
OutputAdapterRegistry.register('my-context', new MyCustomAdapter());
```

---

## Styling Blocks

### Canvas Preview Styles

Blocks are styled in the canvas using Tailwind CSS. Each block component receives:

- `props` - Block properties
- `isSelected` - Whether the block is selected
- `onUpdate` - Function to update properties
- `blockId` - Unique block ID
- `context` - Current context (email, page, etc.)

```jsx
function MyBlock({ props, isSelected }) {
    return (
        <div
            className={`
                p-4 rounded-lg transition-all
                ${isSelected ? 'ring-2 ring-primary shadow-lg' : 'hover:shadow-md'}
            `}
            style={{
                backgroundColor: props.backgroundColor,
                color: props.textColor,
            }}
        >
            {props.content}
        </div>
    );
}
```

### Email HTML Styles

Email blocks must use inline styles for compatibility:

```javascript
htmlGenerator: {
    email: (props) => `
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td style="
                    padding: 16px;
                    background-color: ${props.backgroundColor || '#ffffff'};
                    color: ${props.textColor || '#333333'};
                    font-family: Arial, sans-serif;
                ">
                    ${props.content}
                </td>
            </tr>
        </table>
    `,
}
```

### Web HTML Styles

Web blocks can use CSS classes:

```javascript
htmlGenerator: {
    page: (props) => `
        <div class="lb-block lb-my-block" style="--bg-color: ${props.backgroundColor}">
            ${props.content}
        </div>
    `,
}
```

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + Z` | Undo |
| `Ctrl/Cmd + Shift + Z` | Redo |
| `Ctrl/Cmd + Y` | Redo (alternate) |
| `Delete` / `Backspace` | Delete selected block |
| `Enter` | Add new text block after selected |

---

## API Reference

### BlockRegistry

```javascript
import { blockRegistry } from '@/lara-builder';

// Register a block
blockRegistry.register(definition);

// Get a block definition
const block = blockRegistry.get('heading');

// Get blocks for a context
const emailBlocks = blockRegistry.getBlocksForContext('email');

// Get blocks by category
const grouped = blockRegistry.getByCategory('email');

// Create a block instance
const newBlock = blockRegistry.createInstance('heading', { text: 'Hello' });

// Check if block exists
const exists = blockRegistry.has('my-block');

// Search blocks
const results = blockRegistry.search('image');
```

### OutputAdapterRegistry

```javascript
import { OutputAdapterRegistry } from '@/lara-builder';

// Register an adapter
OutputAdapterRegistry.register('email', new EmailAdapter());

// Get an adapter
const adapter = OutputAdapterRegistry.get('email');

// Generate HTML
const html = OutputAdapterRegistry.generateHtml('email', blocks, settings);

// Get default settings for a context
const defaults = OutputAdapterRegistry.getDefaultSettings('email');
```

### LaraHooks

```javascript
import { LaraHooks } from '@/lara-builder';

// Add a filter (modifies data)
LaraHooks.addFilter(hookName, callback, priority);

// Apply filters (get modified data)
const result = LaraHooks.applyFilters(hookName, value, ...args);

// Add an action (side effect)
LaraHooks.addAction(hookName, callback, priority);

// Trigger actions
LaraHooks.doAction(hookName, ...args);

// Remove hooks
LaraHooks.removeFilter(hookName, callback);
LaraHooks.removeAction(hookName, callback);
```

### BuilderService (PHP)

```php
use App\Services\Builder\BuilderService;

$builder = app(BuilderService::class);

// Register a block
$builder->registerBlock([...]);

// Get block registry
$blocks = $builder->blocks();

// Get configuration for a context
$config = $builder->getConfig('email');

// Inject to frontend
echo $builder->injectToFrontend('email');

// Add hooks
$builder->addFilter(BuilderFilterHook::BUILDER_BLOCKS, fn($b) => $b);
$builder->addAction(BuilderActionHook::BUILDER_AFTER_SAVE, fn($r) => log($r));
```

---

## Example: Complete Custom Block

Here's a complete example of a custom "Testimonial" block:

### 1. PHP Registration (ServiceProvider)

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    $builder = app(BuilderService::class);

    $builder->registerBlock([
        'type' => 'testimonial',
        'label' => 'Testimonial',
        'category' => 'Content',
        'icon' => 'mdi:format-quote-close',
        'contexts' => ['email', 'page'],
        'defaultProps' => [
            'quote' => 'This product changed my life!',
            'author' => 'John Doe',
            'role' => 'CEO, Company',
            'avatar' => '',
            'rating' => 5,
            'backgroundColor' => '#f8fafc',
            'textColor' => '#475569',
        ],
        'component' => 'TestimonialBlock',
    ]);
}
```

### 2. React Component

```jsx
// resources/js/components/TestimonialBlock.jsx
export default function TestimonialBlock({ props, isSelected, onUpdate }) {
    const stars = '★'.repeat(props.rating) + '☆'.repeat(5 - props.rating);

    return (
        <div
            className={`p-6 rounded-lg ${isSelected ? 'ring-2 ring-primary' : ''}`}
            style={{ backgroundColor: props.backgroundColor }}
        >
            <div className="text-yellow-500 text-xl mb-2">{stars}</div>
            <p
                className="text-lg italic mb-4"
                style={{ color: props.textColor }}
            >
                "{props.quote}"
            </p>
            <div className="flex items-center gap-3">
                {props.avatar && (
                    <img
                        src={props.avatar}
                        alt={props.author}
                        className="w-12 h-12 rounded-full"
                    />
                )}
                <div>
                    <p className="font-semibold">{props.author}</p>
                    <p className="text-sm text-gray-500">{props.role}</p>
                </div>
            </div>
        </div>
    );
}
```

### 3. Register Component & HTML Generator

```javascript
// resources/js/app.js or a dedicated file
import { blockRegistry } from '@/lara-builder';
import TestimonialBlock from './components/TestimonialBlock';

blockRegistry.register({
    type: 'testimonial',
    component: TestimonialBlock,
    htmlGenerator: {
        email: (props) => `
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: ${props.backgroundColor}; border-radius: 8px;">
                <tr>
                    <td style="padding: 24px;">
                        <p style="color: #eab308; font-size: 20px; margin: 0 0 8px 0;">
                            ${'★'.repeat(props.rating)}${'☆'.repeat(5 - props.rating)}
                        </p>
                        <p style="color: ${props.textColor}; font-size: 18px; font-style: italic; margin: 0 0 16px 0;">
                            "${props.quote}"
                        </p>
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                ${props.avatar ? `
                                    <td style="padding-right: 12px;">
                                        <img src="${props.avatar}" width="48" height="48" style="border-radius: 50%;" />
                                    </td>
                                ` : ''}
                                <td>
                                    <p style="font-weight: 600; margin: 0;">${props.author}</p>
                                    <p style="color: #6b7280; font-size: 14px; margin: 0;">${props.role}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        `,
        page: (props) => `
            <div class="testimonial-block" style="background-color: ${props.backgroundColor}; padding: 24px; border-radius: 8px;">
                <div class="stars" style="color: #eab308; font-size: 20px;">
                    ${'★'.repeat(props.rating)}${'☆'.repeat(5 - props.rating)}
                </div>
                <p style="color: ${props.textColor}; font-size: 18px; font-style: italic;">
                    "${props.quote}"
                </p>
                <div style="display: flex; align-items: center; gap: 12px;">
                    ${props.avatar ? `<img src="${props.avatar}" style="width: 48px; height: 48px; border-radius: 50%;" />` : ''}
                    <div>
                        <p style="font-weight: 600; margin: 0;">${props.author}</p>
                        <p style="color: #6b7280; font-size: 14px; margin: 0;">${props.role}</p>
                    </div>
                </div>
            </div>
        `,
    },
});
```

---

## Troubleshooting

### Blocks Not Appearing

1. Check the `contexts` array includes your target context
2. Ensure the block is registered before the builder initializes
3. Check browser console for registration errors

### HTML Not Generating

1. Verify `htmlGenerator` has the correct context key
2. Check that all required props have values
3. Use `LaraHooks.addFilter('builder.html.block.{type}')` to debug

### Undo/Redo Not Working

1. Ensure you're using `actions.updateBlock()` not direct state mutation
2. Check that history isn't at max capacity (50 entries)
3. Verify keyboard shortcuts aren't captured by other elements

### Custom Blocks Not Saving

1. Ensure `defaultProps` matches what your component expects
2. Check that `onUpdate` is being called with the correct props
3. Verify the save endpoint accepts the block data structure
