{!! Hook::applyFilters(SettingFilterHook::SETTINGS_PERFORMANCE_SECURITY_TAB_BEFORE_SECTION_START, '') !!}

@include('backend.pages.settings.recaptcha-settings')

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_PERFORMANCE_SECURITY_TAB_AFTER_SECTION_END, '') !!}
