<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;

class CompactFileUploader extends Component
{
    use WithFileUploads;

    public $model;
    public $fieldname = 'attachments';
    public $path = 'uploads';
    public $file;
    public $loading = false;

    public function updatedFile()
    {
        if ($this->file) {
            $this->upload();
        }
    }

    public function mount($model = null, $fieldname = 'attachments', $path = 'uploads')
    {
        $this->model = $model;
        $this->fieldname = $fieldname;
        $this->path = $path;
    }

    public function upload()
    {
        $this->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $this->loading = true;
        $filename = date('Ymd_His_') . $this->file->getClientOriginalName();
        $storedPath = $this->file->storeAs($this->path, $filename, 'public');

        $fileInfo = [
            'name' => $this->file->getClientOriginalName(),
            'path' => $storedPath,
            'url' => asset('storage/' . $storedPath),
            'size' => $this->file->getSize(),
            'mime_type' => $this->file->getMimeType(),
            'extension' => pathinfo($this->file->getClientOriginalName(), PATHINFO_EXTENSION),
            'uploaded_at' => now()->toISOString(),
        ];

        if ($this->model && $this->model->exists) {
            $files = $this->model->{$this->fieldname} ?? [];
            $files[] = $fileInfo;
            $this->model->{$this->fieldname} = $files;
            $this->model->save();
        }

        $this->file = null;
        $this->loading = false;

        // Refresh the page to show new attachment
        $this->redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.components.compact-file-uploader');
    }
}
