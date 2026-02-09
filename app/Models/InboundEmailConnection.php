<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InboundEmailConnection extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'uuid',
        'name',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'imap_folder',
        'imap_validate_cert',
        'delete_after_processing',
        'mark_as_read',
        'fetch_limit',
        'polling_interval',
        'email_connection_id',
        'is_active',
        'last_checked_at',
        'last_check_status',
        'last_check_message',
        'emails_processed_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'imap_password' => 'encrypted',
        'imap_port' => 'integer',
        'imap_validate_cert' => 'boolean',
        'delete_after_processing' => 'boolean',
        'mark_as_read' => 'boolean',
        'fetch_limit' => 'integer',
        'polling_interval' => 'integer',
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
        'emails_processed_count' => 'integer',
    ];

    protected $hidden = [
        'imap_password',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function emailConnection(): BelongsTo
    {
        return $this->belongsTo(EmailConnection::class);
    }

    public function inboundEmails(): HasMany
    {
        return $this->hasMany(InboundEmail::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDueForPolling(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_checked_at')
                    ->orWhereRaw('last_checked_at < DATE_SUB(NOW(), INTERVAL polling_interval MINUTE)');
            });
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return __('Inactive');
        }

        if ($this->last_check_status === 'success') {
            return __('Connected');
        }

        if ($this->last_check_status === 'failed') {
            return __('Failed');
        }

        return __('Not Tested');
    }

    public function getStatusColorAttribute(): string
    {
        if (! $this->is_active) {
            return 'gray';
        }

        if ($this->last_check_status === 'success') {
            return 'green';
        }

        if ($this->last_check_status === 'failed') {
            return 'red';
        }

        return 'yellow';
    }

    public function markAsChecked(bool $success, ?string $message = null): void
    {
        $this->update([
            'last_checked_at' => now(),
            'last_check_status' => $success ? 'success' : 'failed',
            'last_check_message' => $message,
        ]);
    }

    public function incrementProcessedCount(int $count = 1): void
    {
        $this->increment('emails_processed_count', $count);
    }

    /**
     * Get the IMAP connection string for php-imap.
     */
    public function getImapConnectionString(): string
    {
        $encryption = match ($this->imap_encryption) {
            'ssl' => '/ssl',
            'tls' => '/tls',
            default => '',
        };

        $validateCert = $this->imap_validate_cert ? '' : '/novalidate-cert';

        return sprintf(
            '{%s:%d/imap%s%s}%s',
            $this->imap_host,
            $this->imap_port,
            $encryption,
            $validateCert,
            $this->imap_folder
        );
    }
}
