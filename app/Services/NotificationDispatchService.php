<?php

namespace App\Services;

use App\Models\NotificationCampaign;
use App\Models\NotificationRecipient;
use App\Models\Evenement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class NotificationDispatchService
{
    public function notifyEventBuyers(Evenement $event, string $name, string $message, string $trigger, string $sourceKey): void
    {
        $recipients = $event->billets()->get(['nom_auteur', 'email', 'numero'])
            ->map(fn ($ticket): array => [
                'name' => $ticket->nom_auteur,
                'email' => $ticket->email,
                'phone' => $ticket->numero,
            ])->all();

        $this->createAutomaticCampaign($event, $name, $message, $trigger, $sourceKey, $recipients);
    }

    public function notifyOrganizer(Evenement $event, string $name, string $message, string $trigger, string $sourceKey): void
    {
        $event->loadMissing('organisateur.user');
        $organizer = $event->organisateur;
        if (!$organizer) {
            return;
        }

        $this->createAutomaticCampaign($event, $name, $message, $trigger, $sourceKey, [[
            'name' => $organizer->user?->name,
            'email' => $organizer->user?->email,
            'phone' => $organizer->telephone,
        ]]);
    }

    private function createAutomaticCampaign(Evenement $event, string $name, string $message, string $trigger, string $sourceKey, array $recipients): void
    {
        $recipients = collect($recipients)
            ->filter(fn (array $recipient): bool => !empty($recipient['email']) || !empty($recipient['phone']))
            ->unique(fn (array $recipient): string => strtolower((string) ($recipient['email'] ?? '')) . '|' . (string) ($recipient['phone'] ?? ''))
            ->values()
            ->all();

        if ($recipients === [] || NotificationCampaign::where('source_key', $sourceKey)->exists()) {
            return;
        }

        $this->createCampaign([
            'name' => $name,
            'subject' => $name,
            'message' => $message,
            'channels' => ['email', 'sms'],
            'trigger' => $trigger,
            'created_by' => null,
            'event_id' => $event->id,
            'source_key' => $sourceKey,
        ], $recipients);
    }

    public function createCampaign(array $attributes, array $recipients): NotificationCampaign
    {
        $campaign = NotificationCampaign::create($attributes);

        foreach ($recipients as $recipient) {
            $channels = $attributes['channels'];
            $campaign->recipients()->create([
                'name' => $recipient['name'] ?? null,
                'email' => $recipient['email'] ?? null,
                'phone' => $recipient['phone'] ?? null,
                'email_status' => in_array('email', $channels, true) ? 'pending' : 'skipped',
                'sms_status' => in_array('sms', $channels, true) ? 'pending' : 'skipped',
            ]);
        }

        if (!$campaign->scheduled_at) {
            $this->send($campaign);
        }

        return $campaign->fresh(['recipients']);
    }

    public function send(NotificationCampaign $campaign): void
    {
        if ($campaign->cancelled_at) {
            return;
        }

        foreach ($campaign->recipients as $recipient) {
            foreach ($campaign->channels as $channel) {
                $this->deliver($campaign, $recipient, $channel);
            }
        }

        $campaign->update(['sent_at' => now()]);
    }

    private function deliver(NotificationCampaign $campaign, NotificationRecipient $recipient, string $channel): void
    {
        $statusColumn = $channel . '_status';
        $sentColumn = $channel . '_sent_at';
        $errorColumn = $channel . '_error';
        $address = $channel === 'email' ? $recipient->email : $recipient->phone;

        if (!$address) {
            $recipient->update([$statusColumn => 'skipped', $errorColumn => null]);

            return;
        }

        try {
            if ($channel === 'email') {
                Mail::raw($campaign->message, function ($mail) use ($campaign, $recipient): void {
                    $mail->to($recipient->email)
                        ->subject($campaign->subject ?: $campaign->name);
                });
            } elseif ($channel === 'sms') {
                $this->sendSms($address, $campaign->message);
            } else {
                throw new \InvalidArgumentException('Canal de notification invalide.');
            }

            $recipient->update([
                $statusColumn => 'sent',
                $sentColumn => now(),
                $errorColumn => null,
            ]);
        } catch (\Throwable $exception) {
            $recipient->update([
                $statusColumn => 'failed',
                $errorColumn => mb_substr($exception->getMessage(), 0, 2000),
            ]);

            Log::error('Échec d’envoi de notification', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'channel' => $channel,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendSms(string $phone, string $message): void
    {
        $accountSid = (string) config('services.twilio.account_sid');
        $authToken = (string) config('services.twilio.auth_token');
        $from = (string) config('services.twilio.from');

        if ($accountSid === '' || $authToken === '' || $from === '') {
            throw new \RuntimeException('Twilio n’est pas configuré. Renseignez TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN et TWILIO_FROM.');
        }

        $response = Http::asForm()
            ->withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'To' => $phone,
                'From' => $from,
                'Body' => $message,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Twilio a refusé le SMS : ' . $response->body());
        }
    }
}
