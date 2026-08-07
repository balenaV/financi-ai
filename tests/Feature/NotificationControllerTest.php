<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_reading_a_notification_redirects_to_its_internal_url(): void
    {
        $user = User::factory()->create();
        $notification = $this->notificationFor($user, '/debts');

        $this->actingAs($user)->patch(route('notifications.read', $notification))
            ->assertRedirect('/debts');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_reading_a_notification_ignores_an_external_url(): void
    {
        // Nenhuma notificação hoje gera uma URL externa (todas vêm do backend),
        // mas o redirect não deve confiar cegamente no valor salvo em "data" —
        // isso é o que impede um open redirect caso isso mude no futuro.
        $user = User::factory()->create();
        $notification = $this->notificationFor($user, 'https://evil.example/phishing');

        $this->actingAs($user)->patch(route('notifications.read', $notification))
            ->assertRedirect(route('notifications.index'));
    }

    public function test_reading_a_notification_ignores_a_protocol_relative_url(): void
    {
        $user = User::factory()->create();
        $notification = $this->notificationFor($user, '//evil.example/phishing');

        $this->actingAs($user)->patch(route('notifications.read', $notification))
            ->assertRedirect(route('notifications.index'));
    }

    public function test_user_cannot_read_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $notification = $this->notificationFor($owner, '/debts');

        $this->actingAs($intruder)->patch(route('notifications.read', $notification))
            ->assertNotFound();
    }

    private function notificationFor(User $user, string $url): DatabaseNotification
    {
        return DatabaseNotification::forceCreate([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\FinancialReminder',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['url' => $url],
            'read_at' => null,
        ]);
    }
}
