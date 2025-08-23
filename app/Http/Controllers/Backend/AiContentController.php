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
}
