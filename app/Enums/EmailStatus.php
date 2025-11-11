<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case OPENED = 'opened';
    case CLICKED = 'clicked';
    case BOUNCED = 'bounced';
    case FAILED = 'failed';
    case UNSUBSCRIBED = 'unsubscribed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent',
            self::DELIVERED => 'Delivered',
            self::OPENED => 'Opened',
            self::CLICKED => 'Clicked',
            self::BOUNCED => 'Bounced',
            self::FAILED => 'Failed',
            self::UNSUBSCRIBED => 'Unsubscribed',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING => 'badge-warning',
            self::SENT => 'badge-info',
            self::DELIVERED => 'badge-primary',
            self::OPENED => 'badge-success',
            self::CLICKED => 'badge-success',
            self::BOUNCED => 'badge-danger',
            self::FAILED => 'badge-danger',
            self::UNSUBSCRIBED => 'badge-secondary',
        };
    }
}
