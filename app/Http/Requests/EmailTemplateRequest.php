<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TemplateType;
use App\Models\Setting;
use App\Services\TemplateTypeRegistry;

class EmailTemplateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->can('manage', Setting::class);
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'subject' => $this->input('type') === 'header' || $this->input('type') === 'footer' ? 'nullable|string|max:500' : 'required|string|max:500',
            'body_html' => 'nullable|string',
            'type' => ['required', 'string', Rule::in(array_merge(TemplateType::getValues(), TemplateTypeRegistry::all()))],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];

        // For update requests, make name unique except for current record
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // Get the template ID from the route parameter
            $templateId = $this->route('email_template');

            if ($templateId) {
                $rules['name'] = 'required|string|max:255|unique:email_templates,name,' . $templateId;
            }
        } else {
            $rules['name'] = 'required|string|max:255|unique:email_templates,name';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Template name is required.',
            'name.unique' => 'A template with this name already exists.',
            'subject.required' => 'Email subject is required for email templates.',
            'type.required' => 'Template type is required.',
            'type.in' => 'Invalid template type selected.',
            'body_html.required_without' => 'Either HTML body or text body is required.',
        ];
    }

    protected function prepareForValidation()
    {
        // Ensure at least one body content is provided
        if (empty($this->body_html)) {
            $this->merge([
                'body_html' => $this->body_html ?: '',
            ]);
        }

        // Convert boolean strings to actual booleans
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
