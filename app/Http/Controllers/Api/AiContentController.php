<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Services\AiContentGeneratorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiContentController extends ApiController
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
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Validation failed'
            );
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

            return $this->successResponse($generatedContent, 'Content generated successfully');

        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to generate content',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function getProviders(): JsonResponse
    {
        $this->authorize('create', Post::class);

        try {
            $providers = $this->aiService->getAvailableProviders();
            $defaultProvider = config('settings.ai_default_provider', 'openai');

            return $this->successResponse([
                'providers' => $providers,
                'default_provider' => $defaultProvider,
                'is_configured' => $this->aiService->isConfigured(),
            ]);

        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to get providers',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
