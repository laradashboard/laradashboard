<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\EmailStatus;
use App\Concerns\QueryBuilderTrait;
use Illuminate\Support\Str;

class EmailCampaignRecipient extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'uuid',
        'campaign_id',
        'contact_id',
        'email',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'failed_at',
        'unsubscribed_at',
        'failure_reason',
        'tracking_data',
        'open_count',
        'click_count',
    ];

    protected $casts = [
        'status' => EmailStatus::class,
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'failed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'tracking_data' => 'array',
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function wasOpened(): bool
    {
        return $this->status === EmailStatus::OPENED || $this->opened_at !== null;
    }

    public function wasClicked(): bool
    {
        return $this->status === EmailStatus::CLICKED || $this->clicked_at !== null;
    }

    public function wasBounced(): bool
    {
        return $this->status === EmailStatus::BOUNCED;
    }

    public function wasFailed(): bool
    {
        return $this->status === EmailStatus::FAILED;
    }

    public function markAsOpened(): void
    {
        if (! $this->wasOpened()) {
            $this->update([
                'status' => EmailStatus::OPENED,
                'opened_at' => now(),
                'open_count' => $this->open_count + 1,
            ]);
        } else {
            $this->increment('open_count');
        }
    }

    public function markAsClicked(): void
    {
        $this->update([
            'status' => EmailStatus::CLICKED,
            'clicked_at' => now(),
            'click_count' => $this->click_count + 1,
        ]);

        if (! $this->wasOpened()) {
            $this->update([
                'opened_at' => now(),
                'open_count' => $this->open_count + 1,
            ]);
        }
    }

    public function scopeByStatus($query, EmailStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }

    public function scopeClicked($query)
    {
        return $query->whereNotNull('clicked_at');
    }
}