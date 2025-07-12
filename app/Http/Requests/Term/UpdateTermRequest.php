<?php

declare(strict_types=1);

namespace App\Http\Requests\Term;

use App\Http\Requests\FormRequest;
use App\Services\Content\ContentService;
use Illuminate\Support\Facades\Auth;

class UpdateTermRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->checkAuthorization(Auth::user(), ['term.edit']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $termId = $this->route('id');

        $rules = [
            /** @example "Web Development" */
            'name' => 'required|string|max:255|unique:terms,name,'.$termId,

            /** @example "web-development" */
            'slug' => 'nullable|string|max:255|unique:terms,slug,'.$termId,

            /** @example "Topics related to web development and programming." */
            'description' => 'nullable|string',

            /** @example null */
            'parent_id' => 'nullable|exists:terms,id',
        ];

        // Add featured image validation if taxonomy supports it.
        $taxonomyName = $this->route('taxonomy');
        $taxonomyModel = app(ContentService::class)->getTaxonomies()->where('name', $taxonomyName)->first();

        if ($taxonomyModel && $taxonomyModel->show_featured_image) {
            /** @example null */
            $rules['featured_image'] = 'nullable|image|max:2048';
        }

        return ld_apply_filters('term.update.validation.rules', $rules, $taxonomyName);
    }
}
