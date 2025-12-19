<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class FileManager extends Component
{
    use WithFileUploads;

    public $model;
    public $fieldname;
    public $path;
    public $isDeletable = false;
    public $file;
    public $loading = false;
    public $files = [];
    public $attachmentModelClass = null;
    public $foreignKey = 'ticket_id';
    public $isCreateMode = false;
    public $tempFiles = [];
    public $isReadOnly = false;

    protected $listeners = ['refreshFiles' => 'loadFiles', 'clearTempFiles' => 'clearTempFiles'];

    public function updatedFile()
    {
        if ($this->file) {
            $this->upload();
        }
    }

    public function mount($model = null, $fieldname = 'attachments', $path = 'uploads', $isDeletable = false, $attachmentModelClass = null, $foreignKey = null, $isCreateMode = false)
    {
        $this->model = $model;
        $this->fieldname = $fieldname;
        $this->path = $path;
        $this->isDeletable = $isDeletable;
        $this->isCreateMode = $isCreateMode || ! $model || ! $model->exists;
        if ($attachmentModelClass) {
            $this->attachmentModelClass = $attachmentModelClass;
        }
        if ($foreignKey) {
            $this->foreignKey = $foreignKey;
        }
        $this->loadFiles();
    }

    public function loadFiles()
    {
        if ($this->isCreateMode) {
            $this->files = $this->tempFiles;
        } elseif ($this->attachmentModelClass && $this->model && $this->model->exists) {
            $attachmentModel = app($this->attachmentModelClass);
            $this->files = $attachmentModel->where($this->foreignKey, $this->model->id)
                ->orderByDesc('id')
                ->get()
                ->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->name,
                        'path' => $file->path,
                        'url' => asset('storage/' . $file->path),
                        'size' => $file->size,
                        'mime_type' => $file->mime_type,
                        'extension' => pathinfo($file->name, PATHINFO_EXTENSION),
                        'uploaded_at' => $file->created_at,
                    ];
                })->toArray();
        } elseif ($this->model && $this->model->exists) {
            $fieldValue = $this->model->{$this->fieldname};
            $this->files = is_array($fieldValue) ? $fieldValue : ($fieldValue ? [$fieldValue] : []);
        } else {
            $this->files = [];
        }
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

        if ($this->isCreateMode) {
            $fileInfo['id'] = 'temp_' . uniqid();
            $this->tempFiles[] = $fileInfo;
            $this->dispatch('filesUpdated', $this->tempFiles);
        } elseif ($this->attachmentModelClass && $this->model && $this->model->exists) {
            $attachmentModel = app($this->attachmentModelClass);
            $attachmentModel->create([
                $this->foreignKey => $this->model->id,
                'name' => $this->file->getClientOriginalName(),
                'path' => $storedPath,
                'size' => $this->file->getSize(),
                'mime_type' => $this->file->getMimeType(),
            ]);
        } elseif ($this->model && $this->model->exists) {
            $files = $this->model->{$this->fieldname} ?? [];
            $files[] = $fileInfo;
            $this->model->{$this->fieldname} = $files;
            $this->model->save();
        }

        $this->file = null;
        $this->loading = false;
        $this->loadFiles();
    }

    public function deleteFile($fileId)
    {
        if ($this->isDeletable) {
            if ($this->isCreateMode) {
                $this->tempFiles = array_filter($this->tempFiles, function ($file) use ($fileId) {
                    if ($file['id'] === $fileId) {
                        Storage::disk('public')->delete($file['path']);
                        return false;
                    }
                    return true;
                });
                $this->tempFiles = array_values($this->tempFiles);
                $this->dispatch('filesUpdated', $this->tempFiles);
            } elseif ($this->attachmentModelClass && $this->model && $this->model->exists) {
                $attachmentModel = app($this->attachmentModelClass);
                $attachment = $attachmentModel->where($this->foreignKey, $this->model->id)
                    ->where('id', $fileId)
                    ->first();
                if ($attachment) {
                    Storage::disk('public')->delete($attachment->path);
                    $attachment->delete();
                }
            } elseif ($this->model && $this->model->exists) {
                $files = $this->model->{$this->fieldname} ?? [];
                $files = array_filter($files, function ($file) use ($fileId) {
                    return $file['path'] !== $fileId;
                });
                $this->model->{$this->fieldname} = array_values($files);
                $this->model->save();
                Storage::disk('public')->delete($fileId);
            }
            $this->loadFiles();
        }
    }

    public function getTempFiles()
    {
        return $this->tempFiles;
    }

    public function clearTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            Storage::disk('public')->delete($file['path']);
        }
        $this->tempFiles = [];
        $this->loadFiles();
    }

    public function render()
    {
        return view('livewire.components.file-manager');
    }
}
