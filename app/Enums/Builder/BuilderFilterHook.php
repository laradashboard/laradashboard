<?php

declare(strict_types=1);

namespace App\Enums\Builder;

/**
 * Builder Filter Hooks
 *
 * These hooks allow filtering and modifying builder data at various points.
 * They mirror the JavaScript BuilderHooks constants for backend integration.
 */
enum BuilderFilterHook: string
{
    // Block filters
    case BUILDER_BLOCKS = 'filter.builder.blocks';
    case BUILDER_BLOCKS_EMAIL = 'filter.builder.blocks.email';
    case BUILDER_BLOCKS_PAGE = 'filter.builder.blocks.page';
    case BUILDER_BLOCKS_CAMPAIGN = 'filter.builder.blocks.campaign';

    // Configuration filters
    case BUILDER_CONFIG = 'filter.builder.config';
    case BUILDER_CONFIG_EMAIL = 'filter.builder.config.email';
    case BUILDER_CONFIG_PAGE = 'filter.builder.config.page';
    case BUILDER_CONFIG_CAMPAIGN = 'filter.builder.config.campaign';

    // HTML output filters
    case BUILDER_HTML_GENERATED = 'filter.builder.html.generated';
    case BUILDER_HTML_BLOCK = 'filter.builder.html.block';
    case BUILDER_HTML_BEFORE_WRAP = 'filter.builder.html.before_wrap';
    case BUILDER_HTML_AFTER_WRAP = 'filter.builder.html.after_wrap';

    // Canvas settings filters
    case BUILDER_CANVAS_SETTINGS = 'filter.builder.canvas.settings';
    case BUILDER_CANVAS_SETTINGS_EMAIL = 'filter.builder.canvas.settings.email';
    case BUILDER_CANVAS_SETTINGS_PAGE = 'filter.builder.canvas.settings.page';

    // Block data filters
    case BUILDER_BLOCK_PROPS = 'filter.builder.block.props';
    case BUILDER_BLOCK_VALIDATE = 'filter.builder.block.validate';

    // Save filters
    case BUILDER_SAVE_DATA = 'filter.builder.save.data';
    case BUILDER_SAVE_HTML = 'filter.builder.save.html';

    /**
     * Get the context-specific blocks hook
     */
    public static function blocksForContext(string $context): string
    {
        return match ($context) {
            'email' => self::BUILDER_BLOCKS_EMAIL->value,
            'page' => self::BUILDER_BLOCKS_PAGE->value,
            'campaign' => self::BUILDER_BLOCKS_CAMPAIGN->value,
            default => self::BUILDER_BLOCKS->value,
        };
    }

    /**
     * Get the context-specific config hook
     */
    public static function configForContext(string $context): string
    {
        return match ($context) {
            'email' => self::BUILDER_CONFIG_EMAIL->value,
            'page' => self::BUILDER_CONFIG_PAGE->value,
            'campaign' => self::BUILDER_CONFIG_CAMPAIGN->value,
            default => self::BUILDER_CONFIG->value,
        };
    }
}
