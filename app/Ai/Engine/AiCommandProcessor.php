<?php

declare(strict_types=1);

namespace App\Ai\Engine;

use App\Ai\Data\AiResult;
use App\Ai\Registry\ActionRegistry;
use App\Models\AiCommandLog;
use App\Services\AiContentGeneratorService;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Main processor for AI commands.
 *
 * Simplified approach: directly matches commands to actions
 * without complex multi-step LLM planning.
 */
class AiCommandProcessor
{
    public function __construct(
        private AiContentGeneratorService $aiService
    ) {
    }

    /**
     * Process a user command and return the result.
     */
    public function process(string $command): AiResult
    {
        return $this->processWithProgress($command, null);
    }

    /**
     * Process a user command with progress callback for streaming.
     *
     * @param  callable|null  $onProgress  Callback: fn(string $step, string $status, ?array $data)
     */
    public function processWithProgress(string $command, ?callable $onProgress = null): AiResult
    {
        $startTime = microtime(true);

        // Helper to report progress
        $progress = function (string $step, string $status = 'in_progress', ?array $data = null) use ($onProgress) {
            if ($onProgress) {
                $onProgress($step, $status, $data);
            }
        };

        try {
            $progress(__('Analyzing your request...'), 'in_progress');

            // Get available actions
            $actions = ActionRegistry::getActionsForUser();

            if (empty($actions)) {
                $progress(__('No actions available'), 'failed');

                return AiResult::failed(__('No AI actions are available. Please ensure modules are properly configured.'));
            }

            $progress(__('Understanding command...'), 'in_progress');

            // Determine which action to use based on command
            $actionMatch = $this->matchCommandToAction($command, $actions);

            if ($actionMatch === null) {
                $progress(__('Could not understand command'), 'failed');

                return AiResult::failed(__('I could not understand your command. Try something like: "Create a post about [topic]"'));
            }

            $progress(__('Command understood'), 'completed');

            // Execute the matched action with progress callback
            $result = $this->executeAction($actionMatch['action'], $actionMatch['payload'], $onProgress);

            // Log the command
            $this->logCommand($command, $actionMatch, $result, $startTime);

            return $result;

        } catch (Exception $e) {
            Log::error('AI Command Processing Error', [
                'command' => substr($command, 0, 200),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $progress(__('Error occurred'), 'failed');

            return AiResult::failed($e->getMessage());
        }
    }

    /**
     * Match a command to an available action.
     *
     * @return array{action: string, payload: array}|null
     */
    private function matchCommandToAction(string $command, array $actions): ?array
    {
        $commandLower = strtolower($command);

        // Simple pattern matching for common commands
        // Pattern 1: "create a post about X" or "write a post about X" or "draft an article about X"
        if (preg_match('/(?:create|write|generate|draft|make)\s+(?:a|an)?\s*(?:beautiful\s+)?(?:new\s+)?(?:blog\s+)?(?:post|article|page)\s+(?:about|on|for)\s+(.+)/i', $command, $matches)) {
            $topic = trim($matches[1]);

            // Clean topic - remove trailing image-related phrases for cleaner topic
            $topic = preg_replace('/[,.]?\s*(?:and\s+)?(?:generate|include|add|with)\s+(?:necessary\s+)?(?:some\s+)?images?.*$/i', '', $topic);
            $topic = trim($topic, ' .,');

            // Determine post_type from command
            $postType = $this->extractPostType($command);

            // Find create post action
            foreach ($actions as $action) {
                if (str_contains($action['name'], 'create') && str_contains($action['name'], 'post')) {
                    $imageInfo = $this->extractImageRequest($command);

                    return [
                        'action' => $action['name'],
                        'payload' => [
                            'topic' => $topic,
                            'tone' => $this->extractTone($command),
                            'length' => $this->extractLength($command),
                            'include_images' => $imageInfo['include'],
                            'image_count' => $imageInfo['count'],
                            'post_type' => $postType,
                        ],
                    ];
                }
            }
        }

        // Pattern 2: "draft an about us page" or "create a contact page" (topic before content type)
        if (preg_match('/(?:create|write|generate|draft|make)\s+(?:a|an)?\s*(?:beautiful\s+)?(?:new\s+)?(.+?)\s+(?:blog\s+)?(?:post|article|page)(?:\s|$)/i', $command, $matches)) {
            $topic = trim($matches[1]);

            // Skip if topic is empty or just adjectives
            if (! empty($topic) && ! preg_match('/^(?:beautiful|new|blog|the|a|an)$/i', $topic)) {
                // Determine post_type from command
                $postType = $this->extractPostType($command);

                // Find create post action
                foreach ($actions as $action) {
                    if (str_contains($action['name'], 'create') && str_contains($action['name'], 'post')) {
                        $imageInfo = $this->extractImageRequest($command);

                        return [
                            'action' => $action['name'],
                            'payload' => [
                                'topic' => $topic,
                                'tone' => $this->extractTone($command),
                                'length' => $this->extractLength($command),
                                'include_images' => $imageInfo['include'],
                                'image_count' => $imageInfo['count'],
                                'post_type' => $postType,
                            ],
                        ];
                    }
                }
            }
        }

        // Pattern: "generate seo for post X" or "create seo meta for post X"
        if (preg_match('/(?:generate|create)\s+seo\s+(?:meta\s+)?(?:for\s+)?post\s+(?:id\s+)?(\d+)/i', $command, $matches)) {
            $postId = (int) $matches[1];

            foreach ($actions as $action) {
                if (str_contains($action['name'], 'seo')) {
                    return [
                        'action' => $action['name'],
                        'payload' => ['post_id' => $postId],
                    ];
                }
            }
        }

        // If no pattern matched, try using AI to parse the command
        return $this->parseCommandWithAi($command, $actions);
    }

    /**
     * Use AI to parse the command and extract action/payload.
     *
     * @return array{action: string, payload: array}|null
     */
    private function parseCommandWithAi(string $command, array $actions): ?array
    {
        if (! $this->aiService->isConfigured()) {
            return null;
        }

        try {
            $actionsJson = json_encode($actions, JSON_PRETTY_PRINT);

            $systemPrompt = <<<PROMPT
You are an AI that parses user commands for a CMS system.

AVAILABLE ACTIONS:
{$actionsJson}

Parse the user's command and return a JSON object with:
- "action": the action name from AVAILABLE ACTIONS that best matches
- "payload": an object with the required parameters extracted from the command

If no action matches, return: {"action": null, "payload": {}}

IMPORTANT: Only use actions from the AVAILABLE ACTIONS list. Return valid JSON only.
PROMPT;

            $response = $this->sendAiRequest($systemPrompt, "Parse this command: {$command}");

            if ($response && isset($response['action']) && $response['action']) {
                return [
                    'action' => $response['action'],
                    'payload' => $response['payload'] ?? [],
                ];
            }
        } catch (Exception $e) {
            Log::warning('AI command parsing failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Execute a specific action with payload.
     */
    private function executeAction(string $actionName, array $payload, ?callable $onProgress = null): AiResult
    {
        $action = ActionRegistry::resolve($actionName);

        if ($action === null) {
            return AiResult::failed(__('Action not found: :action', ['action' => $actionName]));
        }

        // Check permission
        $permission = $action::permission();
        if ($permission && ! auth()->user()?->can($permission)) {
            return AiResult::failed(__('You do not have permission to perform this action.'));
        }

        // Pass progress callback to action if it supports it
        if ($onProgress !== null && method_exists($action, 'handleWithProgress')) {
            return $action->handleWithProgress($payload, $onProgress);
        }

        return $action->handle($payload);
    }

    /**
     * Extract tone from command.
     */
    private function extractTone(string $command): string
    {
        $commandLower = strtolower($command);

        if (str_contains($commandLower, 'casual') || str_contains($commandLower, 'friendly')) {
            return 'casual';
        }
        if (str_contains($commandLower, 'technical')) {
            return 'technical';
        }
        if (str_contains($commandLower, 'formal')) {
            return 'professional';
        }

        return 'professional';
    }

    /**
     * Extract desired length from command.
     */
    private function extractLength(string $command): string
    {
        $commandLower = strtolower($command);

        if (str_contains($commandLower, 'short') || str_contains($commandLower, 'brief')) {
            return 'short';
        }
        if (str_contains($commandLower, 'long') || str_contains($commandLower, 'detailed')) {
            return 'long';
        }

        return 'medium';
    }

    /**
     * Extract post type from command (post or page).
     */
    private function extractPostType(string $command): string
    {
        $commandLower = strtolower($command);

        // Check for page-related keywords
        if (str_contains($commandLower, ' page') || str_contains($commandLower, 'landing')) {
            return 'page';
        }

        // Default to post
        return 'post';
    }

    /**
     * Extract image generation request from command.
     *
     * @return array{include: bool, count: int}
     */
    private function extractImageRequest(string $command): array
    {
        $commandLower = strtolower($command);

        // Check for image-related keywords
        $imageKeywords = [
            'image',
            'images',
            'picture',
            'pictures',
            'photo',
            'photos',
            'illustration',
            'illustrations',
            'visual',
            'visuals',
            'graphic',
            'graphics',
        ];

        $includeImages = false;
        foreach ($imageKeywords as $keyword) {
            if (str_contains($commandLower, $keyword)) {
                $includeImages = true;
                break;
            }
        }

        // Default count
        $imageCount = 1;

        if ($includeImages) {
            // Check for specific count mentions
            if (preg_match('/(\d+)\s*(?:image|picture|photo|illustration|visual|graphic)s?/i', $command, $matches)) {
                $imageCount = min(3, max(1, (int) $matches[1]));
            } elseif (str_contains($commandLower, 'multiple') || str_contains($commandLower, 'several') || str_contains($commandLower, 'some')) {
                $imageCount = 2;
            } elseif (str_contains($commandLower, 'many') || str_contains($commandLower, 'lots')) {
                $imageCount = 3;
            }
        }

        return [
            'include' => $includeImages,
            'count' => $imageCount,
        ];
    }

    /**
     * Send request to AI provider.
     */
    private function sendAiRequest(string $systemPrompt, string $userPrompt): ?array
    {
        $provider = config('settings.ai_default_provider', 'openai');

        // Get API key or base URL based on provider
        $apiKey = match ($provider) {
            'openai' => config('settings.ai_openai_api_key') ?: config('ai.openai.api_key'),
            'claude' => config('settings.ai_claude_api_key') ?: config('ai.anthropic.api_key'),
            'gemini' => config('settings.ai_gemini_api_key') ?: config('ai.gemini.api_key'),
            'ollama' => null, // Ollama doesn't need API key
            default => null,
        };

        $ollamaBaseUrl = config('settings.ai_ollama_base_url') ?: config('ai.ollama.base_url', 'http://localhost:11434');

        // Validate configuration
        if ($provider === 'ollama') {
            if (empty($ollamaBaseUrl)) {
                return null;
            }
        } elseif (empty($apiKey)) {
            return null;
        }

        // Get model for each provider
        $openaiModel = config('settings.ai_openai_model') ?: config('ai.openai.model', 'gpt-4o-mini');
        $claudeModel = config('settings.ai_claude_model') ?: config('ai.anthropic.model', 'claude-3-haiku-20240307');
        $geminiModel = config('settings.ai_gemini_model') ?: config('ai.gemini.model', 'gemini-2.0-flash');
        $ollamaModel = config('settings.ai_ollama_model') ?: config('ai.ollama.model', 'llama3.2');

        $response = match ($provider) {
            'openai' => \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $openaiModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 500,
                'response_format' => ['type' => 'json_object'],
            ]),
            'claude' => \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => $claudeModel,
                'max_tokens' => 500,
                'system' => $systemPrompt . "\n\nIMPORTANT: Return valid JSON only, no additional text.",
                'messages' => [['role' => 'user', 'content' => $userPrompt]],
            ]),
            'gemini' => \Illuminate\Support\Facades\Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt . "\n\n" . $userPrompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 500,
                        'responseMimeType' => 'application/json',
                    ],
                ]
            ),
            'ollama' => \Illuminate\Support\Facades\Http::timeout(60)->post(
                rtrim($ollamaBaseUrl, '/') . '/api/chat',
                [
                    'model' => $ollamaModel,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt . "\n\nIMPORTANT: Return valid JSON only, no additional text or markdown."],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'stream' => false,
                    'format' => 'json',
                    'options' => [
                        'temperature' => 0.3,
                        'num_predict' => 500,
                    ],
                ]
            ),
            default => null,
        };

        if (! $response || ! $response->successful()) {
            return null;
        }

        $content = match ($provider) {
            'openai' => $response->json('choices.0.message.content'),
            'claude' => $response->json('content.0.text'),
            'gemini' => $response->json('candidates.0.content.parts.0.text'),
            'ollama' => $response->json('message.content'),
            default => null,
        };

        if (! $content) {
            return null;
        }

        // Clean and parse JSON
        $content = trim($content);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        return json_decode($content, true);
    }

    /**
     * Log the command execution.
     */
    private function logCommand(string $command, array $actionMatch, AiResult $result, float $startTime): void
    {
        try {
            AiCommandLog::create([
                'user_id' => auth()->id(),
                'command' => $command,
                'intent' => ['action' => $actionMatch['action']],
                'plan' => ['payload' => $actionMatch['payload']],
                'result' => $result->toArray(),
                'status' => $result->status,
                'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to log AI command', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if the AI system is configured.
     */
    public function isConfigured(): bool
    {
        return $this->aiService->isConfigured();
    }

    /**
     * Get the current AI provider.
     */
    public function getProvider(): string
    {
        return config('settings.ai_default_provider', 'openai');
    }
}
