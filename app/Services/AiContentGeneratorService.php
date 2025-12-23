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

    public function __construct()
    {
        $this->provider = config('settings.ai_default_provider', 'openai');
        $this->setApiKey();
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;
        $this->setApiKey();

        return $this;
    }

    private function setApiKey(): void
    {
        $this->apiKey = match ($this->provider) {
            'openai' => config('settings.ai_openai_api_key'),
            'claude' => config('settings.ai_claude_api_key'),
            default => throw new Exception("Unsupported AI provider: {$this->provider}")
        };

        if (empty($this->apiKey)) {
            // throw new Exception("API key not configured for provider: {$this->provider}");
            Log::error('AI Content Generator: API key not configured', [
                'provider' => $this->provider,
            ]);
        }
    }

    public function generateContent(string $prompt, string $type = 'general'): array
    {
        // Check if API key is configured before making request
        if (empty($this->apiKey)) {
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
            default => throw new Exception("Unsupported provider: {$this->provider}")
        };
    }

    private function sendOpenAiRequest(string $systemPrompt, string $userPrompt): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)
            ->post(
                'https://api.openai.com/v1/chat/completions',
                [
                    'model' => 'gpt-3.5-turbo',
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
        return Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ])->timeout(60)
            ->post(
                'https://api.anthropic.com/v1/messages',
                [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => $this->getMaxTokens(),
                    'system' => $systemPrompt,
                    'messages' => [
                        ['role' => 'user', 'content' => $userPrompt],
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
            default => throw new Exception("Unknown provider: {$this->provider}")
        };

        // Clean the content - remove markdown code blocks if present
        $cleanedContent = $this->extractJsonFromResponse($content);

        // Try to parse as JSON first
        $parsedContent = json_decode($cleanedContent, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
            // Ensure we have the required keys
            return [
                'title' => $parsedContent['title'] ?? 'Generated Title',
                'excerpt' => $parsedContent['excerpt'] ?? 'Generated excerpt from AI',
                'content' => $parsedContent['content'] ?? $content,
            ];
        }

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
     * Extract JSON from AI response, handling markdown code blocks and extra text
     */
    private function extractJsonFromResponse(string $content): string
    {
        $content = trim($content);

        // Remove markdown code blocks (```json ... ``` or ``` ... ```)
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        // Try to find JSON object in the response (starts with { and ends with })
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $content = $matches[0];
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

        // Handle Claude/Anthropic errors
        if (isset($body['error']['message'])) {
            return __('AI service error: :message', ['message' => $this->truncateMessage($body['error']['message'])]);
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
        // Check if API key is configured before making request
        if (empty($this->apiKey)) {
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

        if (config('settings.ai_openai_api_key')) {
            $providers['openai'] = 'OpenAI';
        }

        if (config('settings.ai_claude_api_key')) {
            $providers['claude'] = 'Claude (Anthropic)';
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
        $apiKey = config('settings.ai_openai_api_key');

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
        return ! empty(config('settings.ai_openai_api_key'));
    }
}
