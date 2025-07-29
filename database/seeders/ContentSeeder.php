<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ActionType;
use App\Models\Post;
use App\Models\Term;
use App\Services\Content\ContentService;
use App\Concerns\HasActionLogTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ContentSeeder extends Seeder
{
    use HasActionLogTrait;

    public function __construct(private readonly ContentService $contentService)
    {
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
                'featured_image' => 'categories/uncategorized.jpg',
            ],
        ];

        foreach ($categories as $category) {
            try {
                // Generate a placeholder image instead of trying to copy
                $this->generatePlaceholderImage('public/'.$category['featured_image'], pathinfo($category['featured_image'], PATHINFO_BASENAME));

                $category = Term::firstOrCreate([
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'taxonomy' => 'category',
                    'slug' => \Illuminate\Support\Str::slug($category['name']),
                ], [
                    'description' => $category['description'],
                    'featured_image' => $category['featured_image'],
                ]);
                $this->logAction(ActionType::CREATED->value, Term::class, $category->toArray());

                $this->command->info("Created category: {$category['name']}");
            } catch (\Exception $e) {
                $this->command->error("Error creating category {$category['name']}: ".$e->getMessage());
                Log::error("Error in ContentSeeder creating category {$category['name']}: ".$e->getMessage());
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
                'featured_image' => 'tags/sample-tag.jpg',
            ],
        ];

        foreach ($tags as $tag) {
            try {
                // Generate a placeholder image instead of trying to copy
                $this->generatePlaceholderImage('public/'.$tag['featured_image'], pathinfo($tag['featured_image'], PATHINFO_BASENAME));

                $tag = Term::firstOrCreate([
                    'id' => $tag['id'],
                    'name' => $tag['name'],
                    'taxonomy' => 'tag',
                    'slug' => \Illuminate\Support\Str::slug($tag['name']),
                ], [
                    'description' => $tag['description'],
                    'featured_image' => $tag['featured_image'],
                ]);

                $this->logAction(ActionType::CREATED->value, Term::class, $tag->toArray());

                $this->command->info("Created tag: {$tag['name']}");
            } catch (\Exception $e) {
                $this->command->error("Error creating tag {$tag['name']}: ".$e->getMessage());
                Log::error("Error in ContentSeeder creating tag {$tag['name']}: ".$e->getMessage());
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
            $x = (int)((300 - $textWidth) / 2);
            $y = (int)((200 - $textHeight) / 2);

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
            $this->command->error('Error generating image: '.$e->getMessage());
            Log::error('Error generating image: '.$e->getMessage());
        }
    }

    protected function createSamplePosts(): void
    {
        $posts = [
            [
                'id' => 1,
                'title' => 'Welcome to Our Blog',
                'content' => '<p>This is the first post on our new blog. We\'re excited to share our thoughts with you!</p><p>Stay tuned for more updates.</p>',
                'excerpt' => 'Welcome to our new blog! We\'re excited to share our thoughts with you.',
                'status' => 'publish',
                'categories' => ['Uncategorized'],
                'tags' => [],
            ],
        ];

        foreach ($posts as $postData) {
            // Create post.
            $post = Post::firstOrCreate([
                'id' => $postData['id'],
                'title' => $postData['title'],
                'post_type' => 'post',
            ], [
                'slug' => \Illuminate\Support\Str::slug($postData['title']),
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'],
                'status' => $postData['status'],
                'user_id' => 1, // Assuming user ID 1 exists
                'published_at' => now()->subDays(rand(0, 30)),
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
                'content' => '<p>This is a sample page created to demonstrate the page functionality.</p><p>Feel free to edit this content in the admin panel.</p>',
                'status' => 'publish',
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Post::firstOrCreate([
                'id' => $pageData['id'],
                'title' => $pageData['title'],
                'post_type' => 'page',
            ], [
                'slug' => \Illuminate\Support\Str::slug($pageData['title']),
                'content' => $pageData['content'],
                'status' => $pageData['status'],
                'user_id' => 1, // Assuming user ID 1 exists
                'published_at' => now(),
            ]);

            $this->logAction(ActionType::CREATED->value, Post::class, $page->toArray());
        }
    }
}
