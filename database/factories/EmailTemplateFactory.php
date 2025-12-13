<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'subject' => $this->faker->sentence(),
            'body_html' => '<p>' . $this->faker->paragraph(10) . '</p>',
            'type' => 'transactional',
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
