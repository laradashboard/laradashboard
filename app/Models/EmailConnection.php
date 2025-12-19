<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use App\Services\EmailProviderRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailConnection extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'uuid',
        'name',
        'from_email',
        'from_name',
        'force_from_email',
        'force_from_name',
        'provider_type',
        'settings',
        'credentials',
        'is_active',
        'is_default',
        'priority',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'credentials' => 'encrypted:array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'force_from_email' => 'boolean',
        'force_from_name' => 'boolean',
        'priority' => 'integer',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('priority', 'asc')->orderBy('created_at', 'asc');
    }

    public function scopeByProvider(Builder $query, string $providerType): Builder
    {
        return $query->where('provider_type', $providerType);
    }

    public function getProviderLabelAttribute(): string
    {
        $label = EmailProviderRegistry::getLabel($this->provider_type);

        return $label ?? ucfirst(str_replace('_', ' ', $this->provider_type));
    }

    public function getProviderIconAttribute(): ?string
    {
        return EmailProviderRegistry::getIcon($this->provider_type);
    }

    public function getProviderColorAttribute(): ?string
    {
        return EmailProviderRegistry::getColor($this->provider_type);
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return __('Inactive');
        }

        if ($this->last_test_status === 'success') {
            return __('Connected');
        }

        if ($this->last_test_status === 'failed') {
            return __('Failed');
        }

        return __('Not Tested');
    }

    public function getStatusColorAttribute(): string
    {
        if (! $this->is_active) {
            return 'gray';
        }

        if ($this->last_test_status === 'success') {
            return 'green';
        }

        if ($this->last_test_status === 'failed') {
            return 'red';
        }

        return 'yellow';
    }

    public function markAsTested(bool $success, ?string $message = null): void
    {
        $this->update([
            'last_tested_at' => now(),
            'last_test_status' => $success ? 'success' : 'failed',
            'last_test_message' => $message,
        ]);
    }
}
