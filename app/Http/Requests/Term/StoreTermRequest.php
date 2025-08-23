<?php

declare(strict_types=1);

namespace App\Http\Requests\Term;

use App\Http\Requests\FormRequest;
use App\Services\Content\ContentService;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreTermRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the controller using policies.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            /** @example "Technology" */
            'name' => 'required|string|max:255|unique:terms,name',

            /** @example "technology" */
            'slug' => 'nullable|string|max:255|unique:terms,slug',

            /** @example "Articles related to technology and software development." */
            'description' => 'nullable|string',

            /** @example null */
            'parent_id' => 'nullable|exists:terms,id',

            /** @example "post" */
            'post_type' => 'nullable|string',

            /** @example null */
            'post_id' => 'nullable|numeric',

            /** @example null */
            'remove_featured_image' => 'nullable',
        ];

        // Add featured image validation if taxonomy supports it
        $taxonomyName = $this->route('taxonomy');
        $taxonomyModel = app(ContentService::class)->getTaxonomies()->where('name', $taxonomyName)->first();

        if ($taxonomyModel && $taxonomyModel->show_featured_image) {
            /** @example null */
            $rules['featured_image'] = [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Allow either file upload or media ID.
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        // Validate as image file.
                        if (! in_array($value->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                            $fail('The featured image must be a valid image file (JPEG, PNG, GIF, or WebP).');
                        }
                        if ($value->getSize() > 2048 * 1024) { // 2MB in bytes
                            $fail('The featured image must not be larger than 2MB.');
                        }
                    } elseif (is_string($value)) {
                        // Validate as media ID or URL.
                        if (! is_numeric($value)) {
                            // If it's not numeric, check if it's a valid URL.
                            if (! filter_var($value, FILTER_VALIDATE_URL)) {
                                $fail('The featured image must be a valid media ID or URL.');
                            }
                        } else {
                            // If it's numeric, verify the media exists.
                            $mediaExists = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($value);
                            if (! $mediaExists) {
                                $fail('The selected media does not exist.');
                            }
                        }
                    }
                },
            ];
        }

        return ld_apply_filters('term.store.validation.rules', $rules, $taxonomyName);
    }
}
