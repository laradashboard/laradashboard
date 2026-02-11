<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Module;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModuleLogo extends Component
{
    public ?string $logoUrl = null;

    public string $icon;

    public string $alt;

    public string $size;

    /**
     * Create a new component instance.
     *
     * @param  Module|null  $module  The module object
     * @param  string|null  $logoImage  Direct logo image path/URL (alternative to module)
     * @param  string|null  $icon  Iconify icon name (e.g., 'lucide:layout-dashboard')
     * @param  string|null  $moduleName  Module name for building asset path
     * @param  string  $alt  Alt text for the logo image
     * @param  string  $size  Size preset: 'xs', 'sm', 'md', 'lg', 'xl' or custom Tailwind classes
     */
    public function __construct(
        ?Module $module = null,
        ?string $logoImage = null,
        ?string $icon = null,
        ?string $moduleName = null,
        string $alt = 'Module Logo',
        string $size = 'md',
    ) {
        // If module is provided, extract properties from it
        if ($module !== null) {
            $this->logoUrl = $module->getLogoUrl();
            $this->icon = $module->icon;
            $this->alt = $module->title ?: $alt;
        } else {
            // Use individual properties
            $this->icon = $icon ?? 'lucide:box';
            $this->alt = $alt;

            // Build logo URL from logoImage if provided
            if ($logoImage !== null) {
                if (str_starts_with($logoImage, 'http://') || str_starts_with($logoImage, 'https://')) {
                    $this->logoUrl = $logoImage;
                } elseif ($moduleName !== null) {
                    $this->logoUrl = asset("build-{$moduleName}/{$logoImage}");
                } else {
                    $this->logoUrl = asset($logoImage);
                }
            }
        }

        $this->size = $size;
    }

    /**
     * Get size classes based on the size preset.
     */
    public function sizeClasses(): string
    {
        return match ($this->size) {
            'xs' => 'w-6 h-6',
            'sm' => 'w-8 h-8',
            'md' => 'w-10 h-10',
            'lg' => 'w-12 h-12',
            'xl' => 'w-16 h-16',
            '2xl' => 'w-20 h-20',
            default => $this->size, // Allow custom Tailwind classes
        };
    }

    /**
     * Get icon size class based on the size preset.
     */
    public function iconSizeClass(): string
    {
        return match ($this->size) {
            'xs' => 'text-sm',
            'sm' => 'text-base',
            'md' => 'text-xl',
            'lg' => 'text-2xl',
            'xl' => 'text-3xl',
            '2xl' => 'text-4xl',
            default => 'text-xl',
        };
    }

    /**
     * Check if we have a logo image to display.
     */
    public function hasLogo(): bool
    {
        return $this->logoUrl !== null && $this->logoUrl !== '';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.module-logo');
    }
}
