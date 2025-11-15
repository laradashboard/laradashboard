<?php

declare(strict_types=1);

namespace App\Enums;

enum ReceiverType: string
{
    case CONTACT = 'contact';
    case USER = 'user';
    case ANY_EMAIL = 'any_email';
    case CONTACT_GROUP = 'contact_group';
    case CONTACT_TAG = 'contact_tag';

    public function label(): string
    {
        return match($this) {
            self::CONTACT => 'Contact',
            self::USER => 'User',
            self::ANY_EMAIL => 'Any Email',
            self::CONTACT_GROUP => 'Contact Group',
            self::CONTACT_TAG => 'Contact Tag',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::CONTACT => 'Send to specific contacts from CRM',
            self::USER => 'Send to registered users',
            self::ANY_EMAIL => 'Send to any email address',
            self::CONTACT_GROUP => 'Send to contacts in a group',
            self::CONTACT_TAG => 'Send to contacts with specific tag',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
