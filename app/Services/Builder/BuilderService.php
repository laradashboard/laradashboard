<?php

declare(strict_types=1);

namespace App\Services\Builder;

use App\Enums\Builder\BuilderActionHook;
use App\Enums\Builder\BuilderContext;
use App\Enums\Builder\BuilderFilterHook;
use Illuminate\Support\Facades\View;
use TorMorten\Eventy\Facades\Eventy;

/**
 * Builder Service
 *
 * Main service for the LaraBuilder system.
 * Provides configuration, block injection, and helper methods.
 *
 * @example
 * $builder = app(BuilderService::class);
 *
 * // Get config for a context
 * $config = $builder->getConfig('email');
 *
 * // Inject blocks to frontend
 * $builder->injectBlocksToFrontend();
 */
class BuilderService
{
    /**
     * Registered module block script paths
     */
    protected array $moduleScripts = [];

    /**
     * Registered server-side render callbacks for blocks
     * These take priority over JavaScript htmlGenerators (save.js)
     */
    protected array $blockRenderCallbacks = [];

    public function __construct(
        protected BlockRegistryService $blockRegistry
    ) {
    }

    /**
     * Register a module's block script to be loaded with the builder
     *
     * @param  string  $path  Vite asset path (e.g., 'modules/Crm/resources/js/lara-builder-blocks/crm-contact/index.js')
     * @param  string  $buildPath  The Vite build directory (e.g., 'build-crm')
     */
    public function registerModuleScript(string $path, string $buildPath = 'build'): self
    {
        $this->moduleScripts[] = [
            'path' => $path,
            'buildPath' => $buildPath,
        ];

        return $this;
    }

    /**
     * Register a module block with optional server-side render callback
     *
     * Block file structure convention:
     * - index.js    : Main entry point, block definition and registration
     * - block.jsx   : React component for rendering in the builder canvas
     * - editor.jsx  : React component for the properties panel editor
     * - save.js     : HTML generators for different contexts (email, page, etc.)
     * - render.php  : Server-side rendering (optional, takes priority over save.js)
     *
     * @param  string  $blockType  The block type (e.g., 'crm-contact')
     * @param  string  $blockPath  Path to the block folder containing the block files
     * @param  string  $buildPath  The Vite build directory (e.g., 'build-crm')
     */
    public function registerModuleBlock(string $blockType, string $blockPath, string $buildPath = 'build'): self
    {
        // Register the JavaScript entry point
        $indexPath = rtrim($blockPath, '/') . '/index.js';
        if (file_exists($indexPath)) {
            $this->registerModuleScript($indexPath, $buildPath);
        }

        // Check for render.php and register the callback if it exists
        $renderPhpPath = rtrim($blockPath, '/') . '/render.php';
        if (file_exists($renderPhpPath)) {
            $this->registerBlockRenderCallback($blockType, $renderPhpPath);
        }

        return $this;
    }

    /**
     * Register a server-side render callback for a block
     *
     * The callback takes priority over JavaScript htmlGenerators (save.js)
     *
     * @param  string  $blockType  The block type
     * @param  string|callable  $callback  Path to render.php or a callable
     */
    public function registerBlockRenderCallback(string $blockType, string|callable $callback): self
    {
        if (is_string($callback) && file_exists($callback)) {
            // Load the callback from render.php file
            $this->blockRenderCallbacks[$blockType] = require $callback;
        } else {
            $this->blockRenderCallbacks[$blockType] = $callback;
        }

        return $this;
    }

    /**
     * Check if a block has a server-side render callback
     */
    public function hasBlockRenderCallback(string $blockType): bool
    {
        return isset($this->blockRenderCallbacks[$blockType]);
    }

    /**
     * Get the server-side render callback for a block
     */
    public function getBlockRenderCallback(string $blockType): ?callable
    {
        return $this->blockRenderCallbacks[$blockType] ?? null;
    }

