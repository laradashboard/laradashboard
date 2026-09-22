<?php

declare(strict_types=1);

namespace App\Http\Requests\Mcp;

use App\Services\MediaLibraryService;
use Illuminate\Foundation\Http\FormRequest;

class StoreMcpMediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKilobytes = (int) ceil(MediaLibraryService::MCP_MAX_UPLOAD_BYTES / 1024);

        return [
            'file' => ['required', 'file', 'max:'.$maxKilobytes],
            'upload_token' => ['nullable', 'string', 'uuid'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:500'],
        ];
    }
}
