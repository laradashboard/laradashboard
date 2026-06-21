<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\AiContentGeneratorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiContentController extends Controller
{
    public function __construct(
        private AiContentGeneratorService $aiService
    ) {
    }

    public function generateContent(Request $request): JsonResponse
    {
        $this->authorize('create', Post::class);

        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|min:10|max:1000',
            'provider' => 'nullable|string|in:openai,claude',
            'content_type' => 'nullable|string|in:post_content,page_content,general',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            // Set provider if specified
            if ($request->filled('provider')) {
                $this->aiService->setProvider($request->provider);
            }

            $contentType = $request->get('content_type', 'post_content');
            $generatedContent = $this->aiService->generateContent(
                $request->prompt,
                $contentType
            );

            return response()->json([
                'success' => true,
                'message' => 'Content generated successfully',
                'data' => $generatedContent,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate content: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getProviders(): JsonResponse
    {
        try {
            $providers = $this->aiService->getAvailableProviders();
            $defaultProvider = config('settings.ai_default_provider', 'openai');

            return response()->json([
                'success' => true,
                'data' => [
                    'providers' => $providers,
                    'default_provider' => $defaultProvider,
                    'is_configured' => $this->aiService->isConfigured(),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get providers: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function modifyText(Request $request): JsonResponse
    {
        $this->authorize('create', Post::class);

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|min:1|max:5000',
            'instruction' => 'required|string|min:3|max:500',
            'provider' => 'nullable|string|in:openai,claude',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            // Set provider if specified
            if ($request->filled('provider')) {
                $this->aiService->setProvider($request->provider);
            }

            $modifiedText = $this->aiService->modifyText(
                $request->text,
                $request->instruction
            );

            return response()->json([
                'success' => true,
                'message' => 'Text modified successfully',
                'data' => ['text' => $modifiedText],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to modify text: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function generateSeo(Request $request): JsonResponse
    {
        $this->authorize('create', Post::class);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:50000',
            'excerpt' => 'nullable|string|max:5000',
            'slug' => 'nullable|string|max:200',
            'post_type' => 'nullable|string|max:50',
            'provider' => 'nullable|string|in:openai,claude,gemini,ollama',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $title = trim((string) $request->input('title', ''));
        $content = trim(strip_tags((string) $request->input('content', '')));

        if ($title === '' && $content === '') {
            return response()->json([
                'success' => false,
                'message' => __('Add a title or content before generating SEO metadata.'),
                'data' => null,
            ], 422);
        }

        try {
            if ($request->filled('provider')) {
                $this->aiService->setProvider($request->provider);
            }

            $seoMeta = $this->aiService->generateSeoMeta([
                'title' => $title,
                'content' => $request->input('content', ''),
                'excerpt' => $request->input('excerpt', ''),
                'slug' => $request->input('slug', ''),
                'post_type' => $request->input('post_type', 'post'),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('SEO metadata generated successfully.'),
                'data' => $seoMeta,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate SEO metadata: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
