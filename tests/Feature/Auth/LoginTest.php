<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('user can view login form', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertViewIs('backend.auth.login');
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'superadmin@example.com',
        'password' => bcrypt('12345678'),
    ]);

    $response = $this->post('/login', [
        'email' => 'superadmin@example.com',
        'password' => '12345678',
    ]);

    // Redirect path comes from config('settings.auth_redirect_after_login')
    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

test('user cannot login with incorrect password', function () {
    $response = $this->from('/login')
        ->post('/login', [
            'email' => 'superadmin@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('user cannot login with email that does not exist', function () {
    $response = $this->from('/login')->post('/login', [
        'email' => 'nobody@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('remember me functionality works', function () {
    $user = User::factory()->create([
        'email' => 'superadmin@example.com',
        'password' => bcrypt('12345678'),
    ]);

    $response = $this->post('/login', [
        'email' => 'superadmin@example.com',
        'password' => '12345678',
        'remember' => 'on',
    ]);

    // Redirect path comes from config('settings.auth_redirect_after_login')
    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);

    // Check for the remember cookie.
    $cookies = $response->headers->getCookies();
    $hasRememberCookie = false;

    foreach ($cookies as $cookie) {
        if (strpos($cookie->getName(), 'remember_web_') === 0) {
            $hasRememberCookie = true;
            break;
        }
    }

    expect($hasRememberCookie)->toBeTrue();
});
