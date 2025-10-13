<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\Crm\Models\TicketAttachment;
use Illuminate\Support\Facades\URL;



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

    protected $listeners = ['refreshFiles' => 'loadFiles'];

    public function updatedFile()
    {
        if ($this->file) {
            $this->upload();
        }
    }

    public function mount($model, $fieldname, $path, $isDeletable = false, $attachmentModelClass = null, $foreignKey = null)
    {
        $this->model = $model;
        $this->fieldname = $fieldname;
        $this->path = $path;
        $this->isDeletable = $isDeletable;
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
        if ($this->attachmentModelClass) {
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
        } else {
            $fieldValue = $this->model->{$this->fieldname};
            $this->files = is_array($fieldValue) ? $fieldValue : ($fieldValue ? [$fieldValue] : []);
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

        if ($this->attachmentModelClass) {
            $attachmentModel = app($this->attachmentModelClass);
            $attachmentModel->create([
                $this->foreignKey => $this->model->id,
                'name' => $this->file->getClientOriginalName(),
                'path' => $storedPath,
                'size' => $this->file->getSize(),
                'mime_type' => $this->file->getMimeType(),
            ]);
        } else {
            $fileInfo = [
                'name' => $this->file->getClientOriginalName(),
                'path' => $storedPath,
                'size' => $this->file->getSize(),
                'mime_type' => $this->file->getMimeType(),
                'extension' => pathinfo($this->file->getClientOriginalName(), PATHINFO_EXTENSION),
                'uploaded_at' => now()->toISOString(),
            ];
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
            if ($this->attachmentModelClass) {
                $attachmentModel = app($this->attachmentModelClass);
                $attachment = $attachmentModel->where($this->foreignKey, $this->model->id)
                    ->where('id', $fileId)
                    ->first();
                if ($attachment) {
                    Storage::disk('public')->delete($attachment->path);
                    $attachment->delete();
                    $this->loadFiles();
                }
            } else {
                $files = $this->model->{$this->fieldname} ?? [];
                $files = array_filter($files, function ($file) use ($fileId) {
                    return $file['path'] !== $fileId;
                });
                $this->model->{$this->fieldname} = array_values($files);
                $this->model->save();
                Storage::disk('public')->delete($fileId);
                $this->loadFiles();
            }
        }
    }

    public function render()
    {
        return view('livewire.components.file-manager');
    }
}
