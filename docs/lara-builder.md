# LaraBuilder Documentation

LaraBuilder is an extensible visual content builder for creating email templates, web pages, and custom content. It provides a drag-and-drop interface with undo/redo support, context-aware blocks, and a WordPress-style hook system.

## Table of Contents

-   [Features](#features)
-   **[Creating Blocks (Quick Start)](#creating-blocks-quick-start)** ← Start here!
-   [Creating Blocks in Modules](#creating-blocks-in-modules)
-   [Architecture Overview](#architecture-overview)
-   [Block Structure](#block-structure)
-   [Server-Side vs Client-Side Rendering](#server-side-vs-client-side-rendering)
-   [Block Versioning & Migrations](#block-versioning--migrations)
-   [Import Aliases](#import-aliases)
-   [Contexts](#contexts)
-   [Registering Custom Blocks](#registering-custom-blocks)
-   [Using Hooks](#using-hooks)
-   [Output Adapters](#output-adapters)
-   [Styling Blocks](#styling-blocks)
-   [Keyboard Shortcuts](#keyboard-shortcuts)
-   [API Reference](#api-reference)
-   [Complete Examples](#complete-examples)
-   [Troubleshooting](#troubleshooting)

---

## Features

-   **Drag-and-drop** block-based editing
-   **Undo/Redo** with full history tracking (50 entries)
-   **Context-aware blocks** - Different blocks for email, page, campaign
-   **WordPress-style hooks** - Extend functionality via filters and actions
-   **Output adapters** - Email-safe HTML (tables) vs modern HTML5
-   **Third-party extensibility** - Modules can register custom blocks
-   **Auto-generated editors** - Define fields in JSON, get a form automatically
-   **Artisan command** - Scaffold new blocks with `php artisan make:block`
-   **Server-side rendering** - `render.php` for future-proof content that updates automatically
-   **Block versioning** - Semantic versioning with automatic migration support
-   **Migration system** - Transform old props to new formats without re-saving content

---

## Creating Blocks (Quick Start)

> **TL;DR**: Run `php artisan make:block my-block` and edit the generated files.

### Method 1: Use the Artisan Command (Recommended)

```bash
# Create a standard block (with custom editor)
php artisan make:block testimonial

# Create a simple block (auto-generated editor from fields)
php artisan make:block spacer --simple

# Create with specific category and icon
php artisan make:block pricing-card --category=Marketing --icon=mdi:currency-usd

# Create for specific contexts only
php artisan make:block newsletter-signup --contexts=email --contexts=campaign

# Create in a module (see "Creating Blocks in Modules" section)
php artisan make:block product-card --module=crm
```

This creates:

```
blocks/testimonial/
├── block.json    # Metadata + fields (auto-generates editor!)
├── block.jsx     # Canvas component
├── editor.jsx    # Properties panel (optional with --simple)
├── save.js       # HTML output generators
└── index.js      # 3-5 lines of code
```

### Method 2: Minimal Code Block

For the simplest blocks, you only need **3 files**:

**block.json** - Define metadata and fields:

```json
{
    "type": "notice",
    "label": "Notice",
    "category": "Content",
    "version": "1.0.0",
    "icon": "mdi:information",
    "contexts": ["email", "page"],
    "defaultProps": {
        "text": "Important notice",
        "type": "info",
        "color": "#3b82f6"
    },
    "fields": [
        {
            "name": "text",
            "type": "text",
            "label": "Notice Text",
            "section": "Content"
        },
        {
            "name": "type",
            "type": "select",
            "label": "Type",
            "section": "Content",
            "options": [
                { "value": "info", "label": "Info" },
                { "value": "warning", "label": "Warning" },
                { "value": "error", "label": "Error" },
                { "value": "success", "label": "Success" }
            ]
        },
        {
            "name": "color",
            "type": "color",
            "label": "Background Color",
            "section": "Style"
        }
    ]
}
```

**block.jsx** - Render on canvas:

```jsx
export default function NoticeBlock({ props, isSelected }) {
    const typeIcons = {
        info: "ℹ️",
        warning: "⚠️",
        error: "❌",
        success: "✅",
    };

    return (
        <div
            style={{
                backgroundColor: props.color || "#3b82f6",
                color: "white",
                padding: "16px",
                borderRadius: "8px",
                outline: isSelected ? "2px solid #635bff" : "none",
            }}
        >
            {typeIcons[props.type]} {props.text}
        </div>
    );
}
```

**index.js** - Just 3 lines:

```javascript
import { createBlockFromJson } from "@lara-builder/factory";
import config from "./block.json";
import block from "./block";

export default createBlockFromJson(config, { block });
// No editor.jsx needed! Auto-generated from fields in block.json
```

That's it! The editor form is **automatically generated** from the `fields` array.

### Method 3: Inline Block (Ultra Simple)

For quick prototypes or very simple blocks, define everything in one file:

```javascript
import { createBlock } from "@lara-builder/factory";

export default createBlock({
    type: "divider",
    label: "Divider",
    icon: "mdi:minus",
    category: "Layout",
    contexts: ["email", "page"],
    defaultProps: {
        color: "#e5e7eb",
        height: "1px",
        margin: "20px",
    },
    fields: [
        { name: "color", type: "color", label: "Line Color", section: "Style" },
        {
            name: "height",
            type: "select",
            label: "Thickness",
            section: "Style",
            options: [
                { value: "1px", label: "Thin (1px)" },
                { value: "2px", label: "Medium (2px)" },
                { value: "4px", label: "Thick (4px)" },
            ],
        },
        {
            name: "margin",
            type: "select",
            label: "Spacing",
            section: "Layout",
            options: [
                { value: "10px", label: "Small" },
                { value: "20px", label: "Medium" },
                { value: "40px", label: "Large" },
            ],
        },
    ],
    block: ({ props, isSelected }) => (
        <div
            style={{
                padding: `${props.margin} 0`,
                outline: isSelected ? "2px solid #635bff" : "none",
            }}
        >
            <hr
                style={{
                    border: "none",
                    height: props.height,
                    backgroundColor: props.color,
                }}
            />
        </div>
    ),
    save: {
        page: (props) =>
            `<hr style="border:none; height:${props.height}; background:${props.color}; margin:${props.margin} 0;" />`,
        email: (props) =>
            `<table width="100%" cellpadding="0" cellspacing="0"><tr><td style="padding:${props.margin} 0;"><div style="height:${props.height}; background:${props.color};"></div></td></tr></table>`,
    },
});
```

### Field Types Reference

Use these in your `block.json` fields array:

| Type       | Description                                        | Extra Options               |
| ---------- | -------------------------------------------------- | --------------------------- |
| `text`     | Single line text input                             | `placeholder`               |
| `textarea` | Multi-line text                                    | `rows` (default: 3)         |
| `number`   | Numeric input                                      | `min`, `max`, `step`        |
| `url`      | URL input with validation                          | `placeholder`               |
| `email`    | Email input                                        | `placeholder`               |
| `select`   | Dropdown select                                    | `options: [{value, label}]` |
| `color`    | Color picker with hex input                        | -                           |
| `checkbox` | Boolean checkbox                                   | -                           |
| `toggle`   | Switch toggle (styled)                             | -                           |
| `range`    | Slider                                             | `min`, `max`, `step`        |
| `image`    | Image URL with upload button                       | -                           |
| `align`    | Text alignment buttons (left/center/right/justify) | -                           |

**Field Options:**

```json
{
    "name": "fieldName",        // Required: Maps to props.fieldName
    "type": "text",             // Required: Field type from above
    "label": "Display Label",   // Required: Shown in editor (translatable)
    "section": "Content",       // Optional: Groups fields (default: "Content")
    "placeholder": "Hint...",   // Optional: Placeholder text
    "help": "Help text below",  // Optional: Description below field
    "required": true,           // Optional: Mark as required
    "min": 0,                   // Optional: For number/range
    "max": 100,                 // Optional: For number/range
    "step": 1,                  // Optional: For number/range
    "rows": 5,                  // Optional: For textarea
    "options": [...]            // Required for select: [{value, label}]
}
```

### Save Helpers (Email Output)

Use factory helpers for cleaner email HTML:

```javascript
import {
    emailTable,
    emailButton,
    emailSpacer,
    emailTextStyles,
} from "@lara-builder/factory";
import { buildBlockClasses, mergeBlockStyles } from "@lara-builder/utils";

// Page output (modern HTML)
export const page = (props) => {
    const classes = buildBlockClasses("my-block", props);
    const styles = mergeBlockStyles(props, `color: ${props.color}`);
    return `<div class="${classes}" style="${styles}">${props.text}</div>`;
};

// Email output (table-based)
export const email = (props) => {
    return emailTable(
        props,
        `
        <p style="${emailTextStyles(props)}">${props.text}</p>
    `,
        { padding: "20px" }
    );
};
```

**Available helpers:**
| Helper | Description |
|--------|-------------|
| `emailTable(props, content, options)` | Wrap content in email-safe table |
| `emailButton(props)` | Email-safe button with Outlook VML support |
| `emailImage(props)` | Responsive email image |
| `emailDivider(props)` | Horizontal line |
| `emailSpacer(height)` | Vertical space |
| `emailTextStyles(props)` | Generate inline text styles |

---

## Creating Blocks in Modules

Modules can have their own blocks that are automatically namespaced to avoid conflicts.

### Using the Artisan Command

```bash
# Create block in a module (case-insensitive module name)
php artisan make:block product-card --module=crm
php artisan make:block product-card --module=Crm   # Same result

# Create simple block in module
php artisan make:block deal-badge --module=crm --simple

# With all options
php artisan make:block contact-info --module=crm --category=Contacts --icon=mdi:account --contexts=email
```

### What Gets Created

```
modules/crm/resources/js/lara-builder-blocks/product-card/
├── block.json    # type: "crm-product-card" (auto-prefixed!)
├── block.jsx     # Uses @lara-builder/... imports
├── save.js
└── index.js
```

**Key differences for module blocks:**

1. **Block type is prefixed** with module name (e.g., `crm-product-card`) to avoid conflicts
2. **Category defaults** to the module name (e.g., "Crm")
3. **Imports use aliases** (`@lara-builder/...`) so they work from any location

### Generated block.json for Modules

```json
{
    "type": "crm-product-card",
    "label": "ProductCard",
    "category": "Crm",
    "version": "1.0.0",
    "icon": "mdi:shopping",
    "description": "A ProductCard block",
    "keywords": ["product-card"],
    "contexts": ["email", "page"],
    "supports": {
        "align": true,
        "spacing": true,
        "colors": true,
        "layout": true
    },
    "defaultProps": {
        "text": "Hello World"
    },
    "fields": [
        {
            "type": "text",
            "name": "text",
            "label": "Text Content",
            "section": "Content"
        }
    ]
}
```

### Generated block.jsx for Modules

```jsx
/**
 * ProductCard Block - Canvas Component (Module: crm)
 */

// Uses alias - works from any module location
import { applyLayoutStyles } from "@lara-builder/components/layout-styles/styleHelpers";

export default function ProductCardBlock({ props, isSelected }) {
    const containerStyle = applyLayoutStyles(
        {
            padding: "16px",
            outline: isSelected ? "2px solid #635bff" : "none",
            borderRadius: "4px",
        },
        props.layoutStyles
    );

    return <div style={containerStyle}>{props.text || "Click to edit..."}</div>;
}
```

### Registering Module Blocks

In your module's JavaScript entry point:

```javascript
// modules/crm/resources/js/entry.jsx (or similar)
import productCardBlock from "./lara-builder-blocks/product-card";
import dealBadgeBlock from "./lara-builder-blocks/deal-badge";
import { blockRegistry } from "@lara-builder";

// Register all CRM blocks
blockRegistry.register(productCardBlock);
blockRegistry.register(dealBadgeBlock);
```

Or register from PHP in your module's ServiceProvider:

```php
<?php

namespace Modules\Crm\Providers;

use App\Services\Builder\BuilderService;
use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // The JS block will be loaded via the entry.jsx
        // But you can also register PHP-only blocks here
        $builder = app(BuilderService::class);

        $builder->registerBlock([
            'type' => 'crm-dynamic-product',
            'label' => 'Dynamic Product',
            'category' => 'CRM',
            'icon' => 'mdi:database',
            'contexts' => ['email', 'campaign'],
            'defaultProps' => [
                'productId' => null,
            ],
        ]);
    }
}
```

### Complete Module Block Example

Here's a full example of a CRM Deal Card block:

**modules/crm/resources/js/lara-builder-blocks/deal-card/block.json:**

```json
{
    "type": "crm-deal-card",
    "label": "Deal Card",
    "category": "Crm",
    "version": "1.0.0",
    "icon": "mdi:handshake",
    "description": "Display a deal with status and value",
    "keywords": ["deal", "crm", "pipeline"],
    "contexts": ["email", "page"],
    "defaultProps": {
        "dealName": "New Deal",
        "value": "$10,000",
        "stage": "Negotiation",
        "probability": 75,
        "backgroundColor": "#f0fdf4",
        "accentColor": "#22c55e"
    },
    "fields": [
        {
            "name": "dealName",
            "type": "text",
            "label": "Deal Name",
            "section": "Content"
        },
        {
            "name": "value",
            "type": "text",
            "label": "Deal Value",
            "section": "Content"
        },
        {
            "name": "stage",
            "type": "select",
            "label": "Stage",
            "section": "Content",
            "options": [
                { "value": "Lead", "label": "Lead" },
                { "value": "Qualified", "label": "Qualified" },
                { "value": "Proposal", "label": "Proposal" },
                { "value": "Negotiation", "label": "Negotiation" },
                { "value": "Closed Won", "label": "Closed Won" },
                { "value": "Closed Lost", "label": "Closed Lost" }
            ]
        },
        {
            "name": "probability",
            "type": "range",
            "label": "Win Probability",
            "section": "Content",
            "min": 0,
            "max": 100,
            "step": 5
        },
        {
            "name": "backgroundColor",
            "type": "color",
            "label": "Background",
            "section": "Style"
        },
        {
            "name": "accentColor",
            "type": "color",
            "label": "Accent Color",
            "section": "Style"
        }
    ]
}
```

**modules/crm/resources/js/lara-builder-blocks/deal-card/block.jsx:**

```jsx
import { applyLayoutStyles } from "@lara-builder/components/layout-styles/styleHelpers";

export default function DealCardBlock({ props, isSelected }) {
    const containerStyle = applyLayoutStyles(
        {
            backgroundColor: props.backgroundColor,
            padding: "20px",
            borderRadius: "8px",
            borderLeft: `4px solid ${props.accentColor}`,
            outline: isSelected ? "2px solid #635bff" : "none",
        },
        props.layoutStyles
    );

    return (
        <div style={containerStyle}>
            <div
                style={{
                    display: "flex",
                    justifyContent: "space-between",
                    marginBottom: "12px",
                }}
            >
                <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "600" }}>
                    {props.dealName}
                </h3>
                <span
                    style={{
                        backgroundColor: props.accentColor,
                        color: "white",
                        padding: "4px 12px",
                        borderRadius: "12px",
                        fontSize: "12px",
                        fontWeight: "500",
                    }}
                >
                    {props.stage}
                </span>
            </div>
            <div
                style={{
                    fontSize: "24px",
                    fontWeight: "700",
                    color: props.accentColor,
                }}
            >
                {props.value}
            </div>
            <div style={{ marginTop: "12px" }}>
                <div
                    style={{
                        height: "8px",
                        backgroundColor: "#e5e7eb",
                        borderRadius: "4px",
                        overflow: "hidden",
                    }}
                >
                    <div
                        style={{
                            width: `${props.probability}%`,
                            height: "100%",
                            backgroundColor: props.accentColor,
                        }}
                    />
                </div>
                <span style={{ fontSize: "12px", color: "#6b7280" }}>
                    {props.probability}% probability
                </span>
            </div>
        </div>
    );
}
```

**modules/crm/resources/js/lara-builder-blocks/deal-card/save.js:**

```javascript
import { emailTable } from "@lara-builder/factory";
import { buildBlockClasses, mergeBlockStyles } from "@lara-builder/utils";

export const page = (props) => {
    const classes = buildBlockClasses("crm-deal-card", props);
    const styles = mergeBlockStyles(
        props,
        `
        background: ${props.backgroundColor};
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid ${props.accentColor};
    `
    );

    return `
        <div class="${classes}" style="${styles}">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <h3 style="margin:0; font-size:18px; font-weight:600;">${props.dealName}</h3>
                <span style="background:${props.accentColor}; color:white; padding:4px 12px; border-radius:12px; font-size:12px;">${props.stage}</span>
            </div>
            <div style="font-size:24px; font-weight:700; color:${props.accentColor};">${props.value}</div>
            <div style="margin-top:12px;">
                <div style="height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden;">
                    <div style="width:${props.probability}%; height:100%; background:${props.accentColor};"></div>
                </div>
                <span style="font-size:12px; color:#6b7280;">${props.probability}% probability</span>
            </div>
        </div>
    `;
};

export const email = (props) => {
    return emailTable(
        props,
        `
        <table width="100%" cellpadding="0" cellspacing="0" style="background:${props.backgroundColor}; border-left:4px solid ${props.accentColor};">
            <tr>
                <td style="padding:20px;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="font-size:18px; font-weight:600;">${props.dealName}</td>
                            <td align="right">
                                <span style="background:${props.accentColor}; color:white; padding:4px 12px; border-radius:12px; font-size:12px;">${props.stage}</span>
                            </td>
                        </tr>
                    </table>
                    <p style="font-size:24px; font-weight:700; color:${props.accentColor}; margin:12px 0;">${props.value}</p>
                    <p style="font-size:12px; color:#6b7280; margin:0;">${props.probability}% probability</p>
                </td>
            </tr>
        </table>
    `
    );
};

export default { page, email };
```

**modules/crm/resources/js/lara-builder-blocks/deal-card/index.js:**

```javascript
/**
 * Deal Card Block (Module: crm)
 */

import { createBlockFromJson } from "@lara-builder/factory";
import config from "./block.json";
import block from "./block";
import save from "./save";

// No editor needed - uses auto-generated from fields
export default createBlockFromJson(config, { block, save });
```

---

## Architecture Overview

```
resources/js/lara-builder/
├── core/                    # Core React components and state
│   ├── LaraBuilder.jsx      # Main builder component
│   ├── BuilderContext.jsx   # React context provider
│   └── BuilderReducer.js    # State with undo/redo
├── factory/                 # Block creation utilities (NEW)
│   ├── createBlock.jsx      # Block factory functions
│   ├── EditorField.jsx      # Auto-generated editor fields
│   ├── saveHelpers.js       # Email output helpers
│   └── index.js             # Exports
├── hooks-system/            # WordPress-style hooks
│   ├── LaraHooks.js         # Filter/action system
│   └── HookNames.js         # Hook constants
├── registry/                # Block and adapter registries
│   ├── BlockRegistry.js     # Block registration
│   └── OutputAdapterRegistry.js
├── adapters/                # HTML output generators
│   ├── EmailAdapter.js      # Email-safe HTML
│   └── WebAdapter.js        # Modern HTML5
├── blocks/                  # Core blocks
│   └── heading/             # Example block with server rendering
│       ├── index.js         # 3 lines using factory
│       ├── block.json       # Metadata + fields + version
│       ├── block.jsx        # Canvas component (client-side)
│       ├── save.js          # Placeholder for page, full HTML for email
│       ├── render.php       # Server-side HTML generation
│       └── migrations/      # Version migrations (optional)
│           └── v1_0_0_to_v1_1_0.php
├── components/              # Shared UI components
├── utils/                   # Shared utilities
├── i18n/                    # Translation system
└── index.js                 # Main exports

app/Services/Builder/        # PHP Backend Services
├── BuilderService.php       # Block registration
├── BlockRenderer.php        # Processes placeholders → HTML
└── BlockMigrator.php        # Handles version migrations

modules/{module}/resources/js/
└── lara-builder-blocks/     # Module-specific blocks
    └── my-block/
        ├── index.js
        ├── block.json       # Includes version field
        ├── block.jsx
        ├── save.js
        └── render.php       # Optional server rendering
```

---

## Server-Side vs Client-Side Rendering

LaraBuilder uses a **hybrid rendering architecture** to ensure maximum flexibility and future-proofing:

### The Problem

When block output is saved as static HTML, any future changes to block structure or styling require re-saving all content that uses that block. This creates maintenance challenges:

-   Bug fixes don't apply to existing content
-   Style improvements require content migration
-   New features can't be added without breaking old content

### The Solution: Placeholder Pattern

LaraBuilder solves this with **server-side rendering via `render.php`**:

```
┌─────────────────────────────────────────────────────────────────────┐
│                        CONTENT FLOW                                  │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  BUILDER (React)                                                     │
│      │                                                               │
│      ▼                                                               │
│  save.js (page context)                                             │
│      │                                                               │
│      ▼                                                               │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  <div data-lara-block="heading"                              │   │
│  │       data-props='{"text":"Hello","level":"h2"}'></div>      │   │
│  └──────────────────────────────────────────────────────────────┘   │
│      │                                                               │
│      │  Stored in database as placeholder                           │
│      ▼                                                               │
│  BlockRenderer::processContent()                                    │
│      │                                                               │
│      ▼                                                               │
│  render.php (server-side)                                           │
│      │                                                               │
│      ▼                                                               │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  <h2 class="lb-block lb-heading" style="...">Hello</h2>      │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  Final HTML displayed to user                                        │
└─────────────────────────────────────────────────────────────────────┘
```

### File Responsibilities

| File              | When Used      | Responsibility                  |
| ----------------- | -------------- | ------------------------------- |
| `block.jsx`       | Builder canvas | Visual editing experience       |
| `save.js` (page)  | Content save   | Outputs placeholder with props  |
| `save.js` (email) | Email export   | Full HTML (no server available) |
| `render.php`      | Page display   | Server-side HTML generation     |

### How It Works

**1. save.js outputs a placeholder (page context):**

```javascript
export const page = (props, options = {}) => {
    const serverProps = {
        text: props.text || "",
        level: props.level || "h2",
        // ... all props needed for rendering
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, "&#39;");

    return `<div data-lara-block="heading" data-props='${propsJson}'></div>`;
};
```

**2. render.php generates the final HTML:**

```php
<?php
// blocks/heading/render.php

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $text = $props['text'] ?? '';
    $level = $props['level'] ?? 'h2';
    $tag = in_array($level, ['h1','h2','h3','h4','h5','h6']) ? $level : 'h2';

    // Build styles, classes, etc.
    $classes = 'lb-block lb-heading';
    $styles = buildStyles($props); // your helper function

    return sprintf(
        '<%s class="%s" style="%s">%s</%s>',
        $tag, e($classes), e($styles), e($text), $tag
    );
};
```

**3. BlockRenderer processes content on display:**

```php
// In your Post model or controller
$html = $post->renderContent(); // Processes all placeholders
```

### Email Context Exception

Email clients can't make server calls, so `save.js` must output complete HTML for email context:

```javascript
export const email = (props, options = {}) => {
    // Full HTML - no placeholder pattern
    return `
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="font-size: 24px; font-weight: bold;">
                    ${props.text || ""}
                </td>
            </tr>
        </table>
    `;
};
```

### Benefits of Server-Side Rendering

| Benefit                           | Description                            |
| --------------------------------- | -------------------------------------- |
| **Bug fixes apply automatically** | Fix render.php, all content is fixed   |
| **Future-proof**                  | Add features without migrating content |
| **Security**                      | Server-side XSS prevention             |
| **Performance**                   | CDN/caching optimizations              |
| **Consistency**                   | Single source of truth for rendering   |

### Auto-Discovery

The `BlockRenderer` automatically discovers `render.php` files:

```php
// Checks: resources/js/lara-builder/blocks/{type}/render.php
$renderPath = resource_path("js/lara-builder/blocks/{$blockType}/render.php");
```

No registration required - just create the file and it works.

### Checking Block Status

Use the artisan command to see which blocks have server-side rendering:

```bash
php artisan builder:check-versions
```

Output:

```
LaraBuilder Block Status

Found 20 blocks: 20 versioned, 8 with render.php, 8 using placeholder pattern

┌─────────────┬─────────┬────────────┬─────────┬────────────┐
│ Block Type  │ Version │ render.php │ save.js │ Migrations │
├─────────────┼─────────┼────────────┼─────────┼────────────┤
│ button      │ 1.0.0   │ ✓          │ →PHP    │ 0          │
│ heading     │ 1.0.0   │ ✓          │ →PHP    │ 0          │
│ image       │ 1.0.0   │ ✓          │ →PHP    │ 0          │
│ spacer      │ 1.0.0   │ ✗          │ ✓       │ 0          │
│ ...         │         │            │         │            │
└─────────────┴─────────┴────────────┴─────────┴────────────┘

Legend:
  ✓     = File exists
  ✗     = File missing
  →PHP  = save.js outputs placeholder for server rendering
```

---

## Block Versioning & Migrations

LaraBuilder includes a versioning system to handle block schema changes over time without breaking existing content.

### Why Versioning?

As your application evolves, block properties may change:

-   Rename properties (`color` → `backgroundColor`)
-   Change data structures (string → object)
-   Add required properties with defaults
-   Remove deprecated properties

Without versioning, old content would break when blocks change.

### Adding Version to block.json

Every block should have a `version` field using semantic versioning:

```json
{
    "type": "heading",
    "version": "1.0.0",
    "label": "Heading",
    ...
}
```

**Version Format: MAJOR.MINOR.PATCH**

-   **MAJOR**: Breaking changes (props renamed/restructured)
-   **MINOR**: New features (new optional props)
-   **PATCH**: Bug fixes (no prop changes)

### How Migrations Work

When you change a block's props structure:

1. **Bump the version** in `block.json`
2. **Create a migration file** in `blocks/{type}/migrations/`
3. **Migration runs automatically** when old content is rendered

```
blocks/heading/
├── block.json          # version: "1.1.0"
├── render.php
├── save.js
└── migrations/
    └── v1_0_0_to_v1_1_0.php   # Transforms old props to new
```

### Creating a Migration

**Migration file naming:** `v{from}_to_v{to}.php`

-   `v1_0_0_to_v1_1_0.php`
-   `v1_1_0_to_v2_0_0.php`

**Migration file structure:**

```php
<?php
// blocks/heading/migrations/v1_0_0_to_v1_1_0.php

/**
 * Block Migration: heading v1.0.0 to v1.1.0
 *
 * Changes in this version:
 * - Renamed 'color' to 'textColor'
 * - Added 'backgroundColor' property
 */

return function (array $props): array {
    // Rename property
    if (isset($props['color'])) {
        $props['textColor'] = $props['color'];
        unset($props['color']);
    }

    // Add backgroundColor with default
    $props['backgroundColor'] ??= 'transparent';

    return $props;
};
```

### BlockMigrator Service

The `BlockMigrator` service handles version checking and migration:

```php
use App\Services\Builder\BlockMigrator;

$migrator = app(BlockMigrator::class);

// Check if a block needs migration
$needsMigration = $migrator->needsMigration($block);

// Migrate a single block
$migratedBlock = $migrator->migrateBlock($block);

// Migrate all blocks (handles nested blocks in columns)
$migratedBlocks = $migrator->migrateBlocks($blocks);

// Get current version for a block type
$version = $migrator->getCurrentVersion('heading');

// Create a migration file template
$path = $migrator->createMigrationTemplate('heading', '1.0.0', '1.1.0');
```

### Migration Path Resolution

The migrator automatically finds the shortest path between versions:

```
Stored: v1.0.0 → Current: v1.2.0

Available migrations:
- v1_0_0_to_v1_1_0.php
- v1_1_0_to_v1_2_0.php

Migration path: v1.0.0 → v1.1.0 → v1.2.0
```

### When Migrations Run

Migrations are applied when:

1. `BlockMigrator::migrateBlock()` is called explicitly
2. Content is processed (if integrated with your render pipeline)

**Recommended integration:**

```php
// In BlockRenderer or your render pipeline
public function processContent(string $content, string $context = 'page'): string
{
    // Parse blocks from design_json
    $blocks = $this->parseBlocks($content);

    // Migrate if needed
    $blocks = app(BlockMigrator::class)->migrateBlocks($blocks);

    // Continue with rendering...
}
```

### Best Practices

1. **Always version from the start** - Add `"version": "1.0.0"` to every new block
2. **Document changes** - Comment what changed in each migration
3. **Test migrations** - Write tests for your migration functions
4. **Keep migrations simple** - One migration per version bump
5. **Don't skip versions** - Migrate stepwise (1.0→1.1→1.2, not 1.0→1.2)

### Example: Complete Version Upgrade

**Scenario:** Heading block needs to support background colors

**1. Update block.json:**

```json
{
    "type": "heading",
    "version": "1.1.0", // Bumped from 1.0.0
    "defaultProps": {
        "text": "",
        "level": "h2",
        "textColor": "#000000", // Renamed from 'color'
        "backgroundColor": "transparent" // New property
    }
}
```

**2. Create migration file:**

```php
<?php
// blocks/heading/migrations/v1_0_0_to_v1_1_0.php

return function (array $props): array {
    // Rename color → textColor
    if (isset($props['color'])) {
        $props['textColor'] = $props['color'];
        unset($props['color']);
    }

    // Add backgroundColor with default
    $props['backgroundColor'] ??= 'transparent';

    return $props;
};
```

**3. Update render.php:**

```php
return function (array $props, string $context, ?string $blockId): string {
    $textColor = $props['textColor'] ?? '#000000';  // Updated prop name
    $backgroundColor = $props['backgroundColor'] ?? 'transparent';  // New prop

    // ... render with new props
};
```

**4. Update save.js:**

```javascript
export const page = (props) => {
    const serverProps = {
        text: props.text || "",
        level: props.level || "h2",
        textColor: props.textColor || "#000000", // Updated prop name
        backgroundColor: props.backgroundColor || "transparent", // New prop
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, "&#39;");
    return `<div data-lara-block="heading" data-props='${propsJson}'></div>`;
};
```

Now old content with `"color": "#ff0000"` will automatically migrate to `"textColor": "#ff0000"` when rendered.

---

## Import Aliases

LaraBuilder provides Vite path aliases for cleaner imports:

| Alias                      | Path                        | Description              |
| -------------------------- | --------------------------- | ------------------------ |
| `@lara-builder`            | `resources/js/lara-builder` | Main directory           |
| `@lara-builder/factory`    | `.../factory`               | Block creation utilities |
| `@lara-builder/components` | `.../components`            | Shared UI components     |
| `@lara-builder/utils`      | `.../utils`                 | Utility functions        |
| `@lara-builder/blocks`     | `.../blocks`                | Core blocks              |
| `@lara-builder/i18n`       | `.../i18n`                  | Translation system       |

### Usage Examples

```javascript
// In any block (core or module)
import { createBlockFromJson, emailTable } from "@lara-builder/factory";
import { buildBlockClasses, mergeBlockStyles } from "@lara-builder/utils";
import { applyLayoutStyles } from "@lara-builder/components/layout-styles/styleHelpers";
import { blockRegistry, LaraHooks } from "@lara-builder";
import { __ } from "@lara-builder/i18n";
```

---

## Contexts

LaraBuilder supports multiple contexts, each with its own block set and output format:

| Context    | Adapter      | Description                                   |
| ---------- | ------------ | --------------------------------------------- |
| `email`    | EmailAdapter | Email-safe HTML with tables and inline styles |
| `page`     | WebAdapter   | Modern HTML5 with CSS classes                 |
| `campaign` | EmailAdapter | Same as email, with personalization support   |

### Limiting Blocks to Specific Contexts

In your `block.json`:

```json
{
    "contexts": ["email", "campaign"]
}
```

Or with the artisan command:

```bash
php artisan make:block newsletter-form --contexts=page
php artisan make:block email-header --contexts=email --contexts=campaign
```

---

## Registering Custom Blocks

### From JavaScript (Recommended)

```javascript
import { blockRegistry } from "@lara-builder";
import myBlock from "./my-block";

// Register the block
blockRegistry.register(myBlock);

// Or register multiple
[block1, block2, block3].forEach((block) => blockRegistry.register(block));
```

### From PHP (Metadata Only)

```php
$builder = app(BuilderService::class);

$builder->registerBlock([
    'type' => 'my-block',
    'label' => 'My Block',
    'category' => 'Custom',
    'icon' => 'mdi:star',
    'contexts' => ['email', 'page'],
    'defaultProps' => ['text' => 'Hello'],
]);
```

---

## Using Hooks

### JavaScript Hooks

```javascript
import { LaraHooks, BuilderHooks } from "@lara-builder";

// Filter blocks before display
LaraHooks.addFilter(
    BuilderHooks.FILTER_BLOCKS,
    (blocks, context) => {
        if (context === "email") {
            return [...blocks, myEmailBlock];
        }
        return blocks;
    },
    10
);

// React to block events
LaraHooks.addAction(BuilderHooks.ACTION_BLOCK_ADDED, (block, index) => {
    console.log(`Added ${block.type} at position ${index}`);
});
```

### PHP Hooks

```php
use App\Services\Builder\BuilderService;
use App\Enums\Builder\BuilderFilterHook;

$builder = app(BuilderService::class);

$builder->addFilter(BuilderFilterHook::BUILDER_BLOCKS_EMAIL, function ($blocks) {
    $blocks['my-block'] = [...];
    return $blocks;
});
```

---

## Keyboard Shortcuts

| Shortcut               | Action                |
| ---------------------- | --------------------- |
| `Ctrl/Cmd + Z`         | Undo                  |
| `Ctrl/Cmd + Shift + Z` | Redo                  |
| `Ctrl/Cmd + Y`         | Redo (alternate)      |
| `Delete` / `Backspace` | Delete selected block |

---

## Complete Examples

### Example 1: Simple Spacer Block (Auto-Generated Editor)

This is the simplest possible block - just 3 files, ~30 lines total:

**blocks/spacer/block.json:**

```json
{
    "type": "spacer",
    "label": "Spacer",
    "category": "Layout",
    "version": "1.0.0",
    "icon": "mdi:arrow-expand-vertical",
    "contexts": ["email", "page", "campaign"],
    "defaultProps": { "height": "40px" },
    "fields": [
        {
            "name": "height",
            "type": "select",
            "label": "Height",
            "section": "Size",
            "options": [
                { "value": "10px", "label": "10px" },
                { "value": "20px", "label": "20px" },
                { "value": "40px", "label": "40px" },
                { "value": "60px", "label": "60px" },
                { "value": "80px", "label": "80px" }
            ]
        }
    ]
}
```

**blocks/spacer/block.jsx:**

```jsx
export default function SpacerBlock({ props, isSelected }) {
    return (
        <div
            style={{
                height: props.height || "40px",
                backgroundColor: isSelected ? "#f3f4f6" : "transparent",
                border: isSelected
                    ? "1px dashed #9ca3af"
                    : "1px dashed transparent",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
            }}
        >
            {isSelected && (
                <span style={{ color: "#9ca3af", fontSize: "12px" }}>
                    {props.height}
                </span>
            )}
        </div>
    );
}
```

**blocks/spacer/save.js:**

```javascript
import { emailSpacer } from "@lara-builder/factory";
import { buildBlockClasses, mergeBlockStyles } from "@lara-builder/utils";

export const page = (props) => {
    const classes = buildBlockClasses("spacer", props);
    const styles = mergeBlockStyles(props, `height: ${props.height || "20px"}`);
    return `<div class="${classes}" style="${styles}"></div>`;
};

export const email = (props) => emailSpacer(props.height || "20px");

export default { page, email };
```

**blocks/spacer/index.js:**

```javascript
import { createBlockFromJson } from "@lara-builder/factory";
import config from "./block.json";
import block from "./block";
import save from "./save";

export default createBlockFromJson(config, { block, save });
```

### Example 2: Button Block (Custom Editor)

When you need more control over the editor UI:

**blocks/button/index.js:**

```javascript
import { createBlockFromJson } from "@lara-builder/factory";
import config from "./block.json";
import block from "./block";
import editor from "./editor"; // Custom editor
import save from "./save";

export default createBlockFromJson(config, { block, editor, save });
```

**blocks/button/editor.jsx:**

```jsx
import { EditorSection, EditorField } from "@lara-builder/factory";

export default function ButtonEditor({ props, onUpdate }) {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            <EditorSection title="Content">
                <EditorField
                    type="text"
                    name="text"
                    label="Button Text"
                    value={props.text}
                    onChange={(v) => handleChange("text", v)}
                />
                <EditorField
                    type="url"
                    name="link"
                    label="Link URL"
                    value={props.link}
                    onChange={(v) => handleChange("link", v)}
                    placeholder="https://..."
                />
            </EditorSection>

            <EditorSection title="Style">
                <EditorField
                    type="color"
                    name="backgroundColor"
                    label="Background"
                    value={props.backgroundColor}
                    onChange={(v) => handleChange("backgroundColor", v)}
                />
                <EditorField
                    type="color"
                    name="textColor"
                    label="Text Color"
                    value={props.textColor}
                    onChange={(v) => handleChange("textColor", v)}
                />
                <EditorField
                    type="align"
                    name="align"
                    label="Alignment"
                    value={props.align}
                    onChange={(v) => handleChange("align", v)}
                />
            </EditorSection>
        </div>
    );
}
```

---

## Troubleshooting

### Block Not Appearing

1. Check `contexts` includes your target context
2. Verify block is registered before builder initializes
3. Check browser console for errors
4. Run `npm run build` to rebuild assets

### Module Block Not Working

1. Ensure `@lara-builder/...` aliases are in vite.config.js
2. Check block type is prefixed (e.g., `crm-product-card`)
3. Verify the block is registered in module's entry file

### Auto-Generated Editor Not Showing

1. Ensure `fields` array is in block.json
2. Don't pass `editor` to `createBlockFromJson` if using auto-generated
3. Check field types are valid (see Field Types Reference)

### HTML Not Generating

1. Verify `save.js` exports `page` and/or `email` functions
2. Check props have default values
3. Test with `console.log(save.email(props))` in browser

### Translation Not Working

1. Use `label` in fields (translated at runtime)
2. Import and use `__()` function from `@lara-builder/i18n`
3. Add translations to your Laravel lang files
