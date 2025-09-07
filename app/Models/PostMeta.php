<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\PostMetaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([PostMetaObserver::class])]
class PostMeta extends Model
{
    use HasFactory;

    protected $table = 'post_meta';

    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value',
        'type',
        'default_value',
    ];

    /**
     * Get the post that owns the meta.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
