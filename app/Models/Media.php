<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\MediaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Wrapper for Spatie Media to avoid direct dependency in modules
 */
#[ObservedBy([MediaObserver::class])]
class Media extends SpatieMedia
{
    // Inherit all functionality from Spatie Media
    // Add any custom functionality here if needed.
}
