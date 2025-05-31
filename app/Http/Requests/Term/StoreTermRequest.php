<?php

declare(strict_types=1);

namespace App\Http\Requests\Term;

use App\Http\Requests\FormRequest;
use App\Services\Content\ContentService;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:terms,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:terms,id',
        ];

        // Add featured image validation if taxonomy supports it
        $taxonomyName = $this->route('taxonomy');
        $taxonomyModel = app(ContentService::class)->getTaxonomies()->where('name', $taxonomyName)->first();

        if ($taxonomyModel && $taxonomyModel->show_featured_image) {
            $rules['featured_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return ld_apply_filters('term.store.validation.rules', $rules, $taxonomyName);
    }
}
