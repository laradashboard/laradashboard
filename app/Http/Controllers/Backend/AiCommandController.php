<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Ai\Engine\AiCommandProcessor;
use App\Ai\Registry\ActionRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for AI Command System.
 */
class AiCommandController extends Controller
{
    public function __construct(
        private AiCommandProcessor $processor
    ) {
    }

    /**
     * Process an AI command (regular JSON response).
     */
    public function process(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'command' => 'required|string|min:5|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('Please enter a valid command (5-2000 characters).'),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $result = $this->processor->process($request->command);

        return response()->json([
            'success' => $result->isSuccess() || $result->status === 'partial',
            'message' => $result->message,
            'data' => [
                'status' => $result->status,
                'completed_steps' => $result->completedSteps,
                'actions' => $result->actions,
                'result_data' => $result->data,
            ],
        ], $result->isFailed() ? 400 : 200);
    }

    /**
     * Process an AI command with streaming progress updates (SSE).
     */
    public function processStream(Request $request): StreamedResponse
    {
        $validator = Validator::make($request->all(), [
            'command' => 'required|string|min:5|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->stream(function () use ($validator) {
                $this->sendSSE('error', [
                    'message' => __('Please enter a valid command (5-2000 characters).'),
                    'errors' => $validator->errors()->toArray(),
                ]);
            }, 200, $this->sseHeaders());
        }

        $command = $request->command;

        return response()->stream(function () use ($command) {
            // Disable output buffering for real-time streaming
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Progress callback that sends SSE events
            $progressCallback = function (string $step, string $status = 'in_progress', ?array $data = null) {
                $this->sendSSE('progress', [
                    'step' => $step,
                    'status' => $status,
                    'data' => $data,
                ]);
            };

            // Process with streaming
            $result = $this->processor->processWithProgress($command, $progressCallback);

            // Send final result
            $this->sendSSE('complete', [
                'success' => $result->isSuccess() || $result->status === 'partial',
                'message' => $result->message,
                'data' => [
                    'status' => $result->status,
                    'completed_steps' => $result->completedSteps,
                    'actions' => $result->actions,
                    'result_data' => $result->data,
                ],
            ]);
        }, 200, $this->sseHeaders());
    }

    /**
     * Send a Server-Sent Event.
     */
    private function sendSSE(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";

        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }

    /**
     * Get headers for SSE response.
     */
    private function sseHeaders(): array
    {
        return [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ];
    }

    /**
     * Get AI system status.
     */
    public function status(): JsonResponse
    {
        $isConfigured = $this->processor->isConfigured();
        $provider = $this->processor->getProvider();
        $availableActions = ActionRegistry::getActionsForUser();

        return response()->json([
            'success' => true,
            'data' => [
                'configured' => $isConfigured,
                'provider' => $provider,
                'actions_count' => count($availableActions),
                'actions' => array_map(fn ($action) => [
                    'name' => $action['name'],
                    'description' => $action['description'],
                ], $availableActions),
            ],
        ]);
    }

    /**
     * Get example commands.
     */
    public function examples(): JsonResponse
    {
        $examples = [
            [
                'command' => __('Create a post about How to be a serious software engineer'),
                'description' => __('Generates a professional blog post on the topic'),
            ],
            [
                'command' => __('Write a blog post about Laravel best practices'),
                'description' => __('Creates technical content about Laravel development'),
            ],
            [
                'command' => __('Generate SEO meta for post ID 5'),
                'description' => __('Creates SEO title and description for an existing post'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $examples,
        ]);
    }
}
