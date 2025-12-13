<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Services\Builder\BlockService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $blockService = app(BlockService::class);

        return [
            'title' => fake()->sentence(),
            'content' => $blockService->parseBlocks([
                $blockService->heading(fake()->sentence(6), 'h2'),
                $blockService->text(fake()->paragraph()),
            ]),
            'design_json' => [
                'blocks' => [
                    $blockService->heading(fake()->sentence(6), 'h2'),
                    $blockService->text(fake()->paragraph()),
                ],
                'version' => 1,
            ],
            'excerpt' => fake()->sentence(10),
            'status' => fake()->randomElement(collect(PostStatus::cases())->pluck('value')->toArray()),
            'post_type' => fake()->randomElement(['post', 'page']),
            'slug' => fake()->unique()->slug(),
            'user_id' => \App\Models\User::factory(),

            // Create at and update_at would be random time between 1 year ago and now
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'published_at' => fake()->optional(0.5)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
