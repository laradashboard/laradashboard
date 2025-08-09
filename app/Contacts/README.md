# Media Library Feature

This directory contains interfaces for the application's media library feature, which is a wrapper around the Spatie Media Library package.

## Overview

The media library feature allows models across different modules to handle media (images, documents, etc.) in a consistent way without directly depending on the Spatie Media Library package. This abstraction layer makes it easier to maintain and potentially change the underlying implementation in the future.

## Components

The media library feature consists of the following components:

1. **MediaInterface** (app/Contacts/MediaInterface.php): An interface that models must implement to use the media library feature.
2. **HasMediaLibrary trait** (app/Traits/HasMediaLibrary.php): A trait that provides the implementation for the MediaInterface.
3. **MediaLibraryService** (app/Services/MediaLibraryService.php): A service class for handling media operations.
4. **MediaImage component** (app/View/Components/MediaImage.php): A Blade component for displaying media.

## Usage

### 1. Make a model use the media library feature

```php
<?php

namespace Modules\YourModule\Models;

use App\Contacts\MediaInterface;
use App\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class YourModel extends Model implements MediaInterface
{
    use HasMedia;

    // Define media collections and conversions
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('your_collection_name')
            ->singleFile(); // Optional: only allow one file
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // You can override the default conversions or add new ones
        $this->addMediaConversion('custom')
            ->width(300)
            ->height(300);
    }
}
```

### 2. Use the MediaLibraryService in your controller

```php
<?php

namespace Modules\YourModule\Http\Controllers;

use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Modules\YourModule\Models\YourModel;

class YourController extends Controller
{
    protected $mediaService;
    
    public function __construct(MediaLibraryService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function store(Request $request)
    {
        $model = YourModel::create($request->validated());

        // Upload a single file
        $this->mediaService->uploadFromRequest($model, $request, 'file_input_name', 'your_collection_name');

        // Upload multiple files
        $this->mediaService->uploadMultipleFromRequest($model, $request, 'files_input_name', 'your_collection_name');

        return redirect()->back();
    }

    public function update(Request $request, YourModel $model)
    {
        $model->update($request->validated());

        if ($request->hasFile('file_input_name')) {
            // Clear existing media
            $this->mediaService->clearMediaCollection($model, 'your_collection_name');
            
            // Upload new media
            $this->mediaService->uploadFromRequest($model, $request, 'file_input_name', 'your_collection_name');
        }

        return redirect()->back();
    }
}
```

### 3. Display media in your views

```blade
<x-media-image 
    :model="$model" 
    collection="your_collection_name" 
    conversion="thumb" 
    alt="Alt text" 
    class="rounded shadow" 
/>
```

### 4. Get media URLs in your code

```php
// Get the URL of the first media in a collection
$url = $model->getMediaUrl('your_collection_name');

// Get the URL of a specific conversion
$thumbUrl = $model->getMediaUrl('your_collection_name', 'thumb');

// Get all media URLs for a collection with all conversions
$allUrls = $model->getAllMediaUrls('your_collection_name');
```

## Default Conversions

The HasMediaLibrary trait defines the following default conversions:

- **thumb**: 200x200px
- **medium**: 500x500px
- **large**: 1000x1000px

You can override these in your model's `registerMediaConversions` method if needed.
