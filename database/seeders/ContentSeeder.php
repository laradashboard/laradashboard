<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ActionType;
use App\Models\Post;
use App\Models\Term;
use App\Services\Content\ContentService;
use App\Concerns\HasActionLogTrait;
use App\Enums\PostStatus;
use App\Services\Builder\BlockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    use HasActionLogTrait;

    public function __construct(
        private readonly ContentService $contentService,
        private readonly BlockService $blockService
    ) {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if required tables exist.
        if (! Schema::hasTable('taxonomies') || ! Schema::hasTable('posts') || ! Schema::hasTable('terms')) {
            $this->command->info('Content tables not yet migrated. Skipping content seeding.');

            return;
        }

        $this->command->info('Seeding content...');

        $this->registerPostTypesAndTaxonomies();
        $this->createSampleCategories();
        $this->createSampleTags();
        $this->createSamplePages();
        $this->createSamplePosts();

        $this->command->info('Content seeded successfully!');
    }

    /**
     * Register post types and taxonomies
     */
    protected function registerPostTypesAndTaxonomies(): void
    {
        // Register post type.
        $this->contentService->registerPostType([
            'name' => 'post',
            'label' => 'Posts',
            'label_singular' => 'Post',
            'description' => 'Default post type for blog entries',
            'taxonomies' => ['category', 'tag'],
        ]);

        // Register page type.
        $this->contentService->registerPostType([
            'name' => 'page',
            'label' => 'Pages',
            'label_singular' => 'Page',
            'description' => 'Default post type for static pages',
            'has_archive' => false,
            'hierarchical' => true,
            'supports_excerpt' => false,
            'taxonomies' => [],
        ]);

        // Register category taxonomy.
        $this->contentService->registerTaxonomy([
            'name' => 'category',
            'label' => 'Categories',
            'label_singular' => 'Category',
            'description' => 'Default taxonomy for categorizing posts',
            'hierarchical' => true,
            'show_featured_image' => true,
        ], 'post');

        // Register tag taxonomy.
        $this->contentService->registerTaxonomy([
            'name' => 'tag',
            'label' => 'Tags',
            'label_singular' => 'Tag',
            'description' => 'Default taxonomy for tagging posts',
            'hierarchical' => false,
            'show_featured_image' => true,
        ], 'post');
    }

    protected function createSampleCategories(): void
    {
        $this->command->info('Creating sample categories...');

        // Make sure storage directory exists.
        Storage::disk('public')->makeDirectory('categories');

        $categories = [
            [
                'id' => 1,
                'name' => 'Uncategorized',
                'slug' => 'uncategorized',
                'description' => 'Default category for posts that do not belong to any other category.',
            ],
        ];

        foreach ($categories as $category) {
            try {
                $category = Term::firstOrCreate([
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'taxonomy' => 'category',
                    'slug' => \Illuminate\Support\Str::slug($category['name']),
                ], [
                    'description' => $category['description'],
                ]);
                $this->logAction(ActionType::CREATED->value, Term::class, $category->toArray());

                $this->command->info("Created category: {$category['name']}");
            } catch (\Exception $e) {
                $this->command->error("Error creating category {$category['name']}: " . $e->getMessage());
                Log::error("Error in ContentSeeder creating category {$category['name']}: " . $e->getMessage());
            }
        }
    }

    protected function createSampleTags(): void
    {
        $this->command->info('Creating sample tags...');

        // Make sure storage directory exists.
        Storage::disk('public')->makeDirectory('tags');

        $tags = [
            [
                'id' => 2,
                'name' => 'Sample Tag',
                'slug' => 'sample-tag',
                'description' => 'A sample tag for demonstration purposes.',
            ],
        ];

        foreach ($tags as $tag) {
            try {
                $tag = Term::firstOrCreate([
                    'id' => $tag['id'],
                    'name' => $tag['name'],
                    'taxonomy' => 'tag',
                    'slug' => \Illuminate\Support\Str::slug($tag['name']),
                ], [
                    'description' => $tag['description'],
                ]);

                $this->logAction(ActionType::CREATED->value, Term::class, $tag->toArray());

                $this->command->info("Created tag: {$tag['name']}");
            } catch (\Exception $e) {
                $this->command->error("Error creating tag {$tag['name']}: " . $e->getMessage());
                Log::error("Error in ContentSeeder creating tag {$tag['name']}: " . $e->getMessage());
            }
        }
    }

    /**
     * Generate a placeholder image directly to storage
     *
     * @param  string  $path  Full path including disk name
     * @param  string  $text  Text to display on image
     */
    protected function generatePlaceholderImage(string $path, string $text): void
    {
        try {
            // Parse the path to get disk and path
            $parts = explode('/', $path, 2);
            $disk = $parts[0];
            $imagePath = $parts[1];

            // Skip if image already exists
            if (Storage::disk(name: $disk)->exists($imagePath)) {
                return;
            }

            // Skip if GD library is not available
            if (! extension_loaded('gd')) {
                $this->command->warn('GD library not available. Skipping image creation.');

                return;
            }

            // Create a 300x200 image
            $image = imagecreatetruecolor(300, 200);

            // Generate a semi-random color based on text
            $hash = md5($text);
            $r = hexdec(substr($hash, 0, 2));
            $g = hexdec(substr($hash, 2, 2));
            $b = hexdec(substr($hash, 4, 2));

            // Fill background
            $bgColor = imagecolorallocate($image, $r, $g, $b);
            imagefill($image, 0, 0, $bgColor);

            // Add text (shortened version of the filename)
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $textToDisplay = pathinfo($text, PATHINFO_FILENAME);
            $textToDisplay = substr($textToDisplay, 0, 20); // Limit length

            // Position text in center
            $fontSize = 5;
            $textWidth = imagefontwidth($fontSize) * strlen($textToDisplay);
            $textHeight = imagefontheight($fontSize);
            $x = (int) ((300 - $textWidth) / 2);
            $y = (int) ((200 - $textHeight) / 2);

            // Draw text
            imagestring($image, $fontSize, $x, $y, $textToDisplay, $textColor);

            // Save the image to a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'img');
            imagejpeg($image, $tempFile, 90);
            imagedestroy($image);

            // Store the file
            Storage::disk($disk)->put($imagePath, file_get_contents($tempFile));

            // Remove temp file
            unlink($tempFile);

            $this->command->info("Generated placeholder image: $imagePath");
        } catch (\Exception $e) {
            $this->command->error('Error generating image: ' . $e->getMessage());
            Log::error('Error generating image: ' . $e->getMessage());
        }
    }

    protected function createSamplePosts(): void
    {
        $posts = [
            [
                'id' => 1,
                'title' => 'Welcome to Our Blog',
                'excerpt' => 'Welcome to our new blog! We\'re excited to share our thoughts with you.',
                'status' => PostStatus::PUBLISHED->value,
                'categories' => ['Uncategorized'],
                'tags' => [],
                'blocks' => [
                    $this->blockService->heading('Welcome to Our Blog'),
                    $this->blockService->text('This is the first post on our new blog. We\'re excited to share our thoughts with you!'),
                    $this->blockService->spacer('16px'),
                    $this->blockService->text('Stay tuned for more updates.'),
                ],
            ],
        ];

        foreach ($posts as $postData) {
            $post = Post::firstOrCreate([
                'id' => $postData['id'],
                'title' => $postData['title'],
                'post_type' => 'post',
            ], [
                'slug' => Str::slug($postData['title']),
                'excerpt' => $postData['excerpt'],
                'status' => $postData['status'],
                'user_id' => 1, // Assuming user ID 1 exists
                'published_at' => now()->subDays(rand(0, 30)),
                'design_json' => [
                    'blocks' => $postData['blocks'],
                    'version' => 1,
                ],
                'content' => $this->blockService->parseBlocks($postData['blocks']),
            ]);

            $this->logAction(ActionType::CREATED->value, Post::class, $post->toArray());

            // Attach categories.
            $categoryIds = [];
            if (! empty($postData['categories'])) {
                $categoryIds = Term::whereIn('name', $postData['categories'])
                    ->where('taxonomy', 'category')
                    ->pluck('id')
                    ->toArray();
                $post->terms()->syncWithoutDetaching($categoryIds);
            }

            // Attach tags.
            $tagIds = [];
            if (! empty($postData['tags'])) {
                $tagIds = Term::whereIn('name', $postData['tags'])
                    ->where('taxonomy', 'tag')
                    ->pluck('id')
                    ->toArray();
                $post->terms()->syncWithoutDetaching($tagIds);
            }
        }

        Post::factory()->count(50)->create();
    }

    protected function createSamplePages(): void
    {
        $pages = [
            [
                'id' => 2,
                'title' => 'Sample Page',
                'status' => PostStatus::PUBLISHED->value,
                'blocks' => [
                    $this->blockService->heading('Sample Page', 'h1'),
                    $this->blockService->text('This is a sample page created to demonstrate the page functionality.'),
                    $this->blockService->spacer('16px'),
                    $this->blockService->text('Feel free to edit this content in the admin panel.'),
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Post::firstOrCreate([
                'id' => $pageData['id'],
                'title' => $pageData['title'],
                'post_type' => 'page',
            ], [
                'slug' => Str::slug($pageData['title']),
                'status' => $pageData['status'],
                'user_id' => 1, // Assuming user ID 1 exists
                'published_at' => now(),
                'design_json' => [
                    'blocks' => $pageData['blocks'],
                    'version' => 1,
                ],
                'content' => $this->blockService->parseBlocks($pageData['blocks']),
            ]);

            $this->logAction(ActionType::CREATED->value, Post::class, $page->toArray());
        }
    }
}
