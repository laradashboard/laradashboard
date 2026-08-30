<?php

declare(strict_types=1);

namespace App\Http\Requests\Backend;

use App\Support\Helper\MediaHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->can('media.create');
    }

    public function rules(): array
    {
        $limits = MediaHelper::getUploadLimits();
        $maxFileSizeKb = (int) floor($limits['effective_max_filesize'] / 1024);

        return [
            'files' => 'required|array|max:' . $limits['max_file_uploads'],
            'files.*' => [
                'required',
                File::types(MediaHelper::getAllowedMimeTypes())
                    ->extensions(MediaHelper::getAllowedExtensions())
                    ->max($maxFileSizeKb),
            ],
        ];
    }

    public function messages(): array
    {
        $limits = MediaHelper::getUploadLimits();

        return [
            'files.required' => __('Please select at least one file to upload.'),
            'files.max' => __('You can upload a maximum of :max files at once.', ['max' => $limits['max_file_uploads']]),
            'files.*.required' => __('Each file is required.'),
            'files.*.file' => __('Each upload must be a valid file.'),
            'files.*.max' => __('Each file cannot exceed :max. Current PHP limit: :limit', [
                'max' => $limits['effective_max_filesize_formatted'],
                'limit' => $limits['effective_max_filesize_formatted'],
            ]),
            'files.*.extensions' => __('This file type is not allowed.'),
            'files.*.mimetypes' => __('This file type is not allowed.'),
            'files.*.mimes' => __('This file type is not allowed.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $phpError = MediaHelper::checkPhpUploadError();
        if ($phpError) {
            $this->getValidatorInstance()->after(function ($validator) use ($phpError) {
                $validator->errors()->add('php_upload_limit', $phpError['message']);
            });
        }
    }
}
