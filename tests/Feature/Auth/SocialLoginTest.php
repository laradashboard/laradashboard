<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    // Enable social login globally
    Setting::create([
        'option_name' => 'auth_show_social_login',
        'option_value' => '1',
    ]);

    // Enable Google provider
    Setting::create([
        'option_name' => 'auth_social_enable_google',
        'option_value' => '1',
    ]);
    Setting::create([
        'option_name' => 'auth_social_google_client_id',
        'option_value' => 'test-client-id',
    ]);
    Setting::create([
        'option_name' => 'auth_social_google_client_secret',
        'option_value' => 'test-client-secret',
    ]);

    // Reload config
    $settings = Setting::pluck('option_value', 'option_name')->toArray();
    foreach ($settings as $key => $value) {
        config(['settings.'.$key => $value]);
    }
});

test('social login redirect returns error when provider is disabled', function () {
    // Disable the provider
    Setting::where('option_name', 'auth_social_enable_google')->update(['option_value' => '0']);
    config(['settings.auth_social_enable_google' => '0']);

    $response = $this->get('/auth/google/redirect');

    $response->assertRedirect('/login');
    $response->assertSessionHas('error');
});

test('social login redirect returns error for invalid provider', function () {
    $response = $this->get('/auth/invalid-provider/redirect');

    $response->assertStatus(404);
});

test('social login callback creates new user when user does not exist', function () {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('12345');
    $socialiteUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
    $socialiteUser->shouldReceive('getNickname')->andReturn('johndoe');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $socialiteUser->token = 'test-token';
    $socialiteUser->refreshToken = 'test-refresh-token';
    $socialiteUser->expiresIn = 3600;

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()
            ->shouldReceive('user')
            ->andReturn($socialiteUser)
            ->getMock());

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect();
    $this->assertAuthenticated();

    // Verify user was created
    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->first_name)->toBe('John');
    expect($user->last_name)->toBe('Doe');
    expect($user->email_verified_at)->not->toBeNull();

    // Verify social account was linked
    $socialAccount = SocialAccount::where('user_id', $user->id)
        ->where('provider', 'google')
        ->first();
    expect($socialAccount)->not->toBeNull();
    expect($socialAccount->provider_user_id)->toBe('12345');
});

test('social login callback links existing user when email matches', function () {
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('67890');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('existinguser');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $socialiteUser->token = 'test-token';
    $socialiteUser->refreshToken = 'test-refresh-token';
    $socialiteUser->expiresIn = 3600;

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()
            ->shouldReceive('user')
            ->andReturn($socialiteUser)
            ->getMock());

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect();
    $this->assertAuthenticatedAs($existingUser);

    // Verify social account was linked to existing user
    $socialAccount = SocialAccount::where('user_id', $existingUser->id)
        ->where('provider', 'google')
        ->first();
    expect($socialAccount)->not->toBeNull();
});

test('social login callback authenticates existing social account user', function () {
    $existingUser = User::factory()->create();
    SocialAccount::create([
        'user_id' => $existingUser->id,
        'provider' => 'google',
        'provider_user_id' => '11111',
        'provider_email' => $existingUser->email,
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('11111');
    $socialiteUser->shouldReceive('getEmail')->andReturn($existingUser->email);
    $socialiteUser->shouldReceive('getName')->andReturn('Test User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('testuser');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $socialiteUser->token = 'updated-token';
    $socialiteUser->refreshToken = 'updated-refresh-token';
    $socialiteUser->expiresIn = 7200;

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()
            ->shouldReceive('user')
            ->andReturn($socialiteUser)
            ->getMock());

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect();
    $this->assertAuthenticatedAs($existingUser);

    // Verify tokens were updated
    $socialAccount = SocialAccount::where('user_id', $existingUser->id)
        ->where('provider', 'google')
        ->first();
    expect($socialAccount->access_token)->toBe('updated-token');
});

test('social auth service returns empty providers when social login is disabled', function () {
    Setting::where('option_name', 'auth_show_social_login')->update(['option_value' => '0']);
    config(['settings.auth_show_social_login' => '0']);

    $service = new SocialAuthService();
    $providers = $service->getEnabledProviders();

    expect($providers)->toBeEmpty();
});

test('social auth service returns only enabled and configured providers', function () {
    $service = new SocialAuthService();
    $providers = $service->getEnabledProviders();

    expect($providers)->toHaveKey('google');
    expect($providers)->not->toHaveKey('github');
    expect($providers)->not->toHaveKey('facebook');
});

test('user can have multiple social accounts', function () {
    $user = User::factory()->create();

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_user_id' => 'github-456',
    ]);

    expect($user->socialAccounts()->count())->toBe(2);
});

test('social account belongs to user', function () {
    $user = User::factory()->create();
    $socialAccount = SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'test-id',
    ]);

    expect($socialAccount->user->id)->toBe($user->id);
});

test('deleting user cascades to social accounts', function () {
    $user = User::factory()->create();
    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'test-id',
    ]);

    expect(SocialAccount::where('user_id', $user->id)->count())->toBe(1);

    $user->delete();

    expect(SocialAccount::where('user_id', $user->id)->count())->toBe(0);
});

test('authenticated user cannot access social login routes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/auth/google/redirect');

    $response->assertRedirect();
});
