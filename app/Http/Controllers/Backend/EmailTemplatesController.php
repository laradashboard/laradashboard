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
use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\SettingService;
use App\Services\EnvWriter;
use App\Enums\ActionType;

class EmailTemplatesController extends Controller
{
    public function __construct(
        private readonly EmailTemplateService $emailTemplateService,
        private readonly SettingService $settingService,
        private readonly EnvWriter $envWriter,
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', Setting::class);

        return view('backend.pages.email-templates.index', [
            'breadcrumbs' => [
                'title' => __('Emails'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                ],
            ],
        ]);
    }

    public function create(): Renderable
    {
        $this->authorize('manage', Setting::class);

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
            'templateVariables' => \App\Models\EmailTemplate::getAvailableVariables(),
            'breadcrumbs' => [
                'title' => __('Create Template'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Emails'), 'url' => route('admin.email-templates.index')],
                ],
            ],
        ]);
    }

    public function store(EmailTemplateRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('manage', Setting::class);

        try {
            $template = $this->emailTemplateService->createTemplate($request->validated());

            // If it's an AJAX request (for save and preview), return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'uuid' => $template->uuid,
                    'message' => __('Email template created successfully.'),
                ]);
            }

            return redirect()
                ->route('admin.email-templates.show', $template->id)
                ->with('success', __('Email template created successfully.'));
        } catch (\Exception $e) {
            // If it's an AJAX request, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to create email template: :error', ['error' => $e->getMessage()]),
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to create email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function show(int $id): Renderable
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($id);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        return view('backend.pages.email-templates.show', [
            'template' => $template,
            'breadcrumbs' => [
                'title' => $template->name,
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Emails'), 'url' => route('admin.email-templates.index')],
                ],
            ],
        ]);
    }

    public function edit(string $id): Renderable
    {
        $this->authorize('manage', Setting::class);

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
            'templateVariables' => \App\Models\EmailTemplate::getAvailableVariables(),
            'breadcrumbs' => [
                'title' => __('Edit Template'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Emails'), 'url' => route('admin.email-templates.index')],
                ],
            ],
        ]);
    }

    public function update(EmailTemplateRequest $request, string $id): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById((int) $id);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        try {
            $this->emailTemplateService->updateTemplate($template, $request->validated());

            return redirect()
                ->route('admin.email-templates.index')
                ->with('success', __('Email template updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Failed to update email template', [
                'template_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to update email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($id);

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

    public function duplicate(int $id, Request $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($id);

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

    public function setDefault(int $id): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($id);

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

    public function preview(int $id): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($id);

        if (! $template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        // Load header and footer templates
        $template->load(['headerTemplate', 'footerTemplate']);

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

    public function previewPage(int $id): Renderable
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($id);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        // Load header and footer templates
        $template->load(['headerTemplate', 'footerTemplate']);

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

        return view('backend.pages.email-templates.preview', [
            'template' => $template,
            'rendered' => $rendered,
            'breadcrumbs' => [
                'title' => __('Preview Template'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Emails'), 'url' => route('admin.email-templates.index')],
                    ['label' => $template->name, 'url' => route('admin.email-templates.edit', $template->id)],
                ],
            ],
        ]);
    }

    public function sendTestEmail(int $id, Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($id);

        if (! $template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Log::info('Sending test email', ['id' => $id, 'email' => $request->input('email')]);

            $sampleData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'full_name' => 'John Doe',
                'email' => $request->input('email'),
                'phone' => '+1 (555) 123-4567',
                'company' => 'Acme Corporation',
                'job_title' => 'Marketing Manager',
                'dob' => '1980-01-15',
                'industry' => 'Technology',
                'website' => 'www.example.com',
            ];

            $rendered = $this->emailTemplateService->renderTemplate($template, $sampleData);
            Log::info('Template rendered', ['subject' => $rendered['subject']]);

            Mail::send([], [], function ($message) use ($rendered, $request) {
                $message->to($request->input('email'))
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($rendered['subject'])
                    ->html($rendered['body_html']);
            });

            Log::info('Email sent successfully');

            return response()->json(['message' => 'Test email sent successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to send test email', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }

    public function uploadPreview(int $id, Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($id);

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
        $this->authorize('manage', Setting::class);

        try {
            $templateType = TemplateType::from($type);
            $templates = $this->emailTemplateService->getTemplatesByType($templateType);

            return response()->json([
                'templates' => $templates->map(function (EmailTemplate $template) {
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

    public function getContent(int $id): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        try {
            $template = $this->emailTemplateService->getTemplateById($id);

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

    public function livePreview(Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'subject' => 'required|string',
            'body_html' => 'nullable|string',
            'body_text' => 'nullable|string',
            'header_template_id' => 'nullable|exists:email_templates,id',
            'footer_template_id' => 'nullable|exists:email_templates,id',
        ]);

        try {
            // Create a temporary template object for rendering
            $tempTemplate = new EmailTemplate([
                'subject' => $request->input('subject'),
                'body_html' => $request->input('body_html', ''),
                'body_text' => $request->input('body_text', ''),
                'header_template_id' => $request->input('header_template_id'),
                'footer_template_id' => $request->input('footer_template_id'),
            ]);

            // Load header and footer templates if specified
            if ($tempTemplate->header_template_id) {
                $headerTemplate = EmailTemplate::find($tempTemplate->header_template_id);
                $tempTemplate->setRelation('headerTemplate', $headerTemplate);
            }

            if ($tempTemplate->footer_template_id) {
                $footerTemplate = EmailTemplate::find($tempTemplate->footer_template_id);
                $tempTemplate->setRelation('footerTemplate', $footerTemplate);
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

            $rendered = $tempTemplate->renderTemplate($sampleData);

            return response()->json([
                'subject' => $rendered['subject'],
                'body_html' => $rendered['body_html'],
                'body_text' => $rendered['body_text'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateEmailSettings(Request $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'email_from_email' => 'required|email',
            'email_from_name' => 'required|string|max:255',
            'email_reply_to_email' => 'nullable|email',
            'email_reply_to_name' => 'nullable|string|max:255',
            'email_utm_source_default' => 'nullable|string|max:255',
            'email_utm_medium_default' => 'nullable|string|max:255',
            'email_rate_limit_per_hour' => 'nullable|integer|min:1|max:10000',
            'email_delay_seconds' => 'nullable|integer|min:0|max:60',
        ]);

        $fields = $request->only([
            'email_from_email',
            'email_from_name',
            'email_reply_to_email',
            'email_reply_to_name',
            'email_utm_source_default',
            'email_utm_medium_default',
            'email_rate_limit_per_hour',
            'email_delay_seconds',
        ]);

        foreach ($fields as $fieldName => $fieldValue) {
            $this->settingService->addSetting($fieldName, $fieldValue);
        }

        $this->envWriter->batchWriteKeysToEnvFile($fields);

        $this->storeActionLog(ActionType::UPDATED, [
            'email_settings' => $fields,
        ]);

        return redirect()->back()->with('success', __('Email settings updated successfully.'));
    }
}
