<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_view_login_form()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    #[Test]
    public function user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function user_cannot_login_with_email_that_does_not_exist()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function remember_me_functionality_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => 'on',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);

        // Check for the remember cookie
        $cookies = $response->headers->getCookies();
        $hasRememberCookie = false;

        foreach ($cookies as $cookie) {
            if (strpos($cookie->getName(), 'remember_web_') === 0) {
                $hasRememberCookie = true;
                break;
            }
        }

        $this->assertTrue($hasRememberCookie);
    }
}
