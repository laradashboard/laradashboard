<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum SettingFilterHook: string
{
    // Validation hooks
    case SETTINGS_UPDATE_VALIDATION_RULES = 'filter.settings.update.validation.rules';

    // Data filtering hooks
    case SETTINGS_TABS = 'filter.settings.tabs';
    case SETTINGS_RESTRICTED_FIELDS = 'filter.settings.restricted_fields';

    // UI Hooks - General tab
    case SETTINGS_GENERAL_TAB_BEFORE_SECTION_START = 'filter.settings.general_tab_before_section_start';
    case SETTINGS_GENERAL_TAB_BEFORE_SECTION_END = 'filter.settings.general_tab_before_section_end';

    // UI Hooks - Appearance tab
    case SETTINGS_APPEARANCE_TAB_BEFORE_SECTION_START = 'filter.settings.appearance_tab_before_section_start';
    case SETTINGS_APPEARANCE_TAB_BEFORE_SECTION_END = 'filter.settings.appearance_tab_before_section_end';
    case SETTINGS_APPEARANCE_TAB_AFTER_SECTION_END = 'filter.settings.appearance_tab_after_section_end';

    // UI Hooks - Content tab
    case SETTINGS_CONTENT_TAB_BEFORE_SECTION_START = 'filter.settings.content_tab_before_section_start';
    case SETTINGS_CONTENT_TAB_BEFORE_SECTION_END = 'filter.settings.content_tab_before_section_end';
    case SETTINGS_CONTENT_TAB_AFTER_SECTION_END = 'filter.settings.content_tab_after_section_end';

    // UI Hooks - Security tab
    case SETTINGS_PERFORMANCE_SECURITY_TAB_BEFORE_SECTION_START = 'filter.settings.performance_security_tab_before_section_start';
    case SETTINGS_PERFORMANCE_SECURITY_TAB_AFTER_SECTION_END = 'filter.settings.performance_security_tab_after_section_end';

    // UI Hooks - Integration tab
    case SETTINGS_INTEGRATIONS_TAB_BEFORE_SECTION_START = 'filter.settings.integrations_tab_before_section_start';
    case SETTINGS_INTEGRATIONS_TAB_BEFORE_SECTION_END = 'filter.settings.integrations_tab_before_section_end';
    case SETTINGS_INTEGRATIONS_TAB_AFTER_SECTION_END = 'filter.settings.integrations_tab_after_section_end';

    // UI Hooks - AI tab
    case SETTINGS_AI_INTEGRATIONS_TAB_BEFORE_SECTION_START = 'filter.settings.ai_integrations_tab_before_section_start';
    case SETTINGS_AI_INTEGRATIONS_TAB_BEFORE_SECTION_END = 'filter.settings.ai_integrations_tab_before_section_end';
    case SETTINGS_AI_INTEGRATIONS_TAB_AFTER_SECTION_END = 'filter.settings.ai_integrations_tab_after_section_end';

    // UI Hooks - reCAPTCHA tab
    case SETTINGS_RECAPTCHA_INTEGRATIONS_TAB_BEFORE_SECTION_START = 'filter.settings.recaptcha_integrations_tab_before_section_start';
    case SETTINGS_RECAPTCHA_INTEGRATIONS_TAB_BEFORE_SECTION_END = 'filter.settings.recaptcha_integrations_tab_before_section_end';

    // UI Hooks - Tab menu and content
    case SETTINGS_TAB_MENU_BEFORE = 'filter.settings.tab_menu_before_';
    case SETTINGS_TAB_MENU_AFTER = 'filter.settings.tab_menu_after_';
    case SETTINGS_TAB_CONTENT_BEFORE = 'filter.settings.tab_content_before_';
    case SETTINGS_TAB_CONTENT_AFTER = 'filter.settings.tab_content_after_';

    // Breadcrumbs
    case SETTINGS_AFTER_BREADCRUMBS = 'filter.settings.after_breadcrumbs';
}
