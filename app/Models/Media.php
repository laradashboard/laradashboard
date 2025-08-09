<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Wrapper for Spatie Media to avoid direct dependency in modules
 */
class Media extends SpatieMedia
{
    // Inherit all functionality from Spatie Media
    // Add any custom functionality here if needed
}
