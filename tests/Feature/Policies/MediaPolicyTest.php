<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_with_media_create_can_create(): void
    {
        $user = User::factory()->create();

        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'media.create',
            'guard_name' => 'web',
        ]);

        $user->givePermissionTo('media.create');

        $policy = app(\App\Policies\MediaPolicy::class);
        $this->assertTrue($policy->create($user));
    }

    /** @test */
    public function user_without_media_create_cannot_create(): void
    {
        $user = User::factory()->create();

        $policy = app(\App\Policies\MediaPolicy::class);
        $this->assertFalse($policy->create($user));
    }
}
