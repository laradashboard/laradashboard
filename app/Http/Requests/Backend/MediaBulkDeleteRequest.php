<?php

declare(strict_types=1);

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MediaBulkDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->can('media.delete');
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ];
    }
}
