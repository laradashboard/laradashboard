<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum AdminFilterHook: string
{
    case ADMIN_HEAD = 'filter.admin_head';
    case ADMIN_HEAD_MIDDLE = 'filter.admin_head_middle';
    case ADMIN_SITE_ONLY = 'filter.admin_site_only';
    case ADMIN_FOOTER_BEFORE = 'filter.admin_footer_before';
    case ADMIN_FOOTER_AFTER = 'filter.admin_footer_after';

    // Dark mode.
    case DARK_MODE_TOGGLER_BEFORE_BUTTON = 'filter.dark_mode_toggler_before_button';
    case DARK_MODE_TOGGLER_AFTER_BUTTON = 'filter.dark_mode_toggler_after_button';

    // Header right menu.
    case HEADER_RIGHT_MENU_BEFORE = 'filter.header_right_menu_before';
    case HEADER_RIGHT_MENU_AFTER = 'filter.header_right_menu_after';
    case HEADER_BEFORE_LOCALE_SWITCHER = 'filter.header_before_locale_switcher';
    case HEADER_AFTER_LOCALE_SWITCHER = 'filter.header_after_locale_switcher';
    case HEADER_AFTER_ACTIONS = 'filter.header_after_actions';
    case USER_DROPDOWN_BEFORE = 'filter.user_dropdown_before';
    case USER_DROPDOWN_AFTER_USER_INFO = 'filter.user_dropdown_after_user_info';
    case USER_DROPDOWN_AFTER_PROFILE_LINKS = 'filter.user_dropdown_after_profile_links';
    case USER_DROPDOWN_AFTER_SETTINGS_LINK = 'filter.user_dropdown_after_settings_link';
    case USER_DROPDOWN_AFTER_LOGOUT = 'filter.user_dropdown_after_logout';

    // Sidebar menu.
    case SIDEBAR_MENU_GROUP_BEFORE = 'filter.sidebar_menu_group_before_';
    case SIDEBAR_MENU_GROUP_HEADING_BEFORE = 'filter.sidebar_menu_group_heading_before_';
    case SIDEBAR_MENU_GROUP_HEADING_AFTER = 'filter.sidebar_menu_group_heading_after_';
    case SIDEBAR_MENU_BEFORE = 'filter.sidebar_menu_before_';
    case SIDEBAR_MENU_AFTER = 'filter.sidebar_menu_after_';
    case SIDEBAR_MENU_BEFORE_ALL = 'filter.sidebar_menu_before_all_';
    case SIDEBAR_MENU_AFTER_ALL = 'filter.sidebar_menu_after_all_';
    case SIDEBAR_MENU_GROUP_AFTER = 'filter.sidebar_menu_group_after_';
    case SIDEBAR_MENU_ITEM_AFTER = 'filter.sidebar_menu_item_after_';
    case SIDEBAR_MENU = 'filter.sidebar_menu_';

    // Menu.
    case ADMIN_MENU_GROUPS_BEFORE_SORTING = 'filter.admin_menu_groups_before_sorting';
}
