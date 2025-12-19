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
            throw new Exception('AI API request failed: ' . $response->body());
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
}
