<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorNotificationLog extends Model
{
    protected $fillable = [
        'error_hash',
        'level',
        'message',
        'file',
        'line',
        'occurrences',
        'first_seen_at',
        'last_seen_at',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'notified_at' => 'datetime',
            'occurrences' => 'integer',
            'line' => 'integer',
        ];
    }
}
