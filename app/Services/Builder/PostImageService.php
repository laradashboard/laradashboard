<?php

declare(strict_types=1);

namespace App\Services\Builder;

use App\Services\AiContentGeneratorService;
use Illuminate\Support\Str;

class PostImageService
{
    public function __construct(
        private AiContentGeneratorService $aiService,
        private BlockService $blockService,
    ) {
    }

    public function canGenerate(): bool
    {
        return $this->aiService->canGenerateImages();
    }

    /**
     * @param  array<int, array<string, string>|string>  $imageSuggestions
     * @return array<int, array{url: string, alt: string}>
     */
    public function generateImages(string $topic, string $title, int $count = 1, array $imageSuggestions = []): array
    {
        if (! $this->canGenerate()) {
            return [];
        }

        $count = min(3, max(1, $count));

        if ($imageSuggestions === []) {
            $imageSuggestions = $this->generateImagePrompts($topic, $title, $count);
        }

        $imageSuggestions = array_slice($imageSuggestions, 0, $count);
        $images = [];

        foreach ($imageSuggestions as $suggestion) {
            $prompt = is_array($suggestion)
                ? ($suggestion['prompt'] ?? $suggestion['description'] ?? $topic)
                : $suggestion;
            $alt = is_array($suggestion)
                ? ($suggestion['alt'] ?? $prompt)
                : $prompt;

            $result = $this->aiService->generateImage($prompt, '1536x1024');

            if ($result === null) {
                continue;
            }

            $localUrl = $this->aiService->storeGeneratedImage($result);

            if ($localUrl) {
                $images[] = [
                    'url' => $localUrl,
                    'alt' => Str::limit((string) $alt, 100),
                ];
            }
        }

        return $images;
    }

    /**
     * @param  array<int, array{url: string, alt: string}>  $images
     * @return array<int, array<string, mixed>>
     */
    public function prependFeaturedImageBlocks(array $blocks, array $images): array
    {
        if ($images === []) {
            return $blocks;
        }

        $featuredImage = array_shift($images);
        $preface = [
            $this->blockService->image(
                $featuredImage['url'],
                $featuredImage['alt'],
                '100%',
                'center'
            ),
            $this->blockService->spacer('24px'),
        ];

        return array_merge($preface, $blocks);
    }

    /**
     * @return array<int, string>
     */
    protected function generateImagePrompts(string $topic, string $title, int $count): array
    {
        $prompts = [
            "A professional, visually appealing header image for a blog post about: {$topic}. Modern, clean design suitable for web content.",
        ];

        if ($count >= 2) {
            $prompts[] = "An illustrative image that visually explains concepts related to: {$topic}. Clear, informative, suitable for educational content.";
        }

        if ($count >= 3) {
            $prompts[] = "An inspiring, engaging image to conclude an article about: {$topic}. Motivational and professional.";
        }

        return array_slice($prompts, 0, $count);
    }
}
