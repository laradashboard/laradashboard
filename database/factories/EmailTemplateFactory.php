<?php

namespace Database\Factories;

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
            'body_html' => '<p>' . $this->faker->paragraph() . '</p>',
            'type' => 'transactional',
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'is_default' => false,
            'created_by' => \App\Models\User::factory(),
            'updated_by' => null,
            'header_template_id' => null,
            'footer_template_id' => null,
        ];
    }
}
