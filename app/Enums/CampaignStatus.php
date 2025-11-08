<?php

declare(strict_types=1);

namespace App\Enums;

enum CampaignStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case SENDING = 'sending';
    case SENT = 'sent';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::SENDING => 'Sending',
            self::SENT => 'Sent',
            self::PAUSED => 'Paused',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DRAFT => 'badge-secondary',
            self::SCHEDULED => 'badge-info',
            self::SENDING => 'badge-warning',
            self::SENT => 'badge-primary',
            self::PAUSED => 'badge-warning',
            self::COMPLETED => 'badge-success',
            self::CANCELLED => 'badge-danger',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => '#6b7280',
            self::SCHEDULED => '#3b82f6',
            self::SENDING => '#f59e0b',
            self::SENT => '#10b981',
            self::PAUSED => '#f97316',
            self::COMPLETED => '#059669',
            self::CANCELLED => '#ef4444',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DRAFT => 'bi bi-file-text',
            self::SCHEDULED => 'bi bi-clock',
            self::SENDING => 'bi bi-send',
            self::SENT => 'bi bi-check-circle',
            self::PAUSED => 'bi bi-pause-circle',
            self::COMPLETED => 'bi bi-check-circle-fill',
            self::CANCELLED => 'bi bi-x-circle',
        };
    }
}