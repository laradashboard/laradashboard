<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum CommonFilterHook: string
{
    // Media
    case MEDIA_AFTER_BREADCRUMBS = 'filter.media_after_breadcrumbs';

    // Translations
    case TRANSLATION_AFTER_BREADCRUMBS = 'filter.translation_after_breadcrumbs';

    // Languages
    case LANGUAGES = 'filter.languages';

    // Environment
    case AVAILABLE_KEYS = 'filter.available_keys';
    case EXCLUDED_SETTING_KEYS = 'filter.excluded_setting_keys';

    // Advanced fields
    case ADVANCED_FIELDS_TYPES = 'filter.advanced_fields_types';

    // reCAPTCHA
    case RECAPTCHA_IS_ENABLED_FOR_PAGE = 'filter.recaptcha_is_enabled_for_page';
    case RECAPTCHA_PRE_VERIFICATION = 'filter.recaptcha_pre_verification';
    case RECAPTCHA_VERIFY_URL = 'filter.recaptcha_verify_url';
    case RECAPTCHA_POST_VERIFICATION = 'filter.recaptcha_post_verification';
    case RECAPTCHA_VERIFICATION_EXCEPTION = 'filter.recaptcha_verification_exception';
    case RECAPTCHA_AVAILABLE_PAGES = 'filter.recaptcha_available_pages';

    // Registration anti-spam
    case REGISTRATION_GUARD_VALIDATION_RULES = 'filter.registration_guard.validation_rules';
    case REGISTRATION_SPAM_NAME_PATTERNS = 'filter.registration_guard.spam_name_patterns';
    case REGISTRATION_DISPOSABLE_EMAIL_DOMAINS = 'filter.registration_guard.disposable_email_domains';

    // Public forms (contact, support ticket, newsletter, etc.)
    case PUBLIC_FORM_GUARD_VALIDATION_RULES = 'filter.public_form_guard.validation_rules';

    // Email submission (registration, public forms)
    case EMAIL_SUBMISSION_VALIDATION_RULES = 'filter.email_submission.validation_rules';
}
