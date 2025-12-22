<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

#[ObservedBy([SettingObserver::class])]
class Setting extends Model
{
    use HasFactory;

    // =========================================================================
    // System Settings
    // =========================================================================
    public const INSTALLATION_COMPLETED = 'installation_completed';

    public const APP_NAME = 'app_name';

    public const SITE_NAME = 'site_name';

    public const DEFAULT_PAGINATION = 'default_pagination';

    // =========================================================================
    // Theme Colors
    // =========================================================================
    public const PRIMARY_COLOR = 'primary_color';

    public const THEME_PRIMARY_COLOR = 'theme_primary_color';

    public const THEME_SECONDARY_COLOR = 'theme_secondary_color';

    // =========================================================================
    // Sidebar Colors
    // =========================================================================
    public const SIDEBAR_BG_LITE = 'sidebar_bg_lite';

    public const SIDEBAR_BG_DARK = 'sidebar_bg_dark';

    public const SIDEBAR_LI_HOVER_LITE = 'sidebar_li_hover_lite';

    public const SIDEBAR_LI_HOVER_DARK = 'sidebar_li_hover_dark';

    public const SIDEBAR_TEXT_LITE = 'sidebar_text_lite';

    public const SIDEBAR_TEXT_DARK = 'sidebar_text_dark';

    // =========================================================================
    // Navbar Colors
    // =========================================================================
    public const NAVBAR_BG_LITE = 'navbar_bg_lite';

    public const NAVBAR_BG_DARK = 'navbar_bg_dark';

    public const NAVBAR_TEXT_LITE = 'navbar_text_lite';

    public const NAVBAR_TEXT_DARK = 'navbar_text_dark';

    // =========================================================================
    // Text Colors
    // =========================================================================
    public const TEXT_COLOR_LITE = 'text_color_lite';

    public const TEXT_COLOR_DARK = 'text_color_dark';

    // =========================================================================
    // Site Logo & Icons
    // =========================================================================
    public const SITE_LOGO_LITE = 'site_logo_lite';

    public const SITE_LOGO_DARK = 'site_logo_dark';

    public const SITE_ICON = 'site_icon';

    public const SITE_FAVICON = 'site_favicon';

    // =========================================================================
    // Analytics & Tracking
    // =========================================================================
    public const GOOGLE_TAG_MANAGER_SCRIPT = 'google_tag_manager_script';

    public const GOOGLE_ANALYTICS_SCRIPT = 'google_analytics_script';

    // =========================================================================
    // Custom Code
    // =========================================================================
    public const GLOBAL_CUSTOM_CSS = 'global_custom_css';

    public const GLOBAL_CUSTOM_JS = 'global_custom_js';

    // =========================================================================
    // AI Integration
    // =========================================================================
    public const AI_DEFAULT_PROVIDER = 'ai_default_provider';

    public const AI_OPENAI_API_KEY = 'ai_openai_api_key';

    public const AI_CLAUDE_API_KEY = 'ai_claude_api_key';

    // =========================================================================
    // Email Settings
    // =========================================================================
    public const MAIL_MAILER = 'mail_mailer';

    public const MAIL_HOST = 'mail_host';

    public const MAIL_PORT = 'mail_port';

    public const MAIL_USERNAME = 'mail_username';

    public const MAIL_PASSWORD = 'mail_password';

    public const MAIL_ENCRYPTION = 'mail_encryption';

    public const MAIL_FROM_ADDRESS = 'mail_from_address';

    public const MAIL_FROM_NAME = 'mail_from_name';

    // =========================================================================
    // Authentication Settings
    // =========================================================================
    public const AUTH_ENABLE_PUBLIC_LOGIN = 'auth_enable_public_login';

    public const AUTH_ENABLE_PUBLIC_REGISTRATION = 'auth_enable_public_registration';

    public const AUTH_ENABLE_PASSWORD_RESET = 'auth_enable_password_reset';

    public const AUTH_ENABLE_EMAIL_VERIFICATION = 'auth_enable_email_verification';

    public const AUTH_DEFAULT_USER_ROLE = 'auth_default_user_role';

    public const AUTH_REDIRECT_AFTER_LOGIN = 'auth_redirect_after_login';

    public const AUTH_REDIRECT_AFTER_REGISTER = 'auth_redirect_after_register';

    public const AUTH_LOGIN_PAGE_TITLE = 'auth_login_page_title';

    public const AUTH_LOGIN_PAGE_DESCRIPTION = 'auth_login_page_description';

    public const AUTH_REGISTER_PAGE_TITLE = 'auth_register_page_title';

    public const AUTH_REGISTER_PAGE_DESCRIPTION = 'auth_register_page_description';

    public const AUTH_SHOW_SOCIAL_LOGIN = 'auth_show_social_login';

    protected $fillable = [
        'option_name',
        'option_value',
        'autoload',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            // Clear config cache when a setting is saved
            Artisan::call('config:clear');
        });

        static::deleted(function ($setting) {
            // Clear config cache when a setting is deleted
            Artisan::call('config:clear');
        });
    }
}
