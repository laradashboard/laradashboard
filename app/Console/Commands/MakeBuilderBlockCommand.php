<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeBuilderBlockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:block
                            {name : The name of the block (e.g., testimonial, pricing-card)}
                            {--category=Content : The block category}
                            {--icon=lucide:box : The Iconify icon name}
                            {--contexts=* : Allowed contexts (email, page, campaign)}
                            {--simple : Create a simple block without editor}
                            {--module= : Create block in a module instead of core (e.g., crm, Crm)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new LaraBuilder block with all required files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $slug = Str::slug($name);
        $pascalName = Str::studly($name);
        $category = $this->option('category');
        $icon = $this->option('icon');
        $contexts = $this->option('contexts') ?: ['email', 'page'];
        $simple = $this->option('simple');
        $module = $this->option('module');

        // Determine the base path and module info
        $isModule = ! empty($module);
        $moduleName = $isModule ? $this->findModuleName($module) : null;

        if ($isModule && ! $moduleName) {
            $this->error("Module '{$module}' not found in modules/ directory.");
            $this->line('Available modules:');
            foreach ($this->getAvailableModules() as $mod) {
                $this->line("  - {$mod}");
            }

            return self::FAILURE;
        }

        if ($isModule) {
            // Module blocks go in: modules/{module}/resources/js/lara-builder-blocks/{slug}/
            $basePath = base_path("modules/{$moduleName}/resources/js/lara-builder-blocks/{$slug}");
            $blocksDir = base_path("modules/{$moduleName}/resources/js/lara-builder-blocks");
        } else {
            // Core blocks go in: resources/js/lara-builder/blocks/{slug}/
            $basePath = resource_path("js/lara-builder/blocks/{$slug}");
            $blocksDir = resource_path('js/lara-builder/blocks');
        }

        // Check if block already exists
        if (is_dir($basePath)) {
            $this->error("Block '{$slug}' already exists at: {$basePath}");

            return self::FAILURE;
        }

        // Create the blocks directory if it doesn't exist (for modules)
        if ($isModule && ! is_dir($blocksDir)) {
            if (! mkdir($blocksDir, 0755, true)) {
                $this->error("Failed to create blocks directory: {$blocksDir}");

                return self::FAILURE;
            }
        }

        // Create the block directory
        if (! mkdir($basePath, 0755, true)) {
            $this->error("Failed to create directory: {$basePath}");

            return self::FAILURE;
        }

        // Set category to module name if creating in a module
        if ($isModule && $category === 'Content') {
            $category = Str::studly($moduleName);
        }

        // Generate files
        $this->createBlockJson($basePath, $slug, $pascalName, $category, $icon, $contexts, $isModule, $moduleName);
        $this->createBlockJsx($basePath, $pascalName, $simple, $isModule);

        if (! $simple) {
            $this->createEditorJsx($basePath, $pascalName);
        }

        $this->createSaveJs($basePath, $slug);
        $this->createIndexJs($basePath, $pascalName, $simple, $isModule, $moduleName);

        $this->newLine();
        $this->info("Block '{$slug}' created successfully!");
        $this->newLine();

        $this->comment('Files created:');
        $this->line("  - {$basePath}/block.json");
        $this->line("  - {$basePath}/block.jsx");
        if (! $simple) {
            $this->line("  - {$basePath}/editor.jsx");
        }
        $this->line("  - {$basePath}/save.js");
        $this->line("  - {$basePath}/index.js");

        $this->newLine();
        $this->comment('Next steps:');
        $this->line('  1. Edit block.json to add your fields and defaultProps');
        $this->line('  2. Implement the block canvas component in block.jsx');
        if (! $simple) {
            $this->line('  3. Customize the editor panel in editor.jsx (or use auto-generated)');
        }
        $this->line('  4. Add HTML generators in save.js for page/email output');

        if ($isModule) {
            $this->line("  5. Register the block in your module's entry.jsx or provider:");
            $this->newLine();
            $this->line("     // In modules/{$moduleName}/resources/js/entry.jsx or similar:");
            $this->line("     import {$slug}Block from './lara-builder-blocks/{$slug}';");
            $this->line("     import { blockRegistry } from '@lara-builder';");
            $this->line('');
            $this->line("     blockRegistry.register({$slug}Block);");
        } else {
            $this->line('  5. Import and register in blockLoader.js:');
            $this->newLine();
            $this->line("     import {$slug}Block from './{$slug}';");
            $this->line("     // Add to modularBlocks array: {$slug}Block,");
        }

        return self::SUCCESS;
    }

    /**
     * Find the actual module directory name (case-insensitive search)
     */
    private function findModuleName(string $module): ?string
    {
        $modulesPath = base_path('modules');

        if (! is_dir($modulesPath)) {
            return null;
        }

        // Try exact match first
        if (is_dir("{$modulesPath}/{$module}")) {
            return $module;
        }

        // Try case-insensitive search
        $dirs = scandir($modulesPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            if (strtolower($dir) === strtolower($module)) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * Get list of available modules
     */
    private function getAvailableModules(): array
    {
        $modulesPath = base_path('modules');
        $modules = [];

        if (is_dir($modulesPath)) {
            $dirs = scandir($modulesPath);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..' || ! is_dir("{$modulesPath}/{$dir}")) {
                    continue;
                }
                // Skip hidden and system files
                if (str_starts_with($dir, '.') || str_starts_with($dir, '_')) {
                    continue;
                }
                $modules[] = $dir;
            }
        }

        return $modules;
    }

    /**
     * Create block.json metadata file
     */
    private function createBlockJson(string $path, string $slug, string $name, string $category, string $icon, array $contexts, bool $isModule, ?string $moduleName): void
    {
        $contextsJson = json_encode($contexts);

        // Add module prefix to type to avoid conflicts
        $type = $isModule ? strtolower($moduleName).'-'.$slug : $slug;

        $content = <<<JSON
{
    "type": "{$type}",
    "label": "{$name}",
    "category": "{$category}",
    "icon": "{$icon}",
    "description": "A {$name} block",
    "keywords": ["{$slug}"],
    "contexts": {$contextsJson},
    "supports": {
        "align": true,
        "spacing": true,
        "colors": true,
        "layout": true,
        "html": true,
        "duplicate": true,
        "remove": true
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
JSON;

        file_put_contents("{$path}/block.json", $content);
    }

    /**
     * Create block.jsx canvas component
     */
    private function createBlockJsx(string $path, string $name, bool $simple, bool $isModule): void
    {
        // For modules, import from @lara-builder instead of relative path
        $layoutStylesImport = $isModule
            ? "import { applyLayoutStyles } from '@lara-builder/components/layout-styles/styleHelpers';"
            : "import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';";

        if ($simple) {
            $content = <<<JSX
/**
 * {$name} Block - Canvas Component
 *
 * Simple block - renders content without inline editing.
 */

{$layoutStylesImport}

export default function {$name}Block({ props, isSelected }) {
    const containerStyle = applyLayoutStyles(
        {
            padding: '16px',
            outline: isSelected ? '2px solid #635bff' : 'none',
            borderRadius: '4px',
        },
        props.layoutStyles
    );

    return (
        <div style={containerStyle}>
            {props.text || 'Click to edit...'}
        </div>
    );
}
JSX;
        } else {
            $content = <<<JSX
/**
 * {$name} Block - Canvas Component
 *
 * Renders the block in the builder canvas.
 * Supports inline editing when selected.
 */

import { useRef, useEffect, useCallback } from 'react';
{$layoutStylesImport}

export default function {$name}Block({ props, isSelected, onUpdate, onRegisterTextFormat }) {
    const editorRef = useRef(null);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    // Handle text input
    const handleInput = useCallback(() => {
        if (editorRef.current) {
            const newText = editorRef.current.innerHTML;
            onUpdateRef.current({ ...propsRef.current, text: newText });
        }
    }, []);

    // Initialize editor when selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            editorRef.current.innerHTML = props.text || '';
            editorRef.current.focus();
        }
    }, [isSelected]);

    // Register toolbar
    useEffect(() => {
        if (onRegisterTextFormat) {
            onRegisterTextFormat(
                isSelected
                    ? {
                          editorRef,
                          isContentEditable: true,
                          align: props.align || 'left',
                          onAlignChange: (align) =>
                              onUpdateRef.current({ ...propsRef.current, align }),
                      }
                    : null
            );
        }
    }, [isSelected, onRegisterTextFormat, props.align]);

    // Styles
    const containerStyle = applyLayoutStyles(
        {
            padding: '16px',
            borderRadius: '4px',
        },
        props.layoutStyles
    );

    const contentStyle = {
        color: props.color || '#333333',
        fontSize: props.fontSize || '16px',
        textAlign: props.align || 'left',
        lineHeight: '1.6',
    };

    if (isSelected) {
        return (
            <div style={containerStyle} data-text-editing="true">
                <div
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={handleInput}
                    onBlur={handleInput}
                    style={{
                        ...contentStyle,
                        border: '2px solid #635bff',
                        borderRadius: '4px',
                        padding: '8px',
                        outline: 'none',
                        minHeight: '40px',
                    }}
                />
            </div>
        );
    }

    return (
        <div style={containerStyle}>
            <div
                style={contentStyle}
                dangerouslySetInnerHTML={{ __html: props.text || 'Click to edit...' }}
            />
        </div>
    );
}
JSX;
        }

        file_put_contents("{$path}/block.jsx", $content);
    }

    /**
     * Create editor.jsx properties panel component
     */
    private function createEditorJsx(string $path, string $name): void
    {
        $content = <<<JSX
/**
 * {$name} Block - Property Editor
 *
 * Option 1: Use auto-generated editor from fields in block.json
 * Option 2: Custom editor component (this file)
 *
 * To use auto-generated editor, simply don't export an editor
 * from index.js - the system will use fields from block.json.
 */

import { EditorSection, EditorField } from '@lara-builder/factory';

export default function {$name}BlockEditor({ props, onUpdate, onImageUpload }) {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            <EditorSection title="Content">
                <EditorField
                    type="text"
                    name="text"
                    label="Text Content"
                    value={props.text}
                    onChange={(value) => handleChange('text', value)}
                />
            </EditorSection>

            <EditorSection title="Style">
                <EditorField
                    type="color"
                    name="color"
                    label="Text Color"
                    value={props.color}
                    onChange={(value) => handleChange('color', value)}
                />

                <EditorField
                    type="select"
                    name="fontSize"
                    label="Font Size"
                    value={props.fontSize}
                    onChange={(value) => handleChange('fontSize', value)}
                    options={[
                        { value: '14px', label: 'Small (14px)' },
                        { value: '16px', label: 'Medium (16px)' },
                        { value: '18px', label: 'Large (18px)' },
                        { value: '20px', label: 'X-Large (20px)' },
                    ]}
                />

                <EditorField
                    type="align"
                    name="align"
                    label="Alignment"
                    value={props.align}
                    onChange={(value) => handleChange('align', value)}
                />
            </EditorSection>
        </div>
    );
}
JSX;

        file_put_contents("{$path}/editor.jsx", $content);
    }

    /**
     * Create save.js HTML generators
     */
    private function createSaveJs(string $path, string $slug): void
    {
        $content = <<<JS
/**
 * {$slug} Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 * Uses helper functions from @lara-builder/factory for cleaner code.
 */

import { emailTable, emailTextStyles } from '@lara-builder/factory';
import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = '{$slug}';
    const blockClasses = buildBlockClasses(type, props);

    const styles = [
        `color: \${props.color || '#333333'}`,
        `font-size: \${props.fontSize || '16px'}`,
        `text-align: \${props.align || 'left'}`,
        'line-height: 1.6',
    ];

    const mergedStyles = mergeBlockStyles(props, styles.join('; '));

    return `<div class="\${blockClasses}" style="\${mergedStyles}">\${props.text || ''}</div>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const textStyles = emailTextStyles(props);

    return emailTable(props, `
        <div style="\${textStyles}">
            \${props.text || ''}
        </div>
    `, { padding: '16px' });
};

export default { page, email };
JS;

        file_put_contents("{$path}/save.js", $content);
    }

    /**
     * Create index.js entry point
     */
    private function createIndexJs(string $path, string $name, bool $simple, bool $isModule, ?string $moduleName): void
    {
        $moduleComment = $isModule ? " (Module: {$moduleName})" : '';

        if ($simple) {
            $content = <<<JS
/**
 * {$name} Block{$moduleComment} - Simple block using createBlock factory
 *
 * This is a simplified block that uses the factory pattern.
 * No editor.jsx needed - uses auto-generated editor from fields.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import save from './save';

// Create block using factory - handles all boilerplate
export default createBlockFromJson(config, { block, save });
JS;
        } else {
            $content = <<<JS
/**
 * {$name} Block{$moduleComment}
 *
 * Block file structure:
 * - index.js    : Main entry point (this file)
 * - block.json  : Block metadata and configuration
 * - block.jsx   : React component for builder canvas
 * - editor.jsx  : React component for properties panel
 * - save.js     : HTML generators for page/email output
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

// Create block using factory - handles all boilerplate
const {$name}Block = createBlockFromJson(config, { block, editor, save });

// Export parts for direct imports if needed
export { block, editor, config, save };
export default {$name}Block;
JS;
        }

        file_put_contents("{$path}/index.js", $content);
    }
}
