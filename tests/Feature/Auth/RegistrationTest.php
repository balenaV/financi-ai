<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('user_settings', ['user_id' => auth()->id()]);
        Notification::assertSentTo(User::whereEmail('test@example.com')->firstOrFail(), VerifyEmail::class);
        $this->assertDatabaseHas('categories', ['user_id' => auth()->id(), 'name' => 'Salário']);
    }

    public function test_registration_requires_accepting_the_terms(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'no-terms@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrorsIn('registro', ['terms']);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'no-terms@example.com']);
    }

    public function test_registration_can_be_disabled(): void
    {
        config(['features.registration' => false]);

        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
    }
}
