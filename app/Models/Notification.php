<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ReceiverType;
use App\Concerns\QueryBuilderTrait;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'notification_type',
        'email_template_id',
        'body_html',
        'receiver_type',
        'receiver_ids',
        'receiver_emails',
        'from_email',
        'from_name',
        'reply_to_email',
        'reply_to_name',
        'is_active',
        'is_deleteable',
        'track_opens',
        'track_clicks',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'receiver_type' => ReceiverType::class,
        'receiver_ids' => 'array',
        'receiver_emails' => 'array',
        'is_active' => 'boolean',
        'is_deleteable' => 'boolean',
        'track_opens' => 'boolean',
        'track_clicks' => 'boolean',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function getNotificationTypeIcon()
    {
        return (new NotificationType())->icon($this->notification_type);
    }

    public function getNotificationTypeLabel()
    {
        return (new NotificationType())->label($this->notification_type);
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public function scopeByReceiverType($query, ReceiverType $type)
    {
        return $query->where('receiver_type', $type);
    }
}
