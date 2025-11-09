<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\EmailTemplateService;
use App\Http\Requests\EmailTemplateRequest;
use App\Enums\TemplateType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmailTemplatesController extends Controller
{
    public function __construct(
        private readonly EmailTemplateService $emailTemplateService
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', \App\Models\Setting::class);

        return view('backend.pages.email-templates.index', [
            'breadcrumbs' => [
                'title' => __('Email Templates'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                ],
            ],
        ]);
    }

    public function create(): Renderable
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $templateTypes = collect(TemplateType::cases())
            ->mapWithKeys(function ($type) {
                return [$type->value => (string) $type->label()];
            })
            ->toArray();

        $availableTemplates = $this->emailTemplateService->getAllTemplates();
        $headerTemplates = $this->emailTemplateService->getTemplatesByType(TemplateType::HEADER);
        $footerTemplates = $this->emailTemplateService->getTemplatesByType(TemplateType::FOOTER);

        return view('backend.pages.email-templates.create', [
            'templateTypes' => $templateTypes,
            'availableTemplates' => $availableTemplates,
            'headerTemplates' => $headerTemplates,
            'footerTemplates' => $footerTemplates,
            'breadcrumbs' => [
                'title' => __('Create Email Template'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Email Templates'), 'url' => route('admin.email-templates.index')],
                ],
            ],
        ]);
    }

    public function store(EmailTemplateRequest $request): RedirectResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        try {
            $template = $this->emailTemplateService->createTemplate($request->validated());

            return redirect()
                ->route('admin.email-templates.show', $template->uuid)
                ->with('success', __('Email template created successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to create email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function show(string $uuid): Renderable
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $template = $this->emailTemplateService->getTemplateByUuid($uuid);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        return view('backend.pages.email-templates.show', [
            'template' => $template,
            'breadcrumbs' => [
                'title' => $template->name,
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Email Templates'), 'url' => route('admin.email-templates.index')],
                ],
            ],
        ]);
    }

    public function edit(string $id): Renderable
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $template = $this->emailTemplateService->getTemplateById((int) $id);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        $templateTypes = collect(TemplateType::cases())
            ->mapWithKeys(function ($type) {
                return [$type->value => (string) $type->label()];
            })
            ->toArray();

        $availableTemplates = $this->emailTemplateService->getAllTemplatesExcept($template->id);
        $headerTemplates = $this->emailTemplateService->getAllTemplatesExcept($template->id);
        $footerTemplates = $this->emailTemplateService->getAllTemplatesExcept($template->id);

        return view('backend.pages.email-templates.edit', [
            'template' => $template,
            'templateTypes' => $templateTypes,
            'selectedType' => $template->type->value,
            'availableTemplates' => $availableTemplates,
            'headerTemplates' => $headerTemplates,
            'footerTemplates' => $footerTemplates,
            'breadcrumbs' => [
                'title' => __('Edit Email Template'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Email Templates'), 'url' => route('admin.email-templates.index')],
                ],
            ],
        ]);
    }

    public function update(EmailTemplateRequest $request, string $uuid): RedirectResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $template = $this->emailTemplateService->getTemplateByUuid($uuid);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        try {
            $this->emailTemplateService->updateTemplate($template, $request->validated());

            return redirect()
                ->route('admin.email-templates.index')
                ->with('success', __('Email template updated successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to update email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $template = $this->emailTemplateService->getTemplateByUuid($uuid);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        try {
            $this->emailTemplateService->deleteTemplate($template);

            return redirect()
                ->route('admin.email-templates.index')
                ->with('success', __('Email template deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Failed to delete email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function duplicate(string $uuid, Request $request): RedirectResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $template = $this->emailTemplateService->getTemplateByUuid($uuid);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $newTemplate = $this->emailTemplateService->duplicateTemplate($template, $request->input('name'));

            return redirect()
                ->route('admin.email-templates.show', $newTemplate->uuid)
                ->with('success', __('Email template duplicated successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Failed to duplicate email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function setDefault(string $uuid): RedirectResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $template = $this->emailTemplateService->getTemplateByUuid($uuid);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        try {
            $this->emailTemplateService->setAsDefault($template);

            return redirect()
                ->back()
                ->with('success', __('Email template set as default successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Failed to set email template as default: :error', ['error' => $e->getMessage()]));
        }
    }

    public function preview(string $uuid): JsonResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $template = $this->emailTemplateService->getTemplateByUuid($uuid);

        if (! $template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $sampleData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1 (555) 123-4567',
            'company' => 'Acme Corporation',
            'job_title' => 'Marketing Manager',
            'dob' => '1980-01-15',
            'industry' => 'Technology',
            'website' => 'www.example.com',
        ];

        $rendered = $this->emailTemplateService->renderTemplate($template, $sampleData);

        return response()->json([
            'subject' => $rendered['subject'],
            'body_html' => $rendered['body_html'],
            'body_text' => $rendered['body_text'],
        ]);
    }

    public function uploadPreview(string $uuid, Request $request): JsonResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $template = $this->emailTemplateService->getTemplateByUuid($uuid);

        if (! $template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $request->validate([
            'preview_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $path = $this->emailTemplateService->uploadPreviewImage($template, $request->file('preview_image'));

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getByType(string $type): JsonResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        try {
            $templateType = TemplateType::from($type);
            $templates = $this->emailTemplateService->getTemplatesByType($templateType);

            return response()->json([
                'templates' => $templates->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'uuid' => $template->uuid,
                        'name' => $template->name,
                        'subject' => $template->subject,
                        'is_default' => $template->is_default,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getContent(string $id): JsonResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        try {
            $template = $this->emailTemplateService->getTemplateById((int) $id);

            if (! $template) {
                return response()->json(['error' => 'Template not found'], 404);
            }

            return response()->json([
                'subject' => $template->subject,
                'body_html' => $template->body_html,
                'body_text' => $template->body_text,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}