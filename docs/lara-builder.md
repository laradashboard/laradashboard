# LaraBuilder Documentation

LaraBuilder is an extensible visual content builder for creating email templates, web pages, and custom content. It provides a drag-and-drop interface with undo/redo support, context-aware blocks, and a WordPress-style hook system.

## Table of Contents

- [Features](#features)
- [Quick Start](#quick-start)
- [Architecture Overview](#architecture-overview)
- [Block Structure](#block-structure)
- [Import Aliases](#import-aliases)
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
├── blocks/                  # Core blocks
│   ├── heading/             # Example block structure
│   │   ├── index.js         # Block definition
│   │   ├── block.json       # Block metadata
│   │   ├── block.jsx        # Canvas component
│   │   ├── editor.jsx       # Properties panel
│   │   └── save.js          # HTML generators
│   └── ...
├── utils/                   # Shared utilities
│   ├── index.js             # Main exports
│   └── save.js              # Block save utilities
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

## Block Structure

Each block is a self-contained module with a consistent file structure:

```
blocks/
└── my-block/
    ├── index.js      # Block definition (exports block, editor, save)
    ├── block.json    # Block metadata and configuration
    ├── block.jsx     # React component for builder canvas
    ├── editor.jsx    # React component for properties panel
    └── save.js       # HTML generators for page/email output
```

### Block Definition (index.js)

```javascript
import block from './block';
import editor from './editor';
import config from './block.json';
import save from './save';

const defaultLayoutStyles = {
    margin: { top: '', right: '', bottom: '', left: '' },
    padding: { top: '', right: '', bottom: '', left: '' },
    width: '', minWidth: '', maxWidth: '',
    height: '', minHeight: '', maxHeight: '',
};

const myBlock = {
    ...config,
    block,      // React component for canvas
    editor,     // React component for properties panel
    save,       // HTML generators { page, email }
    defaultProps: {
        ...config.defaultProps,
        layoutStyles: { ...defaultLayoutStyles },
    },
};

export { block, editor, config, save };
export default myBlock;
```

### Block Metadata (block.json)

```json
{
    "type": "my-block",
    "label": "My Block",
    "category": "Content",
    "icon": "lucide:box",
    "description": "A custom block description",
    "keywords": ["custom", "block"],
    "contexts": ["email", "page"],
    "defaultProps": {
        "text": "Default text",
        "color": "#333333"
    },
    "supports": {
        "align": true,
        "spacing": true,
        "colors": true
    }
}
```

### Block Component (block.jsx)

```jsx
import React from 'react';

export default function Block({ props, isSelected, onUpdate, blockId, context }) {
    return (
        <div className={`p-4 ${isSelected ? 'ring-2 ring-primary' : ''}`}>
            <p style={{ color: props.color }}>{props.text}</p>
        </div>
    );
}
```

### Editor Component (editor.jsx)

```jsx
import React from 'react';

export default function Editor({ props, onUpdate, renderField }) {
    return (
        <div className="space-y-4">
            {renderField({
                type: 'text',
                label: 'Text',
                value: props.text,
                onChange: (value) => onUpdate({ ...props, text: value }),
            })}
            {renderField({
                type: 'color',
                label: 'Color',
                value: props.color,
                onChange: (value) => onUpdate({ ...props, color: value }),
            })}
        </div>
    );
}
```

### Save/HTML Generators (save.js)

```javascript
import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

export const page = (props, options = {}) => {
    const type = 'my-block';
    const blockClasses = buildBlockClasses(type, props);
    const mergedStyles = mergeBlockStyles(props, `color: ${props.color || '#333'}`);

    return `
        <div class="${blockClasses}" style="${mergedStyles}">
            <p>${props.text || ''}</p>
        </div>
    `;
};

export const email = (props, options = {}) => {
    return `
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td style="color: ${props.color || '#333333'}; padding: 16px;">
                    ${props.text || ''}
                </td>
            </tr>
        </table>
    `;
};

export default { page, email };
```

---

## Import Aliases

LaraBuilder provides Vite path aliases for cleaner imports, especially useful for plugins and modules:

| Alias | Path | Description |
|-------|------|-------------|
| `@lara-builder` | `resources/js/lara-builder` | Main LaraBuilder directory |
| `@lara-builder/blocks` | `resources/js/lara-builder/blocks` | Core blocks directory |
| `@lara-builder/utils` | `resources/js/lara-builder/utils` | Shared utility functions |

### Usage in save.js

```javascript
// Core blocks or plugin blocks can import utilities the same way
import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';
```

### Usage in Custom Blocks (Modules/Plugins)

```javascript
// Import from main LaraBuilder
import { blockRegistry, LaraHooks } from '@lara-builder';

// Import core blocks (for extending or referencing)
import headingBlock from '@lara-builder/blocks/heading';

// Import save utilities
import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';
```

### Available Utilities

The `@lara-builder/utils` module exports:

```javascript
// Build consistent CSS class names for blocks
// Returns: "lb-block lb-{type} {customClass}"
buildBlockClasses(type, props);

// Merge layout styles (margin, padding, border, etc.) with block-specific styles
// Returns: combined style string
mergeBlockStyles(props, blockSpecificStyles);
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

For full-featured blocks, create the complete block structure:

```javascript
// my-block/index.js
import block from './block';
import editor from './editor';
import save from './save';

const myBlockDefinition = {
    type: 'my-custom-block',
    label: 'My Custom Block',
    category: 'Custom',
    icon: 'lucide:star',
    contexts: ['email', 'page'],
    defaultProps: {
        title: 'Hello World',
        color: '#635bff',
    },
    block,
    editor,
    save,
};

// Register with BlockRegistry
import { blockRegistry } from '@/lara-builder';
blockRegistry.register(myBlockDefinition);

export default myBlockDefinition;
```

For simpler inline registration:

```javascript
import { blockRegistry } from '@/lara-builder';

blockRegistry.register({
    type: 'simple-block',
    label: 'Simple Block',
    category: 'Content',
    icon: 'lucide:box',
    contexts: ['page'],
    defaultProps: {
        text: 'Hello',
    },
    block: ({ props, isSelected }) => (
        <div className={isSelected ? 'ring-2 ring-primary' : ''}>
            {props.text}
        </div>
    ),
    editor: ({ props, onUpdate }) => (
        <input
            value={props.text}
            onChange={(e) => onUpdate({ ...props, text: e.target.value })}
        />
    ),
    save: {
        page: (props) => `<div>${props.text}</div>`,
        email: (props) => `<td>${props.text}</td>`,
    },
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
        const blockDef = blockRegistry.get(type);

        // Use block's save function if available
        if (blockDef?.save?.['my-context']) {
            return blockDef.save['my-context'](props, options);
        }

        // Fallback
        return '';
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
// save.js
export const email = (props) => `
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
`;
```

### Web HTML Styles

Web blocks can use CSS classes:

```javascript
// save.js
export const page = (props) => `
    <div class="lb-block lb-my-block" style="--bg-color: ${props.backgroundColor}">
        ${props.content}
    </div>
`;
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
const blockDef = blockRegistry.get('heading');

// Access block parts
blockDef.block;   // Canvas component
blockDef.editor;  // Properties panel component
blockDef.save;    // HTML generators { page, email }

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

Here's a complete example of a custom "Testimonial" block with the new simplified structure:

### 1. File Structure

```
blocks/
└── testimonial/
    ├── index.js
    ├── block.json
    ├── block.jsx
    ├── editor.jsx
    └── save.js
```

### 2. Block Metadata (block.json)

```json
{
    "type": "testimonial",
    "label": "Testimonial",
    "category": "Content",
    "icon": "mdi:format-quote-close",
    "description": "Display customer testimonials with ratings",
    "keywords": ["testimonial", "quote", "review", "rating"],
    "contexts": ["email", "page"],
    "defaultProps": {
        "quote": "This product changed my life!",
        "author": "John Doe",
        "role": "CEO, Company",
        "avatar": "",
        "rating": 5,
        "backgroundColor": "#f8fafc",
        "textColor": "#475569"
    },
    "supports": {
        "align": true,
        "spacing": true,
        "colors": true
    }
}
```

### 3. Block Component (block.jsx)

```jsx
import React from 'react';

export default function Block({ props, isSelected }) {
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
                        className="w-12 h-12 rounded-full object-cover"
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

### 4. Editor Component (editor.jsx)

```jsx
import React from 'react';

export default function Editor({ props, onUpdate, onImageUpload, renderField }) {
    return (
        <div className="space-y-4">
            {renderField({
                type: 'textarea',
                label: 'Quote',
                value: props.quote,
                onChange: (value) => onUpdate({ ...props, quote: value }),
                rows: 3,
            })}

            {renderField({
                type: 'text',
                label: 'Author Name',
                value: props.author,
                onChange: (value) => onUpdate({ ...props, author: value }),
            })}

            {renderField({
                type: 'text',
                label: 'Role / Company',
                value: props.role,
                onChange: (value) => onUpdate({ ...props, role: value }),
            })}

            {renderField({
                type: 'image',
                label: 'Avatar',
                value: props.avatar,
                onChange: (value) => onUpdate({ ...props, avatar: value }),
                onUpload: onImageUpload,
            })}

            {renderField({
                type: 'range',
                label: 'Rating',
                value: props.rating,
                onChange: (value) => onUpdate({ ...props, rating: parseInt(value) }),
                min: 0,
                max: 5,
                step: 1,
            })}

            {renderField({
                type: 'color',
                label: 'Background Color',
                value: props.backgroundColor,
                onChange: (value) => onUpdate({ ...props, backgroundColor: value }),
            })}

            {renderField({
                type: 'color',
                label: 'Text Color',
                value: props.textColor,
                onChange: (value) => onUpdate({ ...props, textColor: value }),
            })}
        </div>
    );
}
```

### 5. Save/HTML Generators (save.js)

```javascript
import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'testimonial';
    const blockClasses = buildBlockClasses(type, props);
    const stars = '★'.repeat(props.rating) + '☆'.repeat(5 - props.rating);

    const blockStyles = [
        `background-color: ${props.backgroundColor || '#f8fafc'}`,
        'padding: 24px',
        'border-radius: 8px',
    ];

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));

    return `
        <div class="${blockClasses}" style="${mergedStyles}">
            <div style="color: #eab308; font-size: 20px; margin-bottom: 8px;">
                ${stars}
            </div>
            <p style="color: ${props.textColor || '#475569'}; font-size: 18px; font-style: italic; margin: 0 0 16px 0;">
                "${props.quote || ''}"
            </p>
            <div style="display: flex; align-items: center; gap: 12px;">
                ${props.avatar ? `
                    <img src="${props.avatar}" alt="${props.author}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;" />
                ` : ''}
                <div>
                    <p style="font-weight: 600; margin: 0;">${props.author || ''}</p>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">${props.role || ''}</p>
                </div>
            </div>
        </div>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const stars = '★'.repeat(props.rating) + '☆'.repeat(5 - props.rating);

    return `
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: ${props.backgroundColor || '#f8fafc'}; border-radius: 8px;">
            <tr>
                <td style="padding: 24px;">
                    <p style="color: #eab308; font-size: 20px; margin: 0 0 8px 0;">
                        ${stars}
                    </p>
                    <p style="color: ${props.textColor || '#475569'}; font-size: 18px; font-style: italic; margin: 0 0 16px 0;">
                        "${props.quote || ''}"
                    </p>
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            ${props.avatar ? `
                                <td style="padding-right: 12px; vertical-align: middle;">
                                    <img src="${props.avatar}" width="48" height="48" style="border-radius: 50%; display: block;" alt="${props.author}" />
                                </td>
                            ` : ''}
                            <td style="vertical-align: middle;">
                                <p style="font-weight: 600; margin: 0;">${props.author || ''}</p>
                                <p style="color: #6b7280; font-size: 14px; margin: 0;">${props.role || ''}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    `;
};

export default { page, email };
```

### 6. Block Definition (index.js)

```javascript
import block from './block';
import editor from './editor';
import config from './block.json';
import save from './save';

const defaultLayoutStyles = {
    margin: { top: '', right: '', bottom: '', left: '' },
    padding: { top: '', right: '', bottom: '', left: '' },
    width: '', minWidth: '', maxWidth: '',
    height: '', minHeight: '', maxHeight: '',
};

const testimonialBlock = {
    ...config,
    block,
    editor,
    save,
    defaultProps: {
        ...config.defaultProps,
        layoutStyles: { ...defaultLayoutStyles },
    },
};

export { block, editor, config, save };
export default testimonialBlock;
```

### 7. Register the Block

Add to `blockLoader.js`:

```javascript
import testimonialBlock from './testimonial';

const modularBlocks = [
    // ... other blocks
    testimonialBlock,
];
```

Or register dynamically:

```javascript
import testimonialBlock from './blocks/testimonial';
import { blockRegistry } from '@/lara-builder';

blockRegistry.register(testimonialBlock);
```

---

## Troubleshooting

### Blocks Not Appearing

1. Check the `contexts` array includes your target context
2. Ensure the block is registered before the builder initializes
3. Check browser console for registration errors

### HTML Not Generating

1. Verify `save` has the correct context key (`page`, `email`)
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
