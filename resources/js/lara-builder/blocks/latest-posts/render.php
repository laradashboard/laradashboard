<?php

/**
 * Latest Posts Block - Server-side Renderer
 *
 * Renders a dynamic grid of latest published posts.
 * When interactive mode is enabled, renders a Livewire component
 * with search, filter, sort, and pagination.
 * When disabled, renders a static grid of posts.
 */

use App\Models\Post;
use App\Models\Term;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    if (! class_exists(Post::class)) {
        return '';
    }

    $interactive = (bool) ($props['interactive'] ?? false);
    $postsCount = (int) ($props['postsCount'] ?? 6);
    $columns = (int) ($props['columns'] ?? 3);
    $categorySlug = $props['categorySlug'] ?? '';
    $showExcerpt = (bool) ($props['showExcerpt'] ?? true);
    $showImage = (bool) ($props['showImage'] ?? true);
    $showDate = (bool) ($props['showDate'] ?? true);
    $showAuthor = (bool) ($props['showAuthor'] ?? false);
    $headingText = $props['headingText'] ?? '';
    $layout = $props['layout'] ?? 'grid';
    $postRoute = $props['postRoute'] ?? 'starter26.post';
    $showSearch = (bool) ($props['showSearch'] ?? true);
    $showCategoryFilter = (bool) ($props['showCategoryFilter'] ?? true);
    $showSort = (bool) ($props['showSort'] ?? true);

    $layoutStyles = $props['layoutStyles'] ?? [];
    $customClass = $props['customClass'] ?? '';

    // Build inline styles from layoutStyles
    $styles = [];
    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($layoutStyles['margin'][$side])) {
                $styles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['padding'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($layoutStyles['padding'][$side])) {
                $styles[] = "padding-{$side}: {$layoutStyles['padding'][$side]}";
            }
        }
    }
    $styleAttr = ! empty($styles) ? ' style="' . e(implode('; ', $styles)) . '"' : '';

    // Heading HTML (shared between interactive and static modes)
    $headingHtml = '';
    if (! empty($headingText)) {
        $headingHtml = '<h2 class="st:text-2xl st:md:text-3xl st:font-bold st:text-gray-900 st:dark:text-white st:mb-8 st:text-center">' . e($headingText) . '</h2>';
    }

    // Interactive mode: render the Livewire PostsListing component
    if ($interactive && class_exists(\Livewire\Livewire::class)) {
        $livewireComponentName = 'starter26::components.posts-listing';

        // Check if the Livewire component is registered
        try {
            $livewireHtml = Blade::render(
                '@livewire($component, $params)',
                [
                    'component' => $livewireComponentName,
                    'params' => [
                        'postsPerPage' => $postsCount,
                        'columns' => $columns,
                        'showSearch' => $showSearch,
                        'showCategoryFilter' => $showCategoryFilter,
                        'showSort' => $showSort,
                        'showExcerpt' => $showExcerpt,
                        'showImage' => $showImage,
                        'showDate' => $showDate,
                        'categorySlug' => $categorySlug,
                        'postRoute' => $postRoute,
                    ],
                ]
            );

            $blockClasses = 'lb-block lb-latest-posts';
            if (! empty($customClass)) {
                $blockClasses .= ' ' . e($customClass);
            }

            return '<div class="' . e($blockClasses) . '"' . $styleAttr . '>'
                . $headingHtml
                . $livewireHtml
                . '</div>';
        } catch (\Throwable $e) {
            // Fall through to static rendering if Livewire component isn't available
        }
    }

    // Static mode: render a simple grid of posts
    $query = Post::query()
        ->where('status', 'published')
        ->where('post_type', 'post')
        ->orderBy('created_at', 'desc');

    if (! empty($categorySlug) && class_exists(Term::class)) {
        $query->whereHas('terms', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug)->where('taxonomy', 'category');
        });
    }

    $posts = $query->limit($postsCount)->get();

    if ($posts->isEmpty() && empty($headingText)) {
        return '';
    }

    $colClass = match ($columns) {
        1 => 'st:grid-cols-1',
        2 => 'st:grid-cols-1 st:md:grid-cols-2',
        4 => 'st:grid-cols-1 st:md:grid-cols-2 st:lg:grid-cols-4',
        default => 'st:grid-cols-1 st:md:grid-cols-2 st:lg:grid-cols-3',
    };

    $blockClasses = 'lb-block lb-latest-posts';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $ariaLabel = ! empty($headingText) ? e(strip_tags($headingText)) : 'Latest posts';

    $html = '<section class="' . e($blockClasses) . '"' . $styleAttr . ' aria-label="' . $ariaLabel . '">';
    $html .= $headingHtml;

    if ($posts->isNotEmpty()) {
        $gridClass = $layout === 'list' ? 'st:space-y-6' : "st:grid {$colClass} st:gap-6";
        $html .= '<div class="' . $gridClass . '">';

        foreach ($posts as $post) {
            $postUrl = '#';
            try {
                if (\Illuminate\Support\Facades\Route::has($postRoute)) {
                    $postUrl = route($postRoute, $post->slug);
                }
            } catch (\Exception $e) {
                $postUrl = url('/post/' . $post->slug);
            }

            $escapedTitle = e($post->title);
            $dateFormatted = $post->created_at?->format('M d, Y') ?? '';
            $dateISO = $post->created_at?->toISOString() ?? '';
            $excerpt = e(Str::limit(strip_tags($post->content ?? $post->excerpt ?? ''), 120));

            if ($layout === 'list') {
                $html .= '<article class="st:flex st:gap-6 st:bg-white st:dark:bg-gray-800 st:rounded-xl st:border st:border-gray-200 st:dark:border-gray-700 st:overflow-hidden st:transition-shadow st:hover:shadow-lg">';

                if ($showImage) {
                    if ($post->featured_image) {
                        $html .= '<div class="st:w-48 st:shrink-0"><img src="' . e($post->featured_image) . '" alt="' . $escapedTitle . '" class="st:w-full st:h-full st:object-cover" loading="lazy"></div>';
                    } else {
                        $html .= '<div class="st:w-48 st:shrink-0 st:bg-gradient-to-br st:from-gray-100 st:to-gray-200 st:dark:from-gray-700 st:dark:to-gray-800 st:flex st:items-center st:justify-center"><iconify-icon icon="lucide:image" class="st:text-4xl st:text-gray-400" aria-hidden="true"></iconify-icon></div>';
                    }
                }

                $html .= '<div class="st:flex st:flex-col st:justify-center st:p-4">';

                if ($showDate) {
                    $html .= '<div class="st:text-xs st:text-gray-500 st:dark:text-gray-400 st:mb-2"><time datetime="' . e($dateISO) . '">' . e($dateFormatted) . '</time></div>';
                }

                $html .= '<h3 class="st:text-lg st:font-semibold st:text-gray-900 st:dark:text-white st:mb-2"><a href="' . e($postUrl) . '" class="st:hover:text-primary st:transition-colors">' . $escapedTitle . '</a></h3>';

                if ($showExcerpt && $excerpt) {
                    $html .= '<p class="st:text-sm st:text-gray-600 st:dark:text-gray-400 st:line-clamp-2">' . $excerpt . '</p>';
                }

                $html .= '</div></article>';
            } else {
                $html .= '<article class="st:bg-white st:dark:bg-gray-800 st:rounded-xl st:border st:border-gray-200 st:dark:border-gray-700 st:overflow-hidden st:transition-shadow st:hover:shadow-lg">';

                if ($showImage) {
                    if ($post->featured_image) {
                        $html .= '<img src="' . e($post->featured_image) . '" alt="' . $escapedTitle . '" class="st:aspect-video st:w-full st:object-cover" loading="lazy">';
                    } else {
                        $html .= '<div class="st:aspect-video st:bg-gradient-to-br st:from-gray-100 st:to-gray-200 st:dark:from-gray-700 st:dark:to-gray-800 st:flex st:items-center st:justify-center"><iconify-icon icon="lucide:image" class="st:text-4xl st:text-gray-400" aria-hidden="true"></iconify-icon></div>';
                    }
                }

                $html .= '<div class="st:p-5">';

                if ($showDate || $showAuthor) {
                    $html .= '<div class="st:flex st:items-center st:gap-3 st:text-xs st:text-gray-500 st:dark:text-gray-400 st:mb-3">';
                    if ($showDate) {
                        $html .= '<time datetime="' . e($dateISO) . '">' . e($dateFormatted) . '</time>';
                    }
                    if ($showAuthor && $post->user) {
                        $html .= '<span>' . e($post->user->name) . '</span>';
                    }
                    $html .= '</div>';
                }

                $html .= '<h3 class="st:text-lg st:font-semibold st:text-gray-900 st:dark:text-white st:mb-2"><a href="' . e($postUrl) . '" class="st:hover:text-primary st:transition-colors">' . $escapedTitle . '</a></h3>';

                if ($showExcerpt && $excerpt) {
                    $html .= '<p class="st:text-sm st:text-gray-600 st:dark:text-gray-400 st:line-clamp-3">' . $excerpt . '</p>';
                }

                $html .= '</div></article>';
            }
        }

        $html .= '</div>';
    }

    $html .= '</section>';

    return $html;
};
