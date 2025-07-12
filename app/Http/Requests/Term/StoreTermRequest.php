<?php

declare(strict_types=1);

namespace App\Http\Requests\Term;

use App\Http\Requests\FormRequest;
use App\Services\Content\ContentService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class StoreTermRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->checkAuthorization(Auth::user(), ['term.create']);
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
        ];

        // Add featured image validation if taxonomy supports it
        $taxonomyName = $this->route('taxonomy');
        $taxonomyModel = app(ContentService::class)->getTaxonomies()->where('name', $taxonomyName)->first();

        if ($taxonomyModel && $taxonomyModel->show_featured_image) {
            /** @example null */
            $rules['featured_image'] = 'nullable|image|max:2048';
        }

        return ld_apply_filters('term.store.validation.rules', $rules, $taxonomyName);
    }
}
