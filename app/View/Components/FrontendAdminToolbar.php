<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\Hooks\AdminFilterHook;
use App\Models\Post;
use App\Support\Facades\Hook;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FrontendAdminToolbar extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $leftItems = [];

    /** @var array<int, array<string, mixed>> */
    public array $rightItems = [];

    public bool $enabled = false;

    public function __construct(
        public ?Post $contextPost = null,
    ) {
        $this->enabled = $this->resolveEnabled();

        if ($this->enabled) {
            $this->resolveItems();
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.frontend-admin-toolbar');
    }

    public function shouldRender(): bool
    {
        return $this->enabled;
    }

    protected function resolveEnabled(): bool
    {
        // Must be authenticated
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Check setting
        $settingEnabled = config('settings.frontend_admin_toolbar_enabled', 'true');

        if ($settingEnabled !== 'true' && $settingEnabled !== true) {
            return false;
        }

        // Check role visibility
        $allowedRoles = config('settings.frontend_admin_toolbar_roles', 'Superadmin,Admin,Editor');
        $roles = array_map('trim', explode(',', (string) $allowedRoles));

        if (! $user->hasAnyRole($roles)) {
            return false;
        }

        // Allow runtime override via filter
        return (bool) Hook::applyFilters(AdminFilterHook::FRONTEND_TOOLBAR_ENABLED, true);
    }

    protected function resolveItems(): void
    {
        $items = [];

        // Dashboard
        $items[] = [
            'id' => 'dashboard',
            'label' => __('Dashboard'),
            'url' => url('/admin'),
            'icon' => 'lucide:layout-dashboard',
            'position' => 'left',
            'priority' => 10,
            'permission' => null,
            'separator' => false,
        ];

        // Edit current post/page
        if ($this->contextPost) {
            $editLabel = $this->contextPost->post_type === 'page'
                ? __('Edit Page')
                : __('Edit Post');

            $items[] = [
                'id' => 'edit-current',
                'label' => $editLabel,
                'url' => url('/admin/posts/' . ($this->contextPost->post_type ?? 'page') . '/' . $this->contextPost->id . '/edit'),
                'icon' => 'lucide:pencil',
                'position' => 'left',
                'priority' => 20,
                'permission' => null,
                'separator' => false,
            ];
        }

        // New Page
        $items[] = [
            'id' => 'new-page',
            'label' => __('New Page'),
            'url' => url('/admin/posts/page/create'),
            'icon' => 'lucide:plus',
            'position' => 'left',
            'priority' => 30,
            'permission' => null,
            'separator' => false,
        ];

        // New Post
        $items[] = [
            'id' => 'new-post',
            'label' => __('New Post'),
            'url' => url('/admin/posts/post/create'),
            'icon' => 'lucide:file-plus',
            'position' => 'left',
            'priority' => 40,
            'permission' => null,
            'separator' => false,
        ];

        // Apply filter so modules can add/remove/modify items
        $items = Hook::applyFilters(AdminFilterHook::FRONTEND_TOOLBAR_ITEMS, $items);

        // Sort by priority
        usort($items, fn ($a, $b) => ($a['priority'] ?? 50) <=> ($b['priority'] ?? 50));

        // Filter by permission
        $user = auth()->user();
        $items = array_filter($items, function ($item) use ($user) {
            if (empty($item['permission'])) {
                return true;
            }

            return $user && $user->can($item['permission']);
        });

        // Split into left/right
        foreach ($items as $item) {
            $position = $item['position'] ?? 'left';

            if ($position === 'right') {
                $this->rightItems[] = $item;
            } else {
                $this->leftItems[] = $item;
            }
        }
    }
}
