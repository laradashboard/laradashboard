<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\McpOAuthClient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<McpOAuthClient>
 */
class McpOAuthClientFactory extends Factory
{
    protected $model = McpOAuthClient::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => (string) Str::uuid(),
            'name' => $this->faker->company().' MCP',
            'redirect_uris' => ['https://'.$this->faker->domainName().'/oauth/callback'],
        ];
    }
}
