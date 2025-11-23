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
use App\Services\EmailManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailTemplatesController extends Controller
{
    public function __construct(
        private readonly EmailTemplateService $emailTemplateService,
        private readonly EmailManager $emailManager
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Email Templates'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.email-templates.index');
    }

    public function create(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $templateTypes = collect(TemplateType::cases())
            ->mapWithKeys(function ($type) {
                return [$type->value => (string) $type->label()];
            })
            ->toArray();

        $this->setBreadcrumbTitle(__('Create Template'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Email Templates'), route('admin.email-templates.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.email-templates.create', [
            'templateTypes' => $templateTypes,
            'availableTemplates' => $this->emailTemplateService->getAllTemplates(),
            'headerTemplates' => $this->emailTemplateService->getTemplatesByType(TemplateType::HEADER),
            'footerTemplates' => $this->emailTemplateService->getTemplatesByType(TemplateType::FOOTER),
            'templateVariables' => $this->emailManager->getAvailableVariables(),
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

    public function show(EmailTemplate $emailTemplate): Renderable
    {
        $this->authorize('manage', Setting::class);

        $rendered = $this->emailTemplateService->renderTemplate($emailTemplate, $this->emailManager->getPreviewSampleData());
        $emailTemplate->subject = $rendered['subject'];
        $emailTemplate->body_html = $rendered['body_html'];

        $this->setBreadcrumbTitle(__('View Template'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Email Templates'), route('admin.email-templates.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.email-templates.show', compact('emailTemplate'));
    }

    public function edit(EmailTemplate $emailTemplate): Renderable
    {
        $this->authorize('manage', Setting::class);
        $templateTypes = collect(TemplateType::cases())
            ->mapWithKeys(function ($type) {
                return [$type->value => (string) $type->label()];
            })
            ->toArray();

        $availableTemplates = $this->emailTemplateService->getAllTemplatesExcept($emailTemplate->id);
        $headerTemplates = $this->emailTemplateService->getAllTemplatesExcept($emailTemplate->id);
        $footerTemplates = $this->emailTemplateService->getAllTemplatesExcept($emailTemplate->id);

        $this->setBreadcrumbTitle(__('Edit Template'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Email Templates'), route('admin.email-templates.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.email-templates.edit', [
            'emailTemplate' => $emailTemplate,
            'templateTypes' => $templateTypes,
            'selectedType' => $emailTemplate->type->value,
            'availableTemplates' => $availableTemplates,
            'headerTemplates' => $headerTemplates,
            'footerTemplates' => $footerTemplates,
            'templateVariables' => $this->emailManager->getAvailableVariables(),
        ]);
    }

    public function update(EmailTemplateRequest $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        try {
            $this->emailTemplateService->updateTemplate($emailTemplate, $request->validated());

            return redirect()
                ->route('admin.email-templates.index')
                ->with('success', __('Email template updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Failed to update email template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to update email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        try {
            $this->emailTemplateService->deleteTemplate($emailTemplate);

            return redirect()
                ->route('admin.email-templates.index')
                ->with('success', __('Email template deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Failed to delete email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function duplicate(int $email_template, Request $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($email_template);

        if (! $template) {
            abort(404, __('Email template not found.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $newTemplate = $this->emailTemplateService->duplicateTemplate($template, $request->input('name'));

            return redirect()
                ->route('admin.email-templates.show', $newTemplate->id)
                ->with('success', __('Email template duplicated successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Failed to duplicate email template: :error', ['error' => $e->getMessage()]));
        }
    }

    public function sendTestEmail(int $email_template, Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $template = $this->emailTemplateService->getTemplateById($email_template);

        if (! $template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $rendered = $this->emailTemplateService->renderTemplate($template, $this->emailManager->getPreviewSampleData());

            Mail::send([], [], function ($message) use ($rendered, $request) {
                $message->to($request->input('email'))
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($rendered['subject'])
                    ->html($rendered['body_html']);
            });

            $this->logAction('Test Email Template Sent', $email_template, [
                'to' => $request->input('email'),
            ]);

            return response()->json(['message' => __('Test email sent successfully')]);
        } catch (\Exception $e) {
            $this->logAction('Failed to Send Test Email Template', $email_template, [
                'to' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
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
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getContent(int $email_template): JsonResponse
    {
        $this->authorize('manage', Setting::class);
        try {
            $template = $this->emailTemplateService->getTemplateById($email_template);

            if (! $template) {
                return response()->json(['error' => 'Template not found'], 404);
            }

            // Load header and footer templates
            $template->load(['headerTemplate', 'footerTemplate']);

            // Combine header, body, and footer content
            $combinedHtml = '';

            if ($template->headerTemplate) {
                $combinedHtml .= $template->headerTemplate->body_html;
            }

            $combinedHtml .= $template->body_html;

            if ($template->footerTemplate) {
                $combinedHtml .= $template->footerTemplate->body_html;
            }

            return response()->json([
                'subject' => $template->subject,
                'body_html' => $combinedHtml,
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
            'header_template_id' => 'nullable|exists:email_templates,id',
            'footer_template_id' => 'nullable|exists:email_templates,id',
        ]);

        try {
            // Create a temporary template object for rendering
            $tempTemplate = new EmailTemplate([
                'subject' => $request->input('subject'),
                'body_html' => $request->input('body_html', ''),
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

            $rendered = $tempTemplate->renderTemplate($this->emailManager->getPreviewSampleData());

            return response()->json([
                'subject' => $rendered['subject'],
                'body_html' => $rendered['body_html'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
