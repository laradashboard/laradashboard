<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case FORGOT_PASSWORD = 'forgot_password';
    case OTP_EMAIL = 'otp_email';
    case WELCOME_EMAIL = 'welcome_email';
    case ACCOUNT_VERIFICATION = 'account_verification';
    case PASSWORD_RESET = 'password_reset';
    case EMAIL_VERIFICATION = 'email_verification';
    case ORDER_CONFIRMATION = 'order_confirmation';
    case SUBSCRIPTION_CONFIRMATION = 'subscription_confirmation';
    case PAYMENT_RECEIPT = 'payment_receipt';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::FORGOT_PASSWORD => 'Forgot Password',
            self::OTP_EMAIL => 'OTP Email',
            self::WELCOME_EMAIL => 'Welcome Email',
            self::ACCOUNT_VERIFICATION => 'Account Verification',
            self::PASSWORD_RESET => 'Password Reset',
            self::EMAIL_VERIFICATION => 'Email Verification',
            self::ORDER_CONFIRMATION => 'Order Confirmation',
            self::SUBSCRIPTION_CONFIRMATION => 'Subscription Confirmation',
            self::PAYMENT_RECEIPT => 'Payment Receipt',
            self::CUSTOM => 'Custom Notification',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::FORGOT_PASSWORD => 'Email sent when user requests password reset',
            self::OTP_EMAIL => 'Email containing one-time password for authentication',
            self::WELCOME_EMAIL => 'Welcome email sent to new users',
            self::ACCOUNT_VERIFICATION => 'Email to verify user account',
            self::PASSWORD_RESET => 'Confirmation email after password reset',
            self::EMAIL_VERIFICATION => 'Email to verify email address',
            self::ORDER_CONFIRMATION => 'Email confirming order placement',
            self::SUBSCRIPTION_CONFIRMATION => 'Email confirming subscription',
            self::PAYMENT_RECEIPT => 'Receipt email for payments',
            self::CUSTOM => 'Custom notification for specific use case',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::FORGOT_PASSWORD => 'lucide:key',
            self::OTP_EMAIL => 'lucide:shield-check',
            self::WELCOME_EMAIL => 'lucide:smile',
            self::ACCOUNT_VERIFICATION => 'lucide:user-check',
            self::PASSWORD_RESET => 'lucide:lock',
            self::EMAIL_VERIFICATION => 'lucide:mail-check',
            self::ORDER_CONFIRMATION => 'lucide:shopping-cart',
            self::SUBSCRIPTION_CONFIRMATION => 'lucide:check-circle',
            self::PAYMENT_RECEIPT => 'lucide:receipt',
            self::CUSTOM => 'lucide:bell',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
