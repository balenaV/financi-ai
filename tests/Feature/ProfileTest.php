<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_updating_profile_from_the_dashboard_settings_tab_returns_there_not_to_the_legacy_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/dashboard')
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response->assertRedirect('/dashboard');
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_avatar_can_be_uploaded_and_removed(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
        $firstPath = $user->avatar_path;

        // Enviar uma nova foto apaga a anterior do disco.
        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('outra.png', 10, 'image/png'),
        ])->assertSessionHasNoErrors();

        $user->refresh();
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($user->avatar_path);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'remove_avatar' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->avatar_path);
    }

    public function test_logging_out_other_sessions_requires_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.logout-other-sessions'), ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');
    }

    public function test_logging_out_other_sessions_removes_other_session_rows_but_not_other_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Os testes rodam com SESSION_DRIVER=array, então a sessão real desta
        // requisição não fica na tabela `sessions` (isso só acontece com
        // driver=database, o usado fora dos testes) — simulamos as linhas
        // diretamente para verificar a query de exclusão do controller.
        DB::table('sessions')->insert([
            ['id' => 'sessao-do-usuario', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time()],
            ['id' => 'sessao-de-outro-usuario', 'user_id' => $otherUser->id, 'payload' => 'x', 'last_activity' => time()],
        ]);

        $this->actingAs($user)
            ->patch(route('profile.logout-other-sessions'), ['password' => 'password']);

        $this->assertDatabaseMissing('sessions', ['id' => 'sessao-do-usuario']);
        $this->assertDatabaseHas('sessions', ['id' => 'sessao-de-outro-usuario']);
    }
}
