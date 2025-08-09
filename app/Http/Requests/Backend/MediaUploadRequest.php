<?php

declare(strict_types=1);

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->can(abilities: 'media.create');
    }

    public function rules(): array
    {
        return [
            'files.*' => 'required|file|max:10240',
        ];
    }
}
