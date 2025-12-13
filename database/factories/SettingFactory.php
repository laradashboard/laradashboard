<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'option_name' => $this->faker->unique()->word(),
            'option_value' => $this->faker->word(),
            'autoload' => true,
        ];
    }

    public function mailFromAddress($address = 'dev@example.com', $name = 'Laravel App')
    {
        return $this->state([
            'option_name' => 'mail_from_address',
            'option_value' => $address,
            'autoload' => true,
        ]);
    }

    public function mailFromName($name = 'Laravel App')
    {
        return $this->state([
            'option_name' => 'mail_from_name',
            'option_value' => $name,
            'autoload' => true,
        ]);
    }
}
