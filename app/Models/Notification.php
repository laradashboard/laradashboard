<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ReceiverType;
use App\Services\NotificationTypeRegistry;
use App\Concerns\QueryBuilderTrait;
use App\Services\ReceiverTypeRegistry;
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
        'from_email',
        'from_name',
        'reply_to_email',
        'reply_to_name',
        'receiver_type',
        'receiver_ids',
        'receiver_emails',
        'is_active',
        'is_deleteable',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        // Keep receiver_type as string to allow module-registered values; use accessor methods for label/icon.
        'receiver_type' => 'string',
        'receiver_ids' => 'array',
        'receiver_emails' => 'array',
        'is_active' => 'boolean',
        'is_deleteable' => 'boolean',
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
        return NotificationTypeRegistry::getIcon($this->notification_type);
    }

    public function getNotificationTypeLabel()
    {
        $label = NotificationTypeRegistry::getLabel($this->notification_type);
        if ($label) {
            return $label;
        }

        return ucfirst(str_replace('_', ' ', $this->notification_type));
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

    public function scopeByReceiverType($query, string|ReceiverType $type)
    {
        $value = $type instanceof ReceiverType ? $type->value : (string) $type;
        return $query->where('receiver_type', $value);
    }

    /**
     * Get the human-readable receiver type label for a stored value.
     */
    public function getReceiverTypeLabelAttribute(): string
    {
        $value = $this->receiver_type ?? '';
        if (empty($value)) {
            return '';
        }
        // If it's a valid enum case, use the enum label
        $enum = ReceiverType::tryFrom($value);
        if ($enum) {
            return $enum->label();
        }
        // Fallback to registry metadata
        $label = ReceiverTypeRegistry::getLabel($value);
        if ($label) {
            return $label;
        }
        return ucfirst(str_replace('_', ' ', $value));
    }

    public function getReceiverTypeIconAttribute(): ?string
    {
        $value = $this->receiver_type ?? '';
        if (empty($value)) {
            return null;
        }
        // Use receiver type registry metadata for icon where possible.
        return ReceiverTypeRegistry::getIcon($value);
    }
}
