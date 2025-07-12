<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Http\Requests\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->checkAuthorization(Auth::user(), ['post.create']);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize meta keys by slugifying them
        if ($this->has('meta_keys')) {
            $metaKeys = $this->input('meta_keys', []);
            // Ensure $metaKeys is always an array
            $metaKeys = is_array($metaKeys) ? $metaKeys : [];
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
        return ld_apply_filters('post.store.validation.rules', [
            /** @example "How to Build a Laravel Application" */
            'title' => 'required|string|max:255',

            /** @example "how-to-build-a-laravel-application" */
            'slug' => 'nullable|string|max:255|unique:posts',

            /** @example "<p>This is a comprehensive guide to building Laravel applications...</p>" */
            'content' => 'nullable|string',

            /** @example "Learn the fundamentals of building Laravel applications from scratch." */
            'excerpt' => 'nullable|string',

            /** @example "publish" */
            'status' => 'required|in:draft,publish,pending,future,private',

            /** @example null */
            'featured_image' => 'nullable|file|image|max:5120',

            /** @example null */
            'parent_id' => 'nullable|exists:posts,id',

            /** @example null */
            'published_at' => 'nullable|date',

            /** @example "seo_keywords" */
            'meta_keys.*' => 'nullable|string|max:255|regex:/^[a-z0-9_]+$/',

            /** @example "laravel, php, web development" */
            'meta_values.*' => 'nullable|string',

            /** @example "textarea" */
            'meta_types.*' => 'nullable|string|in:input,textarea,number,email,url,text,date,checkbox,select',

            /** @example "laravel" */
            'meta_default_values.*' => 'nullable|string',
        ]);
    }
}
