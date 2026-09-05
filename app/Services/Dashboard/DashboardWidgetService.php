<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\Hooks\DashboardFilterHook;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserMeta;
use App\Services\SettingService;
use App\Support\Facades\Hook;

class DashboardWidgetService
{
    public const USER_HIDDEN_META_KEY = 'dashboard_hidden_widgets';

    public const LEGACY_USER_HIDDEN_META_KEY = 'dashboard_hidden_stat_cards';

    public const TYPE_SECTION = 'section';

    public const TYPE_STAT = 'stat';

    public function __construct(
        private readonly SettingService $settingService,
    ) {
    }

    public function isVisible(string $widgetId, ?User $user = null): bool
    {
        return ! in_array($widgetId, $this->getHiddenWidgetIds($user), true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllWidgets(?User $user = null, array $context = []): array
    {
        $user ??= auth()->user();

        return collect($this->getSectionWidgets())
            ->merge($this->getStatWidgets($context, $user))
            ->merge($this->getModuleWidgets())
            ->filter(fn (array $widget) => $this->userCanViewWidget($widget, $user))
            ->unique('id')
            ->sortBy(fn (array $widget) => $widget['order'] ?? 100)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getManageableWidgets(?User $user = null): array
    {
        $hiddenIds = $this->getHiddenWidgetIds($user);

        return collect($this->getAllWidgets($user))
            ->map(function (array $widget) use ($hiddenIds) {
                $widget['hidden'] = in_array($widget['id'], $hiddenIds, true);

                return $widget;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getManageableWidgetGroups(?User $user = null): array
    {
        $groups = [];

        foreach ($this->getManageableWidgets($user) as $widget) {
            $group = $widget['group'] ?? __('Other');
            $groups[$group][] = $widget;
        }

        return $groups;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    public function getVisibleStatWidgets(array $context, ?User $user = null): array
    {
        $hiddenIds = $this->getHiddenWidgetIds($user);

        return collect($this->getStatWidgets($context, $user))
            ->reject(fn (array $widget) => in_array($widget['id'], $hiddenIds, true))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function getHiddenWidgetIds(?User $user = null): array
    {
        $user ??= auth()->user();

        $globalHidden = $this->decodeHiddenIds(
            $this->settingService->getSetting(Setting::DASHBOARD_HIDDEN_WIDGETS)
                ?? $this->settingService->getSetting(Setting::DASHBOARD_HIDDEN_STAT_CARDS)
        );

        $userHidden = $user ? $this->getUserHiddenWidgetIds($user) : [];

        $filteredHidden = Hook::applyFilters(
            DashboardFilterHook::DASHBOARD_HIDDEN_WIDGETS,
            Hook::applyFilters(DashboardFilterHook::DASHBOARD_HIDDEN_STAT_CARDS, [])
        );

        return array_values(array_unique(array_merge(
            $globalHidden,
            $userHidden,
            is_array($filteredHidden) ? $filteredHidden : []
        )));
    }

    public function hideWidget(string $widgetId, ?User $user = null): void
    {
        $user ??= auth()->user();

        if (! $user) {
            return;
        }

        $hiddenIds = $this->getUserHiddenWidgetIds($user);

        if (! in_array($widgetId, $hiddenIds, true)) {
            $hiddenIds[] = $widgetId;
        }

        $this->storeUserHiddenWidgetIds($user, $hiddenIds);
    }

    public function showWidget(string $widgetId, ?User $user = null): void
    {
        $user ??= auth()->user();

        if (! $user) {
            return;
        }

        $hiddenIds = array_values(array_filter(
            $this->getUserHiddenWidgetIds($user),
            fn (string $id) => $id !== $widgetId
        ));

        $this->storeUserHiddenWidgetIds($user, $hiddenIds);
    }

    public function resetUserWidgetPreferences(?User $user = null): void
    {
        $user ??= auth()->user();

        if (! $user) {
            return;
        }

        $this->storeUserHiddenWidgetIds($user, []);
    }

    /**
     * @param  array<int, string>  $visibleWidgetIds
     */
    public function setUserVisibleWidgetIds(array $visibleWidgetIds, ?User $user = null): void
    {
        $user ??= auth()->user();

        if (! $user) {
            return;
        }

        $allowedIds = collect($this->getAllWidgets($user))->pluck('id')->all();
        $visibleWidgetIds = array_values(array_intersect($visibleWidgetIds, $allowedIds));
        $hiddenIds = array_values(array_diff($allowedIds, $visibleWidgetIds));

        $this->storeUserHiddenWidgetIds($user, $hiddenIds);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSectionWidgets(): array
    {
        $sections = [
            [
                'id' => 'quick_actions',
                'type' => self::TYPE_SECTION,
                'group' => __('Dashboard widgets'),
                'label' => __('Quick Actions'),
                'order' => 10,
            ],
            [
                'id' => 'user_growth',
                'type' => self::TYPE_SECTION,
                'group' => __('Dashboard widgets'),
                'label' => __('User Growth'),
                'order' => 20,
                'permission' => 'user.view',
            ],
            [
                'id' => 'quick_draft',
                'type' => self::TYPE_SECTION,
                'group' => __('Dashboard widgets'),
                'label' => __('Quick Draft'),
                'order' => 30,
                'permission' => 'post.create',
            ],
            [
                'id' => 'post_chart',
                'type' => self::TYPE_SECTION,
                'group' => __('Dashboard widgets'),
                'label' => __('Post Activity'),
                'order' => 40,
                'permission' => 'post.view',
            ],
            [
                'id' => 'recent_posts',
                'type' => self::TYPE_SECTION,
                'group' => __('Dashboard widgets'),
                'label' => __('Recent Posts'),
                'order' => 50,
                'permission' => 'post.view',
            ],
        ];

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    private function getStatWidgets(array $context, ?User $user = null): array
    {
        $user ??= auth()->user();

        return collect($this->getCoreStatWidgets($context))
            ->merge($this->getModuleStatWidgets())
            ->filter(fn (array $widget) => $this->userCanViewWidget($widget, $user))
            ->sortBy(fn (array $widget) => $widget['order'] ?? 100)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getModuleWidgets(): array
    {
        $widgets = Hook::applyFilters(DashboardFilterHook::DASHBOARD_WIDGETS, []);

        return is_array($widgets) ? $widgets : [];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    private function getCoreStatWidgets(array $context): array
    {
        $group = __('Statistics');

        return [
            [
                'id' => 'core_posts',
                'type' => self::TYPE_STAT,
                'group' => $group,
                'order' => 110,
                'permission' => 'post.view',
                'icon' => 'heroicons:document-duplicate',
                'icon_bg' => '#F59E0B',
                'label' => __('Posts'),
                'value' => $context['total_posts'] ?? 0,
                'url' => route('admin.posts.index', 'post'),
            ],
            [
                'id' => 'core_users',
                'type' => self::TYPE_STAT,
                'group' => $group,
                'order' => 120,
                'permission' => 'user.view',
                'icon' => 'heroicons:user-group',
                'icon_bg' => 'var(--color-brand-500)',
                'label' => __('Users'),
                'value' => $context['total_users'] ?? 0,
                'url' => route('admin.users.index'),
            ],
            [
                'id' => 'core_roles',
                'type' => self::TYPE_STAT,
                'group' => $group,
                'order' => 130,
                'permission' => 'role.view',
                'icon' => 'heroicons:key',
                'icon_bg' => '#00D7FF',
                'label' => __('Roles'),
                'value' => $context['total_roles'] ?? 0,
                'url' => route('admin.roles.index'),
            ],
            [
                'id' => 'core_permissions',
                'type' => self::TYPE_STAT,
                'order' => 140,
                'group' => $group,
                'permission' => 'role.view',
                'icon' => 'bi:shield-check',
                'icon_bg' => '#FF4D96',
                'label' => __('Permissions'),
                'value' => $context['total_permissions'] ?? 0,
                'url' => route('admin.permissions.index'),
            ],
            [
                'id' => 'core_translations',
                'type' => self::TYPE_STAT,
                'group' => $group,
                'order' => 150,
                'permission' => 'settings.view',
                'icon' => 'heroicons:language',
                'icon_bg' => '#22C55E',
                'label' => __('Translations'),
                'value' => ($context['languages']['total'] ?? '0') . ' / ' . ($context['languages']['active'] ?? '0'),
                'url' => route('admin.translations.index'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getModuleStatWidgets(): array
    {
        $stats = Hook::applyFilters(DashboardFilterHook::DASHBOARD_STATS, []);

        if (! is_array($stats)) {
            return [];
        }

        return collect($stats)
            ->map(function (array $stat) {
                $stat['type'] = self::TYPE_STAT;
                $stat['group'] = $stat['group'] ?? __('Statistics');

                return $stat;
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $widget
     */
    private function userCanViewWidget(array $widget, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (! empty($widget['permission'])) {
            return $user->can($widget['permission']);
        }

        if (! empty($widget['permissions'])) {
            return $user->canany((array) $widget['permissions']);
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    private function getUserHiddenWidgetIds(User $user): array
    {
        $primary = UserMeta::query()
            ->where('user_id', $user->id)
            ->where('meta_key', self::USER_HIDDEN_META_KEY)
            ->value('meta_value');

        if (is_string($primary) && $primary !== '') {
            return $this->decodeHiddenIds($primary);
        }

        $legacy = UserMeta::query()
            ->where('user_id', $user->id)
            ->where('meta_key', self::LEGACY_USER_HIDDEN_META_KEY)
            ->value('meta_value');

        return $this->decodeHiddenIds($legacy);
    }

    /**
     * @param  array<int, string>  $hiddenIds
     */
    private function storeUserHiddenWidgetIds(User $user, array $hiddenIds): void
    {
        UserMeta::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'meta_key' => self::USER_HIDDEN_META_KEY,
            ],
            [
                'meta_value' => json_encode(array_values(array_unique($hiddenIds))),
                'type' => 'json',
            ]
        );
    }

    /**
     * @return array<int, string>
     */
    private function decodeHiddenIds(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($id) => is_string($id) && $id !== ''));
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, fn ($id) => is_string($id) && $id !== ''));
    }
}
