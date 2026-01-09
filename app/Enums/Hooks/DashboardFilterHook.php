<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Dashboard Filter Hooks
 *
 * Provides filter hooks for dashboard customization that modules can use.
 * Use these hooks to add custom widgets, stats, charts, and content to the dashboard.
 *
 * @example
 * // Add custom dashboard widget
 * Hook::addFilter(DashboardFilterHook::DASHBOARD_WIDGETS, function ($widgets) {
 *     $widgets[] = [
 *         'id' => 'crm_stats',
 *         'title' => 'CRM Statistics',
 *         'component' => 'crm::widgets.stats',
 *         'order' => 5,
 *     ];
 *     return $widgets;
 * });
 */
enum DashboardFilterHook: string
{
    // ==========================================================================
    // Dashboard Layout Hooks
    // ==========================================================================

    /**
     * Filter which dashboard sections are visible.
     * Return an array of section keys to show. Remove keys to hide sections.
     *
     * Available sections: quick_actions, stat_cards, user_growth, quick_draft,
     *                     post_chart, recent_posts
     *
     * @example
     * // Hide quick actions and recent posts
     * Hook::addFilter(DashboardFilterHook::DASHBOARD_SECTIONS, function ($sections) {
     *     return array_diff($sections, ['quick_actions', 'recent_posts']);
     * });
     *
     * @param array $sections The visible sections
     * @return array Modified sections
     */
    case DASHBOARD_SECTIONS = 'filter.dashboard.sections';

    /**
     * Hook after the dashboard breadcrumbs.
     */
    case DASHBOARD_AFTER_BREADCRUMBS = 'filter.dashboard.after_breadcrumbs';

    /**
     * Hook before the dashboard content.
     */
    case DASHBOARD_BEFORE_CONTENT = 'filter.dashboard.before_content';

    /**
     * Hook after all dashboard content.
     */
    case DASHBOARD_AFTER = 'filter.dashboard.after';

    // ==========================================================================
    // Dashboard Cards/Stats Hooks
    // ==========================================================================

    /**
     * Hook before the users stat card.
     */
    case DASHBOARD_CARDS_BEFORE_USERS = 'filter.dashboard.cards.before_users';

    /**
     * Hook after the users stat card.
     */
    case DASHBOARD_CARDS_AFTER_USERS = 'filter.dashboard.cards.after_users';

    /**
     * Hook after the roles stat card.
     */
    case DASHBOARD_CARDS_AFTER_ROLES = 'filter.dashboard.cards.after_roles';

    /**
     * Hook after the permissions stat card.
     */
    case DASHBOARD_CARDS_AFTER_PERMISSIONS = 'filter.dashboard.cards.after_permissions';

    /**
     * Hook after the translations stat card.
     */
    case DASHBOARD_CARDS_AFTER_TRANSLATIONS = 'filter.dashboard.cards.after_translations';

    /**
     * Hook after all stat cards.
     */
    case DASHBOARD_CARDS_AFTER = 'filter.dashboard.cards.after';

    // ==========================================================================
    // Dashboard Widgets
    // ==========================================================================

    /**
     * Filter the list of dashboard widgets.
     * Use this to add custom widgets from modules.
     *
     * @param array $widgets The widgets array
     * @return array Modified widgets
     */
    case DASHBOARD_WIDGETS = 'filter.dashboard.widgets';

    /**
     * Filter widget order/priority.
     *
     * @param array $order The widget order
     * @return array Modified order
     */
    case DASHBOARD_WIDGET_ORDER = 'filter.dashboard.widget.order';

    /**
     * Hook before the widgets section.
     */
    case DASHBOARD_WIDGETS_BEFORE = 'filter.dashboard.widgets.before';

    /**
     * Hook after the widgets section.
     */
    case DASHBOARD_WIDGETS_AFTER = 'filter.dashboard.widgets.after';

    // ==========================================================================
    // Dashboard Charts
    // ==========================================================================

    /**
     * Filter the list of available dashboard charts.
     *
     * @param array $charts The charts array
     * @return array Modified charts
     */
    case DASHBOARD_CHARTS = 'filter.dashboard.charts';

    /**
     * Filter user growth chart data.
     *
     * @param array $data The chart data
     * @return array Modified data
     */
    case DASHBOARD_CHART_USER_GROWTH = 'filter.dashboard.chart.user_growth';

    /**
     * Filter post growth chart data.
     *
     * @param array $data The chart data
     * @return array Modified data
     */
    case DASHBOARD_CHART_POST_GROWTH = 'filter.dashboard.chart.post_growth';

    /**
     * Hook before the charts section.
     */
    case DASHBOARD_CHARTS_BEFORE = 'filter.dashboard.charts.before';

    /**
     * Hook after the charts section.
     */
    case DASHBOARD_CHARTS_AFTER = 'filter.dashboard.charts.after';

    // ==========================================================================
    // Dashboard Stats
    // ==========================================================================

    /**
     * Filter the list of dashboard statistics.
     * Use this to add custom stats from modules.
     *
     * @param array $stats The stats array
     * @return array Modified stats
     */
    case DASHBOARD_STATS = 'filter.dashboard.stats';

    /**
     * Filter individual stat value.
     *
     * @param mixed $value The stat value
     * @param string $key The stat key
     * @return mixed Modified value
     */
    case DASHBOARD_STAT_VALUE = 'filter.dashboard.stat.value';

    // ==========================================================================
    // Dashboard Quick Actions
    // ==========================================================================

    /**
     * Filter quick action buttons on the dashboard.
     *
     * @param array $actions The actions array
     * @return array Modified actions
     */
    case DASHBOARD_QUICK_ACTIONS = 'filter.dashboard.quick_actions';

    // ==========================================================================
    // Dashboard Recent Activity
    // ==========================================================================

    /**
     * Filter the recent activity list.
     *
     * @param array $activities The activities
     * @return array Modified activities
     */
    case DASHBOARD_RECENT_ACTIVITY = 'filter.dashboard.recent_activity';

    /**
     * Hook before the recent activity section.
     */
    case DASHBOARD_RECENT_ACTIVITY_BEFORE = 'filter.dashboard.recent_activity.before';

    /**
     * Hook after the recent activity section.
     */
    case DASHBOARD_RECENT_ACTIVITY_AFTER = 'filter.dashboard.recent_activity.after';

    // ==========================================================================
    // Dashboard Sidebar
    // ==========================================================================

    /**
     * Hook for dashboard sidebar content (if applicable).
     */
    case DASHBOARD_SIDEBAR = 'filter.dashboard.sidebar';

    /**
     * Hook before the dashboard sidebar.
     */
    case DASHBOARD_SIDEBAR_BEFORE = 'filter.dashboard.sidebar.before';

    /**
     * Hook after the dashboard sidebar.
     */
    case DASHBOARD_SIDEBAR_AFTER = 'filter.dashboard.sidebar.after';
}
