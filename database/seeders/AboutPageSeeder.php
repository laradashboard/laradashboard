<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use App\Services\Builder\BlockService;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates an About page using the page builder with design_json.
     */
    public function run(): void
    {
        // Check if About page already exists
        $existingPage = Post::query()
            ->where('slug', 'about')
            ->where('post_type', 'page')
            ->first();

        if ($existingPage) {
            $this->command->info('About page already exists. Skipping...');

            return;
        }

        $blockService = app(BlockService::class);
        $canvasSettings = $blockService->getDefaultPageCanvasSettings();

        // Build the page blocks
        $blocks = $this->getAboutPageBlocks($blockService);

        // Create the About page
        Post::create([
            'user_id' => 1,
            'post_type' => 'page',
            'title' => 'About Us',
            'slug' => 'about',
            'excerpt' => 'Learn more about our mission, our team, and what drives us to create great content.',
            'content' => $blockService->generatePageHtml($blocks, $canvasSettings),
            'design_json' => [
                'blocks' => $blocks,
                'canvasSettings' => $canvasSettings,
                'version' => 1,
            ],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->command->info('About page created successfully with page builder design!');
    }

    /**
     * Get the block structure for the About page.
     */
    private function getAboutPageBlocks(BlockService $b): array
    {
        return [
            // Hero Section
            $b->section(
                children: [
                    $b->heading('About Us', 'h1', 'center', '#111827', '48px'),
                    $b->text('Learn more about our mission, our team, and what drives us to create great content.', 'center', '#6b7280', '18px'),
                ],
                backgroundType: 'gradient',
                gradientFrom: '#f9fafb',
                gradientTo: '#f3f4f6',
                gradientDirection: 'to-br'
            ),

            // Mission Section
            $b->section(
                children: [
                    $b->columns(
                        children: [
                            // Left column - Text content
                            [
                                $b->heading('Our Mission', 'h2', 'left', '#111827', '28px'),
                                $b->text('We are dedicated to providing high-quality content and resources to help our community grow and succeed. Our platform is built with love using the latest technologies.', 'left', '#6b7280', '16px'),
                                $b->text('Whether you are looking for tutorials, insights, or inspiration, we have got you covered. Join us on this journey of continuous learning and improvement.', 'left', '#6b7280', '16px'),
                            ],
                            // Right column - Icon
                            [
                                $b->icon('lucide:target', '128px', '#3b82f6', 'center', '#dbeafe', 'rounded', '48px'),
                            ],
                        ],
                        columns: 2,
                        gap: '48px',
                        verticalAlign: 'center'
                    ),
                ],
                backgroundColor: '#ffffff'
            ),

            // Stats Section
            $b->section(
                children: [
                    $b->columns(
                        children: [
                            [$b->statsItem('1000', 'Users', '+')],
                            [$b->statsItem('500', 'Articles', '+')],
                            [$b->statsItem('50', 'Categories', '+')],
                            [$b->statsItem('99', 'Satisfaction', '%')],
                        ],
                        columns: 4,
                        gap: '32px',
                        verticalAlign: 'center'
                    ),
                ],
                backgroundColor: '#f9fafb'
            ),

            // Team Section
            $b->section(
                children: [
                    $b->heading('Meet Our Team', 'h2', 'center', '#111827', '32px'),
                    $b->text('The talented people behind our success.', 'center', '#6b7280', '18px'),
                    $b->spacer('32px'),
                    $b->columns(
                        children: [
                            [
                                $b->featureBox(
                                    title: 'John Doe',
                                    description: 'Founder & CEO - Passionate about building great products.',
                                    icon: 'lucide:user',
                                    iconSize: '48px',
                                    iconColor: '#9ca3af',
                                    iconBackgroundColor: '#f3f4f6',
                                    layoutStyles: [
                                        'padding' => ['top' => '24px', 'bottom' => '24px', 'left' => '16px', 'right' => '16px'],
                                        'background' => ['color' => '#f9fafb'],
                                        'border' => ['radius' => ['topLeft' => '12px', 'topRight' => '12px', 'bottomLeft' => '12px', 'bottomRight' => '12px']],
                                    ]
                                ),
                            ],
                            [
                                $b->featureBox(
                                    title: 'Jane Smith',
                                    description: 'Lead Developer - Full-stack developer with 10+ years experience.',
                                    icon: 'lucide:user',
                                    iconSize: '48px',
                                    iconColor: '#9ca3af',
                                    iconBackgroundColor: '#f3f4f6',
                                    layoutStyles: [
                                        'padding' => ['top' => '24px', 'bottom' => '24px', 'left' => '16px', 'right' => '16px'],
                                        'background' => ['color' => '#f9fafb'],
                                        'border' => ['radius' => ['topLeft' => '12px', 'topRight' => '12px', 'bottomLeft' => '12px', 'bottomRight' => '12px']],
                                    ]
                                ),
                            ],
                            [
                                $b->featureBox(
                                    title: 'Mike Johnson',
                                    description: 'Designer - Creating beautiful user experiences.',
                                    icon: 'lucide:user',
                                    iconSize: '48px',
                                    iconColor: '#9ca3af',
                                    iconBackgroundColor: '#f3f4f6',
                                    layoutStyles: [
                                        'padding' => ['top' => '24px', 'bottom' => '24px', 'left' => '16px', 'right' => '16px'],
                                        'background' => ['color' => '#f9fafb'],
                                        'border' => ['radius' => ['topLeft' => '12px', 'topRight' => '12px', 'bottomLeft' => '12px', 'bottomRight' => '12px']],
                                    ]
                                ),
                            ],
                        ],
                        columns: 3,
                        gap: '32px',
                        verticalAlign: 'stretch'
                    ),
                ],
                backgroundColor: '#ffffff'
            ),

            // Values Section
            $b->section(
                children: [
                    $b->heading('Our Values', 'h2', 'center', '#111827', '32px'),
                    $b->text('The principles that guide everything we do.', 'center', '#6b7280', '18px'),
                    $b->spacer('32px'),
                    $b->columns(
                        children: [
                            [
                                $b->featureBox(
                                    title: 'Innovation',
                                    description: 'We constantly explore new ideas and technologies to deliver the best experience.',
                                    icon: 'lucide:lightbulb',
                                    iconSize: '32px',
                                    iconColor: '#2563eb',
                                    iconBackgroundColor: '#dbeafe'
                                ),
                            ],
                            [
                                $b->featureBox(
                                    title: 'Quality',
                                    description: 'We are committed to delivering high-quality content and exceptional user experiences.',
                                    icon: 'lucide:heart',
                                    iconSize: '32px',
                                    iconColor: '#16a34a',
                                    iconBackgroundColor: '#dcfce7'
                                ),
                            ],
                            [
                                $b->featureBox(
                                    title: 'Community',
                                    description: 'We believe in building a supportive community where everyone can learn and grow together.',
                                    icon: 'lucide:users',
                                    iconSize: '32px',
                                    iconColor: '#9333ea',
                                    iconBackgroundColor: '#f3e8ff'
                                ),
                            ],
                        ],
                        columns: 3,
                        gap: '32px',
                        verticalAlign: 'start'
                    ),
                ],
                backgroundColor: '#f9fafb'
            ),
        ];
    }
}
