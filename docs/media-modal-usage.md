# Media Modal Component Usage

The Media Modal component provides a WordPress-like media library interface that can be used anywhere in your Blade templates.

## Basic Usage

```blade
<x-media-modal 
    id="myMediaModal"
    title="Select Media"
    :multiple="false"
    allowed-types="all"
    on-select="handleMediaSelect"
    button-text="Choose Media"
    button-class="btn-primary"
/>
```

## Component Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `id` | string | `'mediaModal'` | Unique identifier for the modal |
| `title` | string | `'Select Media'` | Modal title |
| `multiple` | boolean | `false` | Allow multiple file selection |
| `allowed-types` | string | `'all'` | File types: `'all'`, `'images'`, `'videos'`, `'documents'` |
| `on-select` | string | `null` | JavaScript callback function name |
| `button-text` | string | `'Select Media'` | Button text |
| `button-class` | string | `'btn-primary'` | CSS classes for the button |

## Examples

### Single Image Selection
```blade
<x-media-modal 
    id="featuredImage"
    title="Select Featured Image"
    :multiple="false"
    allowed-types="images"
    on-select="setFeaturedImage"
    button-text="Choose Image"
/>

<script>
function setFeaturedImage(files) {
    if (files.length > 0) {
        const file = files[0];
        console.log('Selected image:', file);
        // Handle the selected image
        document.getElementById('imagePreview').src = file.url;
        document.getElementById('imageId').value = file.id;
    }
}
</script>
```

### Multiple Files Selection
```blade
<x-media-modal 
    id="galleryImages"
    title="Select Gallery Images"
    :multiple="true"
    allowed-types="images"
    on-select="setGalleryImages"
    button-text="Add Images"
/>

<script>
function setGalleryImages(files) {
    console.log('Selected files:', files);
    // Handle multiple selected files
    const ids = files.map(file => file.id);
    document.getElementById('galleryIds').value = ids.join(',');
}
</script>
```

### Document Selection Only
```blade
<x-media-modal 
    id="documentPicker"
    title="Select Document"
    :multiple="false"
    allowed-types="documents"
    on-select="setDocument"
    button-text="Choose Document"
/>
```

## JavaScript Events

The component also dispatches a custom event that you can listen to:

```javascript
document.addEventListener('mediaSelected', function(event) {
    const { modalId, files, multiple } = event.detail;
    console.log('Media selected from modal:', modalId, files);
});
```

## File Object Structure

Each selected file object contains:

```javascript
{
    id: 123,
    name: "example.jpg",
    file_name: "hashed_filename.jpg",
    mime_type: "image/jpeg",
    size: 1024000,
    human_readable_size: "1.0 MB",
    url: "http://example.com/storage/media/hashed_filename.jpg",
    extension: "jpg",
    created_at: "2025-01-01 12:00:00"
}
```

## Features

- **WordPress-like Interface**: Familiar grid/list view with thumbnails
- **File Upload**: Drag & drop or click to upload new files
- **Search & Filter**: Search by filename and filter by file type
- **Multiple Selection**: Support for single or multiple file selection
- **File Type Filtering**: Restrict selection to specific file types
- **Responsive Design**: Works on desktop and mobile devices
- **Dark Mode Support**: Automatically adapts to your theme
- **Keyboard Navigation**: ESC key to close modal

## Styling

The component uses Tailwind CSS classes and follows the existing design system. You can customize the button appearance using the `button-class` property:

```blade
<x-media-modal 
    button-class="btn-secondary text-sm"
    button-text="Browse Files"
/>
```

## Integration with Forms

For form integration, use hidden inputs to store selected file IDs:

```blade
<form>
    <input type="hidden" id="selectedFileId" name="file_id" value="">
    
    <x-media-modal 
        id="fileSelector"
        on-select="updateFileInput"
        button-text="Select File"
    />
</form>

<script>
function updateFileInput(files) {
    if (files.length > 0) {
        document.getElementById('selectedFileId').value = files[0].id;
    }
}
</script>
```

## Requirements

- The component requires the media API endpoint (`/admin/media/api`) to be available
- User must have `media.view` permission
- JavaScript must be enabled
- Iconify icons library should be loaded