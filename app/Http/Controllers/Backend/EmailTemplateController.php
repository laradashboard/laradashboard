<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\Emails\EmailTemplateService;
use App\Enums\TemplateType;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Services\Emails\EmailVariable;
use Illuminate\Support\Facades\Log;

class EmailTemplateController extends Controller
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
            ->setBreadcrumbIcon('lucide:mail')
            ->setBreadcrumbActionButton(
                route('admin.email-templates.create'),
                __('New Template'),
                'feather:plus',
                'settings.edit'
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.email-templates.index');
    }

    public function show(EmailTemplate $emailTemplate): Renderable
    {
        $this->authorize('manage', Setting::class);

        $rendered = $emailTemplate->renderTemplate($this->emailVariable->getPreviewSampleData());
        $emailTemplate->subject = $rendered['subject'];
        $emailTemplate->body_html = $rendered['body_html'];

        $this->setBreadcrumbTitle(__('View Template'))
            ->setBreadcrumbIcon('lucide:mail')
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Email Templates'), route('admin.email-templates.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.email-templates.show', compact('emailTemplate'));
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

    public function getByType(string $type): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        try {
            $templates = $this->emailTemplateService->getTemplatesByType($type);

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

        return response()->json([
            'name' => $emailTemplate->name,
            'subject' => $emailTemplate->subject,
            'body_html' => $emailTemplate->body_html ?? '',
        ]);
    }

    /**
     * API endpoint to list all active templates for AJAX requests.
     */
    public function apiList(): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $templates = EmailTemplate::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (EmailTemplate $template) => [
                'id' => $template->id,
                'uuid' => $template->uuid,
                'name' => $template->name,
                'subject' => $template->subject,
                'type' => $template->type,
                'type_label' => $template->type_label,
                'body_html' => $template->body_html ?? '',
            ]);

        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Show the custom drag-drop email builder for creating a new template.
     */
    public function builder(Request $request): Renderable
    {
        $this->authorize('manage', Setting::class);

        // Support redirect_url for extensibility - any module can pass a redirect URL
        // to return to after saving the template
        $redirectUrl = $request->query('redirect_url');

        return view('email-templates.builder', [
            'saveUrl' => route('admin.email-templates.store'),
            'redirectUrl' => $redirectUrl,
        ]);
    }

    /**
     * Show the custom drag-drop email builder for editing an existing template.
     */
    public function builderEdit(Request $request, EmailTemplate $emailTemplate): Renderable
    {
        $this->authorize('manage', Setting::class);

        // Support redirect_url for extensibility - any module can pass a redirect URL
        // to return to after saving the template
        $redirectUrl = $request->query('redirect_url');

        return view('email-templates.builder', [
            'template' => $emailTemplate,
            'initialData' => $emailTemplate->design_json,
            'templateData' => [
                'uuid' => $emailTemplate->uuid,
                'name' => $emailTemplate->name,
                'subject' => $emailTemplate->subject,
                'is_active' => $emailTemplate->is_active,
            ],
            'saveUrl' => route('admin.email-templates.update', $emailTemplate),
            'redirectUrl' => $redirectUrl,
        ]);
    }

    /**
     * Store a template from the custom drag-drop email builder.
     */
    public function builderStore(Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'body_html' => 'required|string',
            'design_json' => 'required|array',
        ]);

        try {
            $template = $this->emailTemplateService->createTemplate([
                'name' => $validated['name'],
                'subject' => $validated['subject'] ?? '',
                'is_active' => $validated['is_active'] ?? true,
                'body_html' => $validated['body_html'],
                'design_json' => $validated['design_json'],
                'type' => TemplateType::EMAIL->value,
            ]);

            return response()->json([
                'success' => true,
                'id' => $template->id,
                'uuid' => $template->uuid,
                'message' => __('Email template created successfully.'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create email template from builder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to create email template: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Update a template from the custom drag-drop email builder.
     */
    public function builderUpdate(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'body_html' => 'required|string',
            'design_json' => 'required|array',
        ]);

        try {
            $this->emailTemplateService->updateTemplate($emailTemplate, [
                'name' => $validated['name'],
                'subject' => $validated['subject'] ?? '',
                'is_active' => $validated['is_active'] ?? $emailTemplate->is_active,
                'body_html' => $validated['body_html'],
                'design_json' => $validated['design_json'],
            ]);

            return response()->json([
                'success' => true,
                'id' => $emailTemplate->id,
                'uuid' => $emailTemplate->uuid,
                'message' => __('Email template updated successfully.'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update email template from builder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to update email template: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Upload an image for the email builder.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);

        try {
            $file = $request->file('image');
            $filename = uniqid('email_') . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store in public storage
            $path = $file->storeAs('email-images', $filename, 'public');

            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'filename' => $filename,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload email image', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to upload image: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Upload a video for the email builder.
     */
    public function uploadVideo(Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        // Check PHP upload limits
        $maxUploadSize = min(
            $this->convertToBytes(ini_get('upload_max_filesize')),
            $this->convertToBytes(ini_get('post_max_size'))
        );
        $maxUploadMb = floor($maxUploadSize / 1024 / 1024);

        // If no file was uploaded, it might be due to PHP limits
        if (! $request->hasFile('video')) {
            return response()->json([
                'success' => false,
                'message' => __('No video file received. Your server allows uploads up to :size MB. Please upload a smaller file or increase PHP upload_max_filesize and post_max_size settings.', ['size' => $maxUploadMb]),
            ], 422);
        }

        $request->validate([
            'video' => 'required|mimes:mp4,webm,ogg,mov,avi|max:' . ($maxUploadMb * 1024), // Dynamic based on PHP config
            'thumbnail' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);

        try {
            $videoFile = $request->file('video');
            $videoFilename = uniqid('email_video_') . '_' . time() . '.' . $videoFile->getClientOriginalExtension();

            // Store video in public storage
            $videoPath = $videoFile->storeAs('email-videos', $videoFilename, 'public');
            $videoUrl = asset('storage/' . $videoPath);

            // Handle thumbnail - either uploaded or generate placeholder
            $thumbnailUrl = null;
            if ($request->hasFile('thumbnail')) {
                $thumbFile = $request->file('thumbnail');
                $thumbFilename = uniqid('email_thumb_') . '_' . time() . '.' . $thumbFile->getClientOriginalExtension();
                $thumbPath = $thumbFile->storeAs('email-images', $thumbFilename, 'public');
                $thumbnailUrl = asset('storage/' . $thumbPath);
            }

            return response()->json([
                'success' => true,
                'videoUrl' => $videoUrl,
                'thumbnailUrl' => $thumbnailUrl,
                'filename' => $videoFilename,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload email video', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to upload video: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Convert PHP ini size string to bytes.
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $numericValue = (int) $value;

        switch ($last) {
            case 'g':
                $numericValue *= 1024;
                // no break - fall through
            case 'm':
                $numericValue *= 1024;
                // no break - fall through
            case 'k':
                $numericValue *= 1024;
        }

        return $numericValue;
    }
}