    /**
     * Render a block using its server-side render callback
     *
     * @param  string  $blockType  The block type
     * @param  array  $props  Block properties
     * @param  string  $context  Rendering context (email, page, campaign)
     * @param  string|null  $blockId  Optional unique block identifier
     * @return string|null HTML output or null if no callback registered
     */
    public function renderBlock(string $blockType, array $props, string $context = 'page', ?string $blockId = null): ?string
    {
        $callback = $this->getBlockRenderCallback($blockType);

        if (! $callback) {
            return null;
        }

        return call_user_func($callback, $props, $context, $blockId);
    }

    /**
     * Get all registered block render callbacks
     */
    public function getBlockRenderCallbacks(): array
    {
        return $this->blockRenderCallbacks;
    }

    /**
     * Get all registered module block scripts
     */
    public function getModuleScripts(): array
    {
        return $this->moduleScripts;
    }

    /**
     * Get the block registry service
     */
    public function blocks(): BlockRegistryService
    {
        return $this->blockRegistry;
    }

    /**
     * Register a new block (convenience method)
     */
    public function registerBlock(array $definition): self
    {
        $this->blockRegistry->register($definition);

        return $this;
    }

    /**
     * Get configuration for a builder context
     */
    public function getConfig(string|BuilderContext $context): array
    {
        $contextValue = $context instanceof BuilderContext ? $context->value : $context;
        $contextEnum = $context instanceof BuilderContext ? $context : BuilderContext::tryFrom($context);

        $config = [
            'context' => $contextValue,
            'labels' => $this->getLabelsForContext($contextValue),
            'features' => $contextEnum ? $this->getFeaturesForContext($contextEnum) : [],
            'blocks' => $this->blockRegistry->getForContext($contextValue),
        ];

        // Apply filter hook
        $hookName = BuilderFilterHook::configForContext($contextValue);

        /** @var array $filteredConfig */
        $filteredConfig = Eventy::filter($hookName, $config);

        return $filteredConfig;
    }

    /**
     * Get labels for a context
     */
    protected function getLabelsForContext(string $context): array
    {
        return match ($context) {
            'email' => [
                'title' => 'Email Builder',
                'backText' => 'Back to Templates',
                'saveText' => 'Update',
            ],
            'page' => [
                'title' => 'Page Builder',
                'backText' => 'Back to Posts',
                'saveText' => 'Save',
            ],
            'campaign' => [
                'title' => 'Campaign Editor',
                'backText' => 'Back to Campaign',
                'saveText' => 'Save Campaign',
            ],
            default => [
                'title' => 'Builder',
                'backText' => 'Back',
                'saveText' => 'Save',
            ],
        };
    }

    /**
     * Get features supported by a context
     */
    protected function getFeaturesForContext(BuilderContext $context): array
    {
        return match ($context) {
            BuilderContext::EMAIL => [
                'inlineStyles' => true,
                'tables' => true,
                'msoConditionals' => true,
                'videoThumbnails' => true,
                'cssClasses' => false,
                'nativeVideo' => false,
            ],
            BuilderContext::PAGE => [
                'inlineStyles' => false,
                'tables' => false,
                'msoConditionals' => false,
                'videoThumbnails' => false,
                'cssClasses' => true,
                'nativeVideo' => true,
            ],
            BuilderContext::CAMPAIGN => [
                'inlineStyles' => true,
                'tables' => true,
                'msoConditionals' => true,
                'videoThumbnails' => true,
                'cssClasses' => false,
                'nativeVideo' => false,
                'personalization' => true,
            ],
        };
    }

    /**
     * Inject blocks and configuration to frontend
     *
     * Call this in your view to make PHP-registered blocks available to JavaScript
     */
    public function injectToFrontend(?string $context = null): string
    {
        $data = [
            'blocks' => $this->blockRegistry->getJavaScriptData()['blocks'],
        ];

        if ($context) {
            $data['config'] = $this->getConfig($context);
        }

        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Generate module script tags
        $moduleScriptTags = $this->generateModuleScriptTags();

        return <<<HTML
        <script>
            window.LaraBuilderServerData = {$json};
            document.addEventListener('DOMContentLoaded', function() {
                if (window.LaraHooks && window.LaraBuilderServerData) {
                    // Register PHP-defined blocks
                    const blocks = window.LaraBuilderServerData.blocks || [];
                    if (window.blockRegistry) {
                        blocks.forEach(function(block) {
                            // Skip invalid blocks (must have type)
                            if (!block || !block.type) return;
                            if (!window.blockRegistry.has(block.type)) {
                                window.blockRegistry.register(block);
                            }
                        });
                    }
                }
            });
        </script>
        {$moduleScriptTags}
        HTML;
    }

    /**
     * Generate script tags for module blocks
     *
     * In production: Uses Vite manifest to generate proper script tags
     * In development: Loads directly from Vite dev server
     */
    protected function generateModuleScriptTags(): string
    {
        if (empty($this->moduleScripts)) {
            return '';
        }

        $tags = [];
        foreach ($this->moduleScripts as $script) {
            $path = $script['path'];
            $buildPath = $script['buildPath'];

            // Get relative path from base for Vite (lowercase for consistency with manifest)
            $relativePath = str_replace(base_path() . '/', '', $path);
            $relativePath = strtolower($relativePath);

            // Check if we're in production (manifest exists)
            $manifestPath = public_path($buildPath . '/.vite/manifest.json');
            if (! file_exists($manifestPath)) {
                $manifestPath = public_path($buildPath . '/manifest.json');
            }

            if (file_exists($manifestPath)) {
                // Production: Use built assets from manifest
                try {
                    $manifest = json_decode(file_get_contents($manifestPath), true);
                    if (isset($manifest[$relativePath])) {
                        $assetPath = $manifest[$relativePath]['file'];
                        $tags[] = sprintf(
                            '<script type="module" src="/%s/assets/%s"></script>',
                            $buildPath,
                            basename($assetPath)
                        );
                    }
                } catch (\Exception) {
                    continue;
                }
            } else {
                // Development: Try to load from Vite dev server
                // The script will be loaded by the main Vite instance if available
                try {
                    $viteTag = \Illuminate\Support\Facades\Vite::useBuildDirectory($buildPath)
                        ->withEntryPoints([$relativePath])
                        ->toHtml();
                    $tags[] = $viteTag;
                } catch (\Exception) {
                    continue;
                }
            }
        }

        return implode("\n", $tags);
    }

    /**
     * Get module scripts configuration for use in Blade templates
     *
     * Returns array of [path, buildPath] for use with @vite directive
     */
    public function getModuleScriptsForBlade(): array
    {
        $scripts = [];
        foreach ($this->moduleScripts as $script) {
            $relativePath = str_replace(base_path() . '/', '', $script['path']);
            $scripts[] = [
                'path' => $relativePath,
                'buildPath' => $script['buildPath'],
            ];
        }

        return $scripts;
    }

    /**
     * Share builder data with views
     */
    public function shareWithViews(): void
    {
        View::share('laraBuilder', [
            'contexts' => BuilderContext::toArray(),
            'hasCustomBlocks' => count($this->blockRegistry->all()) > 0,
        ]);
    }

    /**
     * Fire an action hook
     */
    public function doAction(BuilderActionHook $hook, mixed ...$args): void
    {
        Eventy::action($hook->value, ...$args);
    }

    /**
     * Apply a filter hook
     */
    public function applyFilter(BuilderFilterHook $hook, mixed $value, mixed ...$args): mixed
    {
        return Eventy::filter($hook->value, $value, ...$args);
    }

    /**
     * Add an action listener
     */
    public function addAction(BuilderActionHook $hook, callable $callback, int $priority = 20): void
    {
        Eventy::addAction($hook->value, $callback, $priority);
    }

    /**
     * Add a filter listener
     */
    public function addFilter(BuilderFilterHook $hook, callable $callback, int $priority = 20): void
    {
        Eventy::addFilter($hook->value, $callback, $priority);
    }
}
