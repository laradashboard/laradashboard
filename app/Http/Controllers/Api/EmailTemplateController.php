<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\EmailTemplateRequest;
use App\Http\Resources\EmailTemplateResource;
use App\Models\EmailTemplate;
use App\Services\Emails\EmailTemplateService;
use App\Services\Emails\EmailVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends ApiController
{
    public function __construct(
        private readonly EmailTemplateService $templateService,
        private readonly EmailVariable $emailVariable
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $perPage = (int) ($request->input('per_page') ?? config('settings.default_pagination', 10));
        $templates = $this->templateService->getPaginatedTemplates($request->input('search'), $perPage);

        return $this->resourceResponse(
            EmailTemplateResource::collection($templates)->additional([
                'meta' => [
                    'pagination' => [
                        'current_page' => $templates->currentPage(),
                        'last_page' => $templates->lastPage(),
                        'per_page' => $templates->perPage(),
                        'total' => $templates->total(),
                    ],
                ],
            ]),
            'Email templates retrieved successfully'
        );
    }

    public function store(EmailTemplateRequest $request): JsonResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $template = $this->templateService->createTemplate($data);

        $this->logAction('Email Template Created', $template);

        return $this->resourceResponse(
            new EmailTemplateResource($template),
            'Email template created successfully',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $template = $this->templateService->getTemplateById($id);
        if (! $template) {
            return $this->errorResponse('Email template not found', 404);
        }

        $this->authorize('manage', \App\Models\Setting::class);

        // Render template for preview
        $rendered = $template->renderTemplate($this->emailVariable->getPreviewSampleData());
        $template->subject = $rendered['subject'];
        $template->body_html = $rendered['body_html'];

        return $this->resourceResponse(new EmailTemplateResource($template), 'Email template retrieved successfully');
    }

    public function update(EmailTemplateRequest $request, int $id): JsonResponse
    {
        $template = $this->templateService->getTemplateById($id);
        if (! $template) {
            return $this->errorResponse('Email template not found', 404);
        }

        $this->authorize('manage', \App\Models\Setting::class);

        $updated = $this->templateService->updateTemplate($template, $request->validated());

        $this->logAction('Email Template Updated', $updated);

        return $this->resourceResponse(new EmailTemplateResource($updated), 'Email template updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $template = $this->templateService->getTemplateById($id);
        if (! $template) {
            return $this->errorResponse('Email template not found', 404);
        }

        $this->authorize('manage', \App\Models\Setting::class);

        $this->templateService->deleteTemplate($template);

        $this->logAction('Email Template Deleted', $template);

        return $this->successResponse(null, 'Email template deleted successfully', 204);
    }

    public function getByType(string $type): JsonResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        try {
            $templates = $this->templateService->getTemplatesByType($type);

            return $this->resourceResponse(EmailTemplateResource::collection($templates), 'Templates retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function getContent(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('manage', \App\Models\Setting::class);

        // Load header and footer templates
        $emailTemplate->load(['headerTemplate', 'footerTemplate']);

        $combinedHtml = '';
        if ($emailTemplate->headerTemplate) {
            $combinedHtml .= $emailTemplate->headerTemplate->body_html;
        }
        $combinedHtml .= $emailTemplate->body_html;
        if ($emailTemplate->footerTemplate) {
            $combinedHtml .= $emailTemplate->footerTemplate->body_html;
        }

        return $this->successResponse(['subject' => $emailTemplate->subject, 'body_html' => $combinedHtml], 'Email content retrieved');
    }
}
