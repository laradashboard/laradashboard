<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentGeneratorService
{
    private ?string $provider;

    private ?string $apiKey;

    private ?string $baseUrl;

    public function __construct()
    {
        $this->provider = config('settings.ai_default_provider', 'openai');
        $this->setProviderConfig();
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;
        $this->setProviderConfig();

        return $this;
    }

    private function setProviderConfig(): void
    {
        $this->apiKey = match ($this->provider) {
            'openai' => config('settings.ai_openai_api_key') ?: config('ai.openai.api_key'),
            'claude' => config('settings.ai_claude_api_key') ?: config('ai.anthropic.api_key'),
            'gemini' => config('settings.ai_gemini_api_key') ?: config('ai.gemini.api_key'),
            'ollama' => null, // Ollama doesn't require an API key
            default => throw new Exception("Unsupported AI provider: {$this->provider}")
        };

        // Set base URL for Ollama
        $this->baseUrl = match ($this->provider) {
            'ollama' => config('settings.ai_ollama_base_url') ?: config('ai.ollama.base_url', 'http://localhost:11434'),
            default => null,
        };

        // Validate configuration
        if ($this->provider !== 'ollama' && empty($this->apiKey)) {
            Log::error('AI Content Generator: API key not configured', [
                'provider' => $this->provider,
            ]);
        }

        if ($this->provider === 'ollama' && empty($this->baseUrl)) {
            Log::error('AI Content Generator: Ollama base URL not configured');
        }
    }

    public function generateContent(string $prompt, string $type = 'general'): array
    {
        // Check if provider is configured before making request
        if ($this->provider === 'ollama') {
            if (empty($this->baseUrl)) {
                throw new Exception(__('Ollama is not configured. Please set the Ollama base URL in settings.'));
            }
        } elseif (empty($this->apiKey)) {
            throw new Exception(__('AI service is not configured. Please contact the administrator to set up the API key.'));
        }

        try {
            $systemPrompt = $this->getSystemPrompt($type);
            $response = $this->sendRequest($systemPrompt, $prompt);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            Log::error('AI Content Generation Error', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100) . '...',
            ]);

            throw $e;
        }
    }

    private function getSystemPrompt(string $type): string
    {
        return match ($type) {
            'post_content' => 'You are a content creation assistant. Generate well-structured blog post content including title, excerpt, and main content based on the user\'s requirements. 

IMPORTANT: Return the response in JSON format with keys: "title", "excerpt", and "content". 

For the content field:
- Generate comprehensive, detailed content that matches the requested length (if specified)
- Use double line breaks (\\n\\n) to separate paragraphs
- Make each paragraph 3-5 sentences long for longer content
- Create engaging, SEO-friendly content with proper structure
- Use simple HTML formatting when appropriate (like <strong>, <em>)
- Include relevant subheadings and detailed explanations
- Ensure the content is informative, well-researched, and valuable to readers

Example format:
{
  "title": "Your Title Here",
  "excerpt": "A brief summary of the content",
  "content": "First paragraph with 3-5 sentences introducing the topic.\\n\\nSecond paragraph with detailed information and examples.\\n\\nThird paragraph expanding on key points.\\n\\nContinue with more paragraphs to reach the desired length."
}',
            'page_content' => 'You are a web page content creation assistant. Generate professional page content including title, excerpt, and main content based on the user\'s requirements. Return the response in JSON format with keys: "title", "excerpt", and "content". Use double line breaks (\\n\\n) to separate paragraphs and make the content informative, professional, and well-structured.',
            default => 'You are a helpful content creation assistant. Generate content based on the user\'s requirements and return it in JSON format with appropriate keys. Use proper paragraph breaks with \\n\\n.'
        };
    }

    private function sendRequest(string $systemPrompt, string $userPrompt): Response
    {
        return match ($this->provider) {
            'openai' => $this->sendOpenAiRequest($systemPrompt, $userPrompt),
            'claude' => $this->sendClaudeRequest($systemPrompt, $userPrompt),
            'gemini' => $this->sendGeminiRequest($systemPrompt, $userPrompt),
            'ollama' => $this->sendOllamaRequest($systemPrompt, $userPrompt),
            default => throw new Exception("Unsupported provider: {$this->provider}")
        };
    }

    private function sendOpenAiRequest(string $systemPrompt, string $userPrompt): Response
    {
        $model = config('settings.ai_openai_model') ?: config('ai.openai.model', 'gpt-4o-mini');

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)
            ->post(
                'https://api.openai.com/v1/chat/completions',
                [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => $this->getMaxTokens(),
                ]
            );
    }

    private function sendClaudeRequest(string $systemPrompt, string $userPrompt): Response
    {
        $model = config('settings.ai_claude_model') ?: config('ai.anthropic.model', 'claude-3-haiku-20240307');

        return Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ])->timeout(60)
            ->post(
                'https://api.anthropic.com/v1/messages',
                [
                    'model' => $model,
                    'max_tokens' => $this->getMaxTokens(),
                    'system' => $systemPrompt,
                    'messages' => [
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                ]
            );
    }

    private function sendGeminiRequest(string $systemPrompt, string $userPrompt): Response
    {
        $model = config('settings.ai_gemini_model') ?: config('ai.gemini.model', 'gemini-2.0-flash');

        return Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}",
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
                        'temperature' => 0.7,
                        'maxOutputTokens' => $this->getMaxTokens(),
                        'responseMimeType' => 'application/json',
                    ],
                ]
            );
    }

    private function sendOllamaRequest(string $systemPrompt, string $userPrompt): Response
    {
        $model = config('settings.ai_ollama_model') ?: config('ai.ollama.model', 'llama3.2');
        $baseUrl = rtrim($this->baseUrl, '/');

        return Http::timeout(120)
            ->post(
                "{$baseUrl}/api/chat",
                [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'stream' => false,
                    'format' => 'json',
                    'options' => [
                        'num_predict' => $this->getMaxTokens(),
                        'temperature' => 0.7,
                    ],
                ]
            );
    }

    private function parseResponse(Response $response): array
    {
        if (! $response->successful()) {
            throw new Exception($this->parseApiError($response));
        }

        $data = $response->json();

        $content = match ($this->provider) {
            'openai' => $data['choices'][0]['message']['content'] ?? '',
            'claude' => $data['content'][0]['text'] ?? '',
            'gemini' => $this->extractGeminiContent($data),
            'ollama' => $data['message']['content'] ?? '',
            default => throw new Exception("Unknown provider: {$this->provider}")
        };

        // Clean the content - remove markdown code blocks if present
        $cleanedContent = $this->extractJsonFromResponse($content);

        // Try to parse as JSON first
        $parsedContent = json_decode($cleanedContent, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
            // Handle nested structure if present
            if (isset($parsedContent[0]) && is_array($parsedContent[0])) {
                $parsedContent = $parsedContent[0];
            }

            // Ensure we have the required keys
            return [
                'title' => $parsedContent['title'] ?? 'Generated Title',
                'excerpt' => $parsedContent['excerpt'] ?? 'Generated excerpt from AI',
                'content' => $parsedContent['content'] ?? $content,
            ];
        }

        // Log parsing failure for debugging
        Log::warning('AI response JSON parsing failed', [
            'provider' => $this->provider,
            'json_error' => json_last_error_msg(),
            'content_preview' => substr($cleanedContent, 0, 200),
        ]);

        // If not valid JSON, try to structure the content better
        $lines = explode("\n", trim($content));
        $lines = array_filter($lines, fn ($line) => ! empty(trim($line)));

        if (count($lines) >= 2) {
            // Use first line as title, create excerpt and content from remaining
            $title = trim($lines[0]);
            $contentLines = array_slice($lines, 1);
            $fullContent = implode("\n\n", $contentLines);

            // Create excerpt from first sentence or first 150 characters
            $sentences = preg_split('/[.!?]+/', $fullContent);
            $excerpt = trim($sentences[0] ?? '');
            if (strlen($excerpt) > 150) {
                $excerpt = substr($excerpt, 0, 150) . '...';
            }

            return [
                'title' => $title,
                'excerpt' => $excerpt ?: 'Generated excerpt from AI',
                'content' => $fullContent,
            ];
        }

        // Fallback for single paragraph content
        return [
            'title' => 'Generated Title',
            'excerpt' => 'Generated excerpt from AI',
            'content' => $content,
        ];
    }

    /**
     * Extract content from Gemini API response, handling various response formats
     */
    private function extractGeminiContent(array $data): string
    {
        // Standard response path
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text !== null) {
            return $text;
        }

        // Try alternative paths Gemini might use
        if (isset($data['candidates'][0]['content']['parts'])) {
            $parts = $data['candidates'][0]['content']['parts'];
            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    return $part['text'];
                }
            }
        }

        // If response is directly in candidates
        if (isset($data['candidates'][0]['text'])) {
            return $data['candidates'][0]['text'];
        }

        // Log unexpected format.
        Log::error('Unexpected Gemini response format', [
            'data_keys' => array_keys($data),
            'candidates' => $data['candidates'] ?? 'not set',
        ]);

        return '';
    }

    /**
     * Extract JSON from AI response, handling markdown code blocks, arrays, and extra text
     */
    private function extractJsonFromResponse(string $content): string
    {
        $content = trim($content);

        // Remove markdown code blocks (```json ... ``` or ``` ... ```)
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/s', $content, $matches)) {
            $content = trim($matches[1]);
        }

        // Check if content starts with [ (array) - Gemini sometimes wraps response in array
        if (str_starts_with($content, '[')) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && ! empty($decoded)) {
                // If it's an array, get the first element
                $firstElement = $decoded[0] ?? $decoded;
                if (is_array($firstElement)) {
                    return json_encode($firstElement);
                }
            }
        }

        // Find JSON object boundaries (first { to last })
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');

        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $content = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        return $content;
    }

    /**
     * Parse API error response into user-friendly message
     */
    private function parseApiError(Response $response): string
    {
        $statusCode = $response->status();
        $body = $response->json() ?? [];

        // Handle OpenAI errors
        if (isset($body['error']['message'])) {
            $errorMessage = $body['error']['message'];
            $errorType = $body['error']['type'] ?? '';

            // Map common error types to user-friendly messages
            if (str_contains($errorMessage, 'API key') || $errorType === 'invalid_request_error') {
                return __('AI service is not configured. Please contact the administrator to set up the API key.');
            }

            if ($errorType === 'insufficient_quota') {
                return __('AI service quota exceeded. Please try again later or contact the administrator.');
            }

            if ($errorType === 'rate_limit_error') {
                return __('Too many requests. Please wait a moment and try again.');
            }

            return __('AI service error: :message', ['message' => $this->truncateMessage($errorMessage)]);
        }

        // Handle Gemini errors
        if (isset($body['error']['message']) && $this->provider === 'gemini') {
            $errorMessage = $body['error']['message'];
            if (str_contains($errorMessage, 'API key')) {
                return __('Gemini API key is invalid. Please check your API key in settings.');
            }

            return __('Gemini error: :message', ['message' => $this->truncateMessage($errorMessage)]);
        }

        // Handle Ollama errors
        if ($this->provider === 'ollama') {
            if (isset($body['error'])) {
                $errorMessage = is_string($body['error']) ? $body['error'] : ($body['error']['message'] ?? 'Unknown error');

                if (str_contains($errorMessage, 'model') && str_contains($errorMessage, 'not found')) {
                    return __('Ollama model not found. Please ensure the model is installed (run: ollama pull :model)', [
                        'model' => config('settings.ai_ollama_model') ?: config('ai.ollama.model', 'llama3.2'),
                    ]);
                }

                return __('Ollama error: :message', ['message' => $this->truncateMessage($errorMessage)]);
            }

            // Connection refused or timeout
            if ($statusCode === 0) {
                return __('Cannot connect to Ollama. Please ensure Ollama is running at :url', ['url' => $this->baseUrl]);
            }
        }

        // Handle HTTP status codes
        return match ($statusCode) {
            401 => __('AI service authentication failed. Please contact the administrator.'),
            403 => __('AI service access denied. Please contact the administrator.'),
            429 => __('Too many requests. Please wait a moment and try again.'),
            500, 502, 503 => __('AI service is temporarily unavailable. Please try again later.'),
            default => __('AI service request failed. Please try again.'),
        };
    }

    /**
     * Truncate error message to a reasonable length
     */
    private function truncateMessage(string $message, int $maxLength = 100): string
    {
        if (strlen($message) <= $maxLength) {
            return $message;
        }

        return substr($message, 0, $maxLength) . '...';
    }

    /**
     * Modify text based on user instruction
     */
    public function modifyText(string $text, string $instruction): string
    {
        // Check if provider is configured before making request
        if ($this->provider === 'ollama') {
            if (empty($this->baseUrl)) {
                throw new Exception(__('Ollama is not configured. Please set the Ollama base URL in settings.'));
            }
        } elseif (empty($this->apiKey)) {
            throw new Exception(__('AI service is not configured. Please contact the administrator to set up the API key.'));
        }

        try {
            $systemPrompt = 'You are a helpful writing assistant. Your task is to modify the given text according to the user\'s instruction.

IMPORTANT RULES:
- Only return the modified text, nothing else
- Do not add any explanations, introductions, or conclusions
- Do not wrap the response in quotes or any other formatting
- Preserve any HTML tags that are present in the original text
- Keep the same general format (if it\'s a paragraph, return a paragraph)';

            $userPrompt = "Instruction: {$instruction}\n\nText to modify:\n{$text}";

            $response = $this->sendRequest($systemPrompt, $userPrompt);

            return $this->parseTextResponse($response);
        } catch (\Exception $e) {
            Log::error('AI Text Modification Error', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100) . '...',
            ]);

            throw $e;
        }
    }

    /**
     * Parse response for simple text modification (not JSON)
     */
    private function parseTextResponse(Response $response): string
    {
        if (! $response->successful()) {
            throw new Exception($this->parseApiError($response));
        }

        $data = $response->json();

        $content = match ($this->provider) {
            'openai' => $data['choices'][0]['message']['content'] ?? '',
            'claude' => $data['content'][0]['text'] ?? '',
            'gemini' => $this->extractGeminiContent($data),
            'ollama' => $data['message']['content'] ?? '',
            default => throw new Exception("Unknown provider: {$this->provider}")
        };

        // Clean up the response - remove any quotes wrapping the text
        $content = trim($content);
        $content = preg_replace('/^["\']|["\']$/', '', $content);

        return $content;
    }

    public function getAvailableProviders(): array
    {
        $providers = [];

        if (config('settings.ai_openai_api_key') ?: config('ai.openai.api_key')) {
            $providers['openai'] = 'OpenAI';
        }

        if (config('settings.ai_claude_api_key') ?: config('ai.anthropic.api_key')) {
            $providers['claude'] = 'Claude (Anthropic)';
        }

        if (config('settings.ai_gemini_api_key') ?: config('ai.gemini.api_key')) {
            $providers['gemini'] = 'Gemini (Google)';
        }

        if (config('settings.ai_ollama_base_url') ?: config('ai.ollama.base_url')) {
            $providers['ollama'] = 'Ollama (Local)';
        }

        return $providers;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->getAvailableProviders());
    }

    public function getDefaultProvider(): string
    {
        return config('settings.ai_default_provider', 'openai');
    }

    public function getMaxTokens(): int
    {
        return (int) config('settings.ai_max_tokens', 4096);
    }

    /**
     * Generate an image using AI (OpenAI DALL-E)
     *
     * @param  string  $prompt  Description of the image to generate
     * @param  string  $size  Image size (1024x1024, 1792x1024, 1024x1792)
     * @return array{url: string, revised_prompt: string}|null
     */
    public function generateImage(string $prompt, string $size = '1024x1024'): ?array
    {
        // Image generation only works with OpenAI
        $apiKey = config('settings.ai_openai_api_key') ?: config('ai.openai.api_key');

        if (empty($apiKey)) {
            Log::warning('Image generation skipped: OpenAI API key not configured');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => $size,
                    'quality' => 'standard',
                ]);

            if (! $response->successful()) {
                Log::error('Image generation failed', [
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);

                return null;
            }

            $data = $response->json();

            return [
                'url' => $data['data'][0]['url'] ?? null,
                'revised_prompt' => $data['data'][0]['revised_prompt'] ?? $prompt,
            ];
        } catch (Exception $e) {
            Log::error('Image generation error', [
                'error' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100),
            ]);

            return null;
        }
    }

    /**
     * Download an image from URL and store it locally
     *
     * @param  string  $imageUrl  The temporary URL from DALL-E
     * @param  string  $storagePath  Path relative to storage/app/public
     * @return string|null  The public URL of the stored image
     */
    public function downloadAndStoreImage(string $imageUrl, string $storagePath = 'posts/images'): ?string
    {
        try {
            $response = Http::timeout(60)->get($imageUrl);

            if (! $response->successful()) {
                Log::error('Failed to download generated image', ['url' => $imageUrl]);

                return null;
            }

            $imageContent = $response->body();
            $fileName = 'ai_' . uniqid() . '.png';
            $fullPath = $storagePath . '/' . $fileName;

            // Ensure directory exists
            $directory = storage_path('app/public/' . $storagePath);
            if (! file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Store the image
            \Illuminate\Support\Facades\Storage::disk('public')->put($fullPath, $imageContent);

            // Use asset() helper to get URL with correct host/port from current request
            return asset('storage/' . $fullPath);
        } catch (Exception $e) {
            Log::error('Failed to store generated image', [
                'error' => $e->getMessage(),
                'url' => $imageUrl,
            ]);

            return null;
        }
    }

    /**
     * Check if image generation is available
     */
    public function canGenerateImages(): bool
    {
        return ! empty(config('settings.ai_openai_api_key') ?: config('ai.openai.api_key'));
    }
}
