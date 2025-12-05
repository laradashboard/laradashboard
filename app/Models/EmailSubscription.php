<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EmailSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'subscribed',
        'unsubscribed_at',
        'unsubscribe_token',
    ];

    protected $casts = [
        'subscribed' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];

    public function generateUnsubscribeToken(): string
    {
        $token = Str::random(64);
        $this->update(['unsubscribe_token' => $token]);
        return $token;
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed;
    }

    public function unsubscribe(): bool
    {
        return $this->update([
            'subscribed' => false,
            'unsubscribed_at' => now(),
        ]);
    }

    public function resubscribe(): bool
    {
        return $this->update([
            'subscribed' => true,
            'unsubscribed_at' => null,
        ]);
    }

    public function scopeSubscribed($query)
    {
        return $query->where('subscribed', true);
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('subscribed', false);
    }
}
