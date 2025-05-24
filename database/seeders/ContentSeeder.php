<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Term;
use App\Services\ContentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ContentSeeder extends Seeder
{
    protected $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if required tables exist
        if (!Schema::hasTable('taxonomies') || !Schema::hasTable('posts') || !Schema::hasTable('terms')) {
            $this->command->info('Content tables not yet migrated. Skipping content seeding.');
            return;
        }

        $this->command->info('Seeding content...');

        // Register post types and taxonomies
        $this->registerPostTypesAndTaxonomies();

        // Create sample categories
        $this->createSampleCategories();

        // Create sample tags
        $this->createSampleTags();

        // Create sample posts
        $this->createSamplePosts();

        // Create sample pages
        $this->createSamplePages();

        $this->command->info('Content seeded successfully!');
    }

    /**
     * Register post types and taxonomies
     */
    protected function registerPostTypesAndTaxonomies(): void
    {
        // Register post type
        $this->contentService->registerPostType([
            'name' => 'post',
            'label' => 'Posts',
            'label_singular' => 'Post',
            'description' => 'Default post type for blog entries',
            'taxonomies' => ['category', 'tag']
        ]);

        // Register page type
        $this->contentService->registerPostType([
            'name' => 'page',
            'label' => 'Pages',
            'label_singular' => 'Page',
            'description' => 'Default post type for static pages',
            'has_archive' => false,
            'hierarchical' => true,
            'supports_excerpt' => false,
            'taxonomies' => []
        ]);

        // Register category taxonomy
        $this->contentService->registerTaxonomy([
            'name' => 'category',
            'label' => 'Categories',
            'label_singular' => 'Category',
            'description' => 'Default taxonomy for categorizing posts',
            'hierarchical' => true,
        ], 'post');

        // Register tag taxonomy
        $this->contentService->registerTaxonomy([
            'name' => 'tag',
            'label' => 'Tags',
            'label_singular' => 'Tag',
            'description' => 'Default taxonomy for tagging posts',
            'hierarchical' => false,
        ], 'post');
    }

    /**
     * Create sample categories
     */
    protected function createSampleCategories(): void
    {
        $categories = [
            ['name' => 'News', 'description' => 'Latest news and updates'],
            ['name' => 'Technology', 'description' => 'Tech-related content'],
            ['name' => 'Tutorials', 'description' => 'Step-by-step tutorials'],
            ['name' => 'Events', 'description' => 'Upcoming and past events'],
        ];

        foreach ($categories as $category) {
            Term::firstOrCreate([
                'name' => $category['name'],
                'taxonomy' => 'category',
                'slug' => \Illuminate\Support\Str::slug($category['name']),
            ], [
                'description' => $category['description'],
            ]);
        }
    }

    /**
     * Create sample tags
     */
    protected function createSampleTags(): void
    {
        $tags = [
            ['name' => 'Laravel', 'description' => 'Laravel framework content'],
            ['name' => 'PHP', 'description' => 'PHP language content'],
            ['name' => 'JavaScript', 'description' => 'JavaScript language content'],
            ['name' => 'Vue.js', 'description' => 'Vue.js framework content'],
            ['name' => 'React', 'description' => 'React library content'],
            ['name' => 'CSS', 'description' => 'CSS styles content'],
        ];

        foreach ($tags as $tag) {
            Term::firstOrCreate([
                'name' => $tag['name'],
                'taxonomy' => 'tag',
                'slug' => \Illuminate\Support\Str::slug($tag['name']),
            ], [
                'description' => $tag['description'],
            ]);
        }
    }

    /**
     * Create sample posts
     */
    protected function createSamplePosts(): void
    {
        $posts = [
            [
                'title' => 'Welcome to Our Blog',
                'content' => '<p>This is the first post on our new blog. We\'re excited to share our thoughts with you!</p><p>Stay tuned for more updates.</p>',
                'excerpt' => 'Welcome to our new blog! We\'re excited to share our thoughts with you.',
                'status' => 'publish',
                'categories' => ['News'],
                'tags' => ['Laravel', 'PHP'],
            ],
            [
                'title' => 'Getting Started with Laravel',
                'content' => '<p>Laravel is a web application framework with expressive, elegant syntax.</p><p>In this tutorial, we\'ll go through the basics of Laravel framework.</p>',
                'excerpt' => 'A beginner-friendly introduction to the Laravel framework.',
                'status' => 'publish',
                'categories' => ['Tutorials', 'Technology'],
                'tags' => ['Laravel', 'PHP'],
            ],
            [
                'title' => 'Upcoming Conference',
                'content' => '<p>We\'re excited to announce our upcoming conference on web development.</p><p>The event will feature speakers from various industry leaders.</p>',
                'excerpt' => 'Join us for our upcoming web development conference.',
                'status' => 'publish',
                'categories' => ['Events'],
                'tags' => ['Laravel', 'JavaScript', 'CSS'],
            ],
        ];

        foreach ($posts as $postData) {
            // Create post
            $post = Post::firstOrCreate([
                'title' => $postData['title'],
                'post_type' => 'post',
            ], [
                'slug' => \Illuminate\Support\Str::slug($postData['title']),
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'],
                'status' => $postData['status'],
                'user_id' => 1, // Assuming user ID 1 exists
                'published_at' => now(),
            ]);

            // Attach categories
            if (isset($postData['categories'])) {
                $categoryIds = Term::whereIn('name', $postData['categories'])
                    ->where('taxonomy', 'category')
                    ->pluck('id')
                    ->toArray();
                
                $post->terms()->syncWithoutDetaching($categoryIds);
            }

            // Attach tags
            if (isset($postData['tags'])) {
                $tagIds = Term::whereIn('name', $postData['tags'])
                    ->where('taxonomy', 'tag')
                    ->pluck('id')
                    ->toArray();
                
                $post->terms()->syncWithoutDetaching($tagIds);
            }
        }
    }

    /**
     * Create sample pages
     */
    protected function createSamplePages(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'content' => '<p>Welcome to our About Us page. We are a team of dedicated professionals.</p><p>Our mission is to provide high-quality content and services to our users.</p>',
                'status' => 'publish',
            ],
            [
                'title' => 'Contact Us',
                'content' => '<p>Have questions? We\'d love to hear from you!</p><p>You can reach us via email or using the contact form below.</p>',
                'status' => 'publish',
            ],
            [
                'title' => 'Terms of Service',
                'content' => '<p>These Terms of Service ("Terms") govern your access to and use of our website and services.</p><p>By accessing or using our service, you agree to be bound by these Terms.</p>',
                'status' => 'publish',
            ],
        ];

        foreach ($pages as $pageData) {
            Post::firstOrCreate([
                'title' => $pageData['title'],
                'post_type' => 'page',
            ], [
                'slug' => \Illuminate\Support\Str::slug($pageData['title']),
                'content' => $pageData['content'],
                'status' => $pageData['status'],
                'user_id' => 1, // Assuming user ID 1 exists
                'published_at' => now(),
            ]);
        }
    }
}
