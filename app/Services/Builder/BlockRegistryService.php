<?php

declare(strict_types=1);

namespace App\Services\Builder;

use App\Enums\Builder\BuilderContext;
use App\Enums\Builder\BuilderFilterHook;
use TorMorten\Eventy\Facades\Eventy;

/**
 * Block Registry Service
 *
 * Manages server-side block registration for the LaraBuilder.
 * Blocks registered here are merged with JavaScript-registered blocks.
 *
 * @example
 * // In a module's ServiceProvider:
 * $blockRegistry = app(BlockRegistryService::class);
 * $blockRegistry->register([
 *     'type' => 'crm-product',
 *     'label' => 'Product Card',
 *     'category' => 'CRM',
 *     'icon' => 'mdi:package-variant',
 *     'contexts' => ['email', 'campaign'],
 *     'defaultProps' => ['productId' => null, 'showPrice' => true],
 *     'component' => 'CrmProductBlock',
 * ]);
 */
class BlockRegistryService
{
    /**
     * Registered blocks
     */
    protected array $blocks = [];

    /**
     * Register a new block type
     */
    public function register(array $definition): self
    {
        $this->validateDefinition($definition);

        $type = $definition['type'];

        $this->blocks[$type] = [
            'type' => $type,
            'label' => $definition['label'] ?? ucfirst($type),
            'category' => $definition['category'] ?? 'Custom',
            'icon' => $definition['icon'] ?? 'mdi:puzzle',
            'contexts' => $definition['contexts'] ?? ['email', 'page', 'campaign'],
            'defaultProps' => $definition['defaultProps'] ?? [],
            'component' => $definition['component'] ?? $type,
            'supports' => $definition['supports'] ?? [
                'align' => true,
                'spacing' => true,
                'colors' => true,
            ],
            'isCustom' => true,
        ];

        return $this;
    }

    /**
     * Unregister a block type
     */
    public function unregister(string $type): self
    {
        unset($this->blocks[$type]);

        return $this;
    }

    /**
     * Get a block definition
     */
    public function get(string $type): ?array
    {
        return $this->blocks[$type] ?? null;
    }

    /**
     * Get all registered blocks
     */
    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * Get blocks filtered by context
     */
    public function getForContext(string|BuilderContext $context): array
    {
        $contextValue = $context instanceof BuilderContext ? $context->value : $context;

        $blocks = array_filter($this->blocks, function ($block) use ($contextValue) {
            return in_array($contextValue, $block['contexts']);
        });

        // Apply filter hook
        $hookName = BuilderFilterHook::blocksForContext($contextValue);

        /** @var array $filteredBlocks */
        $filteredBlocks = Eventy::filter($hookName, $blocks);

        return $filteredBlocks;
    }

    /**
     * Get blocks grouped by category
     */
    public function getByCategory(string|BuilderContext|null $context = null): array
    {
        $blocks = $context ? $this->getForContext($context) : $this->blocks;

        $grouped = [];
        foreach ($blocks as $block) {
            $category = $block['category'];
            if (! isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $block;
        }

        return $grouped;
    }

    /**
     * Check if a block type is registered
     */
    public function has(string $type): bool
    {
        return isset($this->blocks[$type]);
    }

    /**
     * Get the JavaScript injection data
     *
     * This data is passed to the frontend to register PHP-defined blocks
     */
    public function getJavaScriptData(): array
    {
        return [
            'blocks' => array_values($this->blocks),
        ];
    }

    /**
     * Validate a block definition
     *
     * @throws \InvalidArgumentException
     */
    protected function validateDefinition(array $definition): void
    {
        if (empty($definition['type'])) {
            throw new \InvalidArgumentException('Block definition must have a "type" property.');
        }

        if (! preg_match('/^[a-z][a-z0-9-]*$/', $definition['type'])) {
            throw new \InvalidArgumentException(
                'Block type must start with a lowercase letter and contain only lowercase letters, numbers, and hyphens.'
            );
        }

        if (isset($definition['contexts']) && ! is_array($definition['contexts'])) {
            throw new \InvalidArgumentException('Block "contexts" must be an array.');
        }

        if (isset($definition['defaultProps']) && ! is_array($definition['defaultProps'])) {
            throw new \InvalidArgumentException('Block "defaultProps" must be an array.');
        }
    }
}
