<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationType;

class NotificationTypeService
{
    public function getNotificationTypesDropdown(): array
    {
        return collect(NotificationType::getValues())
            ->map(function ($type) {
                return [
                    'label' => (new NotificationType())->label($type),
                    'value' => $type,
                ];
            })
            ->toArray();
    }
}
