<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\Emails\EmailTemplateService;
use App\Http\Requests\EmailTemplateRequest;
use App\Enums\TemplateType;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Services\Emails\EmailVariable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailTemplatesController extends Controller
{
    public function __construct(
        private readonly EmailTemplateService $emailTemplateService,
        private readonly EmailVariable $emailVariable,
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
            'templateVariables' => $this->emailVariable->getAvailableVariables(),
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

        $rendered = $emailTemplate->renderTemplate($this->emailVariable->getPreviewSampleData());
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
            'templateVariables' => $this->emailVariable->getAvailableVariables(),
        ]);
    }

    public function update(EmailTemplateRequest $request, int $emailTemplate): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $emailTemplate = $this->emailTemplateService->getTemplateById($emailTemplate);

        if (! $emailTemplate) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Email template not found.'));
        }

        try {
            $this->emailTemplateService->updateTemplate($emailTemplate, $request->validated());

            return redirect()
                ->route('admin.email-templates.show', $emailTemplate->id)
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

    public function sendTestEmail(EmailTemplate $emailTemplate, Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $rendered = $emailTemplate->renderTemplate($this->emailVariable->getPreviewSampleData());
            Mail::send([], [], function ($message) use ($rendered, $request) {
                $message->to($request->input('email'))
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($rendered['subject'])
                    ->html($rendered['body_html']);
            });

            return response()->json(['message' => __('Test email sent successfully')]);
        } catch (\Exception $e) {
            $this->logAction('Failed to Send Test Email Template', $emailTemplate, [
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

    public function getContent(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('manage', Setting::class);
        try {
            // Load header and footer templates
            $emailTemplate->load(['headerTemplate', 'footerTemplate']);

            // Combine header, body, and footer content
            $combinedHtml = '';

            if ($emailTemplate->headerTemplate) {
                $combinedHtml .= $emailTemplate->headerTemplate->body_html;
            }

            $combinedHtml .= $emailTemplate->body_html;

            if ($emailTemplate->footerTemplate) {
                $combinedHtml .= $emailTemplate->footerTemplate->body_html;
            }

            return response()->json([
                'subject' => $emailTemplate->subject,
                'body_html' => $combinedHtml,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
