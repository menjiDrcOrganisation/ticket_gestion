<?php

namespace Tests\Feature;

use App\Models\NotificationCampaign;
use App\Models\NotificationGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificationDispatchService;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_send_a_notification_to_a_saved_group(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.notifications.store'), [
            'name' => 'Information importante',
            'subject' => 'Information',
            'message' => 'Un message de test.',
            'channels' => ['email'],
            'audience' => 'new_group',
            'group_name' => 'Participants',
            'members' => 'Amina ; amina@example.com ; +243812345678',
        ]);

        $response->assertRedirect(route('admin.notifications.index'));
        $this->assertDatabaseHas('notification_groups', ['name' => 'Participants']);
        $this->assertDatabaseHas('notification_campaigns', [
            'name' => 'Information importante',
            'trigger' => 'manual',
        ]);
        $this->assertDatabaseHas('notification_recipients', [
            'email' => 'amina@example.com',
            'email_status' => 'sent',
            'sms_status' => 'skipped',
        ]);
        $this->assertCount(1, NotificationGroup::firstOrFail()->campaigns);
        $this->assertCount(1, NotificationCampaign::firstOrFail()->recipients);
    }

    public function test_only_admins_can_open_the_notification_history(): void
    {
        $organizer = User::factory()->create(['role' => 'organisateur']);

        $this->actingAs($organizer)
            ->get(route('admin.notifications.index'))
            ->assertForbidden();
    }

    public function test_scheduled_notifications_are_dispatched_by_the_scheduler_command(): void
    {
        Mail::fake();
        $campaign = app(NotificationDispatchService::class)->createCampaign([
            'name' => 'Rappel planifié',
            'subject' => 'Rappel',
            'message' => 'Un rappel.',
            'channels' => ['email'],
            'trigger' => 'manual',
            'scheduled_at' => now()->subMinute(),
        ], [[
            'name' => 'Amina',
            'email' => 'amina@example.com',
        ]]);

        $this->assertNull($campaign->sent_at);
        Artisan::call('notifications:dispatch-scheduled');

        $this->assertDatabaseHas('notification_recipients', [
            'notification_campaign_id' => $campaign->id,
            'email_status' => 'sent',
        ]);
        $this->assertNotNull($campaign->fresh()->sent_at);
    }

    public function test_sms_is_sent_through_the_configured_twilio_endpoint(): void
    {
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201)]);
        config([
            'services.twilio.account_sid' => 'AC123',
            'services.twilio.auth_token' => 'test-token',
            'services.twilio.from' => '+15005550006',
        ]);

        $campaign = app(NotificationDispatchService::class)->createCampaign([
            'name' => 'Test SMS',
            'message' => 'Bonjour.',
            'channels' => ['sms'],
            'trigger' => 'manual',
        ], [[
            'name' => 'Amina',
            'phone' => '+243812345678',
        ]]);

        $this->assertSame('sent', $campaign->recipients->first()->sms_status);
        Http::assertSent(fn (HttpRequest $request): bool => str_contains($request->url(), '/Accounts/AC123/Messages.json')
            && $request['To'] === '+243812345678'
            && $request['Body'] === 'Bonjour.');
    }
}
