<?php

declare(strict_types=1);

namespace App\Services\Builder;

/**
 * @deprecated Use per-block style.css files instead.
 * Dark mode CSS is now co-located with each block at:
 *   resources/js/lara-builder/blocks/{type}/style.css
 *
 * The DesignJsonRenderer auto-discovers and includes these files
 * for block types actually used on the page.
 */
class BlockDarkModeStyles
{
    // This class is no longer used.
    // Kept temporarily for backward compatibility.
}
