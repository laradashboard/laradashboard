<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Concerns\QueryBuilderTrait;
use Illuminate\Support\Str;

class EmailLog extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'uuid',
        'campaign_id',
        'template_id',
        'contact_id',
        'to_email',
        'to_name',
        'from_email',
        'from_name',
        'subject',
        'body_html',
        'headers',
        'message_id',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'failed_at',
        'failure_reason',
        'tracking_data',
        'provider',
        'provider_response',
        'sent_by',
    ];

    protected $casts = [
        'headers' => 'array',
        'tracking_data' => 'array',
        'provider_response' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'failed_at' => 'datetime',
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

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function wasSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'opened', 'clicked']);
    }

    public function wasDelivered(): bool
    {
        return in_array($this->status, ['delivered', 'opened', 'clicked']);
    }

    public function wasOpened(): bool
    {
        return in_array($this->status, ['opened', 'clicked']) || $this->opened_at !== null;
    }
    public function wasClicked(): bool
    {
        return $this->status === 'clicked' || $this->clicked_at !== null;
    }

    public function wasBounced(): bool
    {
        return $this->status === 'bounced';
    }

    public function wasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSent($query)
    {
        return $query->whereIn('status', ['sent', 'delivered', 'opened', 'clicked']);
    }

    public function scopeDelivered($query)
    {
        return $query->whereIn('status', ['delivered', 'opened', 'clicked']);
    }

    public function scopeOpened($query)
    {
        return $query->whereIn('status', ['opened', 'clicked'])
                    ->orWhereNotNull('opened_at');
    }

    public function scopeClicked($query)
    {
        return $query->where('status', 'clicked')
                    ->orWhereNotNull('clicked_at');
    }

    public function scopeBounced($query)
    {
        return $query->where('status', 'bounced');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
