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
    public function __construct(
        protected BlockRegistryService $blockRegistry
    ) {
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
                'saveText' => 'Save Template',
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

        return <<<HTML
        <script>
            window.LaraBuilderServerData = {$json};
            document.addEventListener('DOMContentLoaded', function() {
                if (window.LaraHooks && window.LaraBuilderServerData) {
                    // Register PHP-defined blocks
                    const blocks = window.LaraBuilderServerData.blocks || [];
                    if (window.blockRegistry) {
                        blocks.forEach(function(block) {
                            if (!window.blockRegistry.has(block.type)) {
                                window.blockRegistry.register(block);
                            }
                        });
                    }
                }
            });
        </script>
        HTML;
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
