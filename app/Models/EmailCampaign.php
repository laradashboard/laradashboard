<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\CampaignStatus;
use App\Concerns\QueryBuilderTrait;
use Illuminate\Support\Str;

class EmailCampaign extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'subject',
        'preheader',
        'body_html',
        'content_text',
        'template_id',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'failed_count',
        'unsubscribed_count',
        'from_email',
        'from_name',
        'reply_to_name',
        'reply_to_email',
        'settings',
        'filters',
        'track_opens',
        'track_clicks',
        'use_custom_from',
        'use_utm_parameters',
        'created_by',
        'updated_by',
        'contact_group_ids',
        'contact_tag_ids',
        'contact_group_excluded_ids',
        'contact_tag_excluded_ids',
        'campaign_source',
        'campaign_medium',
        'campaign_name',
        'campaign_term',
        'campaign_content',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'settings' => 'array',
        'filters' => 'array',
        'track_opens' => 'boolean',
        'track_clicks' => 'boolean',
        'use_custom_from' => 'boolean',
        'use_utm_parameters' => 'boolean',
        'contact_group_ids' => 'array',
        'contact_tag_ids' => 'array',
        'contact_group_excluded_ids' => 'array',
        'contact_tag_excluded_ids' => 'array',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }

    public function getDeliveryRateAttribute(): float
    {
        if ($this->sent_count == 0) {
            return 0;
        }

        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->delivered_count == 0) {
            return 0;
        }

        return round(($this->opened_count / $this->delivered_count) * 100, 2);
    }

    public function getClickRateAttribute(): float
    {
        if ($this->delivered_count == 0) {
            return 0;
        }

        return round(($this->clicked_count / $this->delivered_count) * 100, 2);
    }

    public function getBounceRateAttribute(): float
    {
        if ($this->sent_count == 0) {
            return 0;
        }

        return round(($this->bounced_count / $this->sent_count) * 100, 2);
    }

    public function canBeSent(): bool
    {
        return in_array($this->status, [CampaignStatus::DRAFT, CampaignStatus::SCHEDULED, CampaignStatus::PAUSED, CampaignStatus::COMPLETED]);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [CampaignStatus::DRAFT, CampaignStatus::SCHEDULED, CampaignStatus::PAUSED]);
    }

    public function scopeByStatus($query, CampaignStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', CampaignStatus::SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }
}