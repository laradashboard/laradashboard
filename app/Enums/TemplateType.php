<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\TemplateTypeRegistry;

enum TemplateType: string
{
    case EMAIL = 'email';
    case HEADER = 'header';
    case FOOTER = 'footer';
    case WELCOME = 'welcome';
    case FOLLOW_UP = 'follow_up';
    case NEWSLETTER = 'newsletter';
    case PROMOTIONAL = 'promotional';
    case TRANSACTIONAL = 'transactional';
    case REMINDER = 'reminder';
    case AUTHENTICATION = 'authentication';
    case NOTIFICATION = 'notification';

    public function label(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'Authentication',
            self::EMAIL => 'Email',
            self::HEADER => 'Email Header',
            self::FOOTER => 'Email Footer',
            self::WELCOME => 'Welcome',
            self::FOLLOW_UP => 'Follow Up',
            self::NEWSLETTER => 'Newsletter',
            self::PROMOTIONAL => 'Promotional',
            self::TRANSACTIONAL => 'Transactional',
            self::REMINDER => 'Reminder',
            self::NOTIFICATION => 'Notification',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AUTHENTICATION => '#14b8a6',
            self::EMAIL => '#6b7280',
            self::HEADER => '#059669',
            self::FOOTER => '#dc2626',
            self::WELCOME => '#10b981',
            self::FOLLOW_UP => '#f59e0b',
            self::NEWSLETTER => '#3b82f6',
            self::PROMOTIONAL => '#ef4444',
            self::TRANSACTIONAL => '#8b5cf6',
            self::REMINDER => '#f97316',
            self::NOTIFICATION => '#6366f1',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'bi bi-shield-lock',
            self::EMAIL => 'bi bi-envelope',
            self::HEADER => 'bi bi-layout-text-window-reverse',
            self::FOOTER => 'bi bi-layout-text-window',
            self::WELCOME => 'bi bi-hand-thumbs-up',
            self::FOLLOW_UP => 'bi bi-arrow-repeat',
            self::NEWSLETTER => 'bi bi-newspaper',
            self::PROMOTIONAL => 'bi bi-megaphone',
            self::TRANSACTIONAL => 'bi bi-receipt',
            self::REMINDER => 'bi bi-bell',
            self::NOTIFICATION => 'bi bi-bell-fill',
        };
    }

    public static function getValues(): array
    {
        // Register base enum cases into registry and return all registered types
        TemplateTypeRegistry::registerMany(array_map(fn ($c) => ['type' => $c->value, 'meta' => ['label' => fn () => $c->label(), 'icon' => fn () => $c->icon(), 'color' => fn () => $c->color()]], self::cases()));
        return TemplateTypeRegistry::all();
    }
}
