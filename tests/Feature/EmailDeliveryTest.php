<?php

namespace Tests\Feature;

use App\Mail\CampaignNotificationMail;
use App\Services\EmailDeliveryService;
use App\Services\NotificationDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_delivery_uses_the_configured_queue_and_a_shared_safe_template(): void
    {
        Mail::fake();
        config(['queue.default' => 'database']);

        $campaign = app(NotificationDispatchService::class)->createCampaign([
            'name' => 'Information importante',
            'subject' => 'Information',
            'message' => '<script>alert("non")</script>',
            'channels' => ['email'],
            'trigger' => 'manual',
        ], [[
            'name' => 'Amina',
            'email' => 'amina@example.com',
        ]]);

        $this->assertSame('queued', $campaign->recipients->first()->email_status);
        Mail::assertQueued(CampaignNotificationMail::class, function (CampaignNotificationMail $mail): bool {
            return $mail->hasTo('amina@example.com')
                && str_contains($mail->render(), 'Bonjour Amina,')
                && ! str_contains($mail->render(), '<script>');
        });
    }

    public function test_email_delivery_is_synchronous_when_the_queue_driver_is_sync(): void
    {
        Mail::fake();
        config(['queue.default' => 'sync']);

        $queued = app(EmailDeliveryService::class)->send(
            new CampaignNotificationMail(1, 'Amina', 'Information', 'Bonjour.'),
            'amina@example.com',
        );

        $this->assertFalse($queued);
        Mail::assertSent(CampaignNotificationMail::class);
        Mail::assertNotQueued(CampaignNotificationMail::class);
    }
}
