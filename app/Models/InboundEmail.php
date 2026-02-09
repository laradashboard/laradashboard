<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class InboundEmail extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'inbound_email_connection_id',
        'message_id',
        'in_reply_to',
        'references',
        'from_email',
        'from_name',
        'to_email',
        'to_name',
        'cc',
        'subject',
        'email_date',
        'body_plain',
        'body_html',
        'body_parsed',
        'attachments',
        'status',
        'processing_error',
        'processed_at',
        'handler_type',
        'handler_model_type',
        'handler_model_id',
        'raw_headers',
        'imap_uid',
    ];

    protected $casts = [
        'cc' => 'array',
        'attachments' => 'array',
        'email_date' => 'datetime',
        'processed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(InboundEmailConnection::class, 'inbound_email_connection_id');
    }

    /**
     * Get the model that was created by handling this email.
     */
    public function handlerModel(): MorphTo
    {
        return $this->morphTo('handler_model', 'handler_model_type', 'handler_model_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByHandler(Builder $query, string $handlerType): Builder
    {
        return $query->where('handler_type', $handlerType);
    }

    public function scopeFromEmail(Builder $query, string $email): Builder
    {
        return $query->where('from_email', $email);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsProcessed(string $handlerType, ?string $modelType = null, ?int $modelId = null): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
            'handler_type' => $handlerType,
            'handler_model_type' => $modelType,
            'handler_model_id' => $modelId,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'processing_error' => $error,
        ]);
    }

    /**
     * Get the best available body content (parsed > plain > html stripped).
     */
    public function getBodyContent(): ?string
    {
        if (! empty($this->body_parsed)) {
            return $this->body_parsed;
        }

        if (! empty($this->body_plain)) {
            return $this->body_plain;
        }

        if (! empty($this->body_html)) {
            return strip_tags($this->body_html);
        }

        return null;
    }

    /**
     * Get references as an array.
     */
    public function getReferencesArray(): array
    {
        if (empty($this->references)) {
            return [];
        }

        // References header contains space-separated message IDs
        return preg_split('/\s+/', trim($this->references)) ?: [];
    }
}
