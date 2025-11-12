<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TemplateType;

class EmailTemplateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->can('manage', \App\Models\Setting::class);
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body_html' => 'nullable|string',
            'body_text' => 'nullable|string',
            'type' => ['required', 'string', Rule::in(TemplateType::getValues())],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_template_id' => 'nullable|exists:email_templates,id',
            'footer_template_id' => 'nullable|exists:email_templates,id',
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
            'subject.required' => 'Email subject is required.',
            'type.required' => 'Template type is required.',
            'type.in' => 'Invalid template type selected.',
            'body_html.required_without' => 'Either HTML body or text body is required.',
            'body_text.required_without' => 'Either HTML body or text body is required.',
            'preview_image.image' => 'Preview image must be a valid image file.',
            'preview_image.mimes' => 'Preview image must be a JPEG, PNG, JPG, or GIF file.',
            'preview_image.max' => 'Preview image must not exceed 2MB.',
        ];
    }

    protected function prepareForValidation()
    {
        // Ensure at least one body content is provided
        if (empty($this->body_html) && empty($this->body_text)) {
            $this->merge([
                'body_html' => $this->body_html ?: '',
                'body_text' => $this->body_text ?: '',
            ]);
        }

        // Convert boolean strings to actual booleans and empty strings to null
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'is_default' => $this->boolean('is_default', false),
            'header_template_id' => $this->header_template_id ?: null,
            'footer_template_id' => $this->footer_template_id ?: null,
        ]);
    }
}
