<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\AiContentGeneratorService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiContentGeneratorServiceChatCompletionTest extends TestCase
{
    #[Test]
    public function it_returns_plain_text_from_an_openai_chat_completion(): void
    {
        config([
            'settings.ai_default_provider' => 'openai',
            'settings.ai_openai_api_key' => 'sk-test',
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Hello from core AI']],
                ],
            ]),
        ]);

        $reply = app(AiContentGeneratorService::class)->chatCompletion([
            ['role' => 'system', 'content' => 'You are helpful.'],
            ['role' => 'user', 'content' => 'Hi'],
        ]);

        $this->assertSame('Hello from core AI', $reply);
    }
}
