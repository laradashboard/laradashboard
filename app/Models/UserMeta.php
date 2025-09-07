<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\UserMetaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UserMetaObserver::class])]
class UserMeta extends Model
{
    protected $table = 'user_meta';

    protected $fillable = [
        'user_id',
        'meta_key',
        'meta_value',
        'type',
        'default_value',
    ];

    /**
     * Get the user that owns the meta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
