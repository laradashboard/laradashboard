<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Http\Requests\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->checkAuthorization(Auth::user(), ['post.edit']);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize meta keys by slugifying them
        if ($this->has('meta_keys')) {
            $metaKeys = $this->input('meta_keys', []);
            $sanitizedKeys = array_map(function ($key) {
                return ! empty($key) ? Str::slug($key, '_') : $key;
            }, $metaKeys);

            $this->merge([
                'meta_keys' => $sanitizedKeys,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $postId = $this->route('id');

        return ld_apply_filters('post.update.validation.rules', [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,'.$postId,
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'status' => 'required|in:draft,publish,pending,future,private',
            'featured_image' => 'nullable|file|image|max:5120',
            'parent_id' => 'nullable|exists:posts,id',
            'published_at' => 'nullable|date',
            'remove_featured_image' => 'nullable|boolean',
            'meta_keys.*' => 'nullable|string|max:255|regex:/^[a-z0-9_]+$/',
            'meta_values.*' => 'nullable|string',
            'meta_types.*' => 'nullable|string|in:input,textarea,number,email,url,text,date,checkbox,select',
            'meta_default_values.*' => 'nullable|string',
        ], $postId);
    }
}
