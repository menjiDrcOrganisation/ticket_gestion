<?php

namespace App\Mail;

use App\Models\NotificationRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignNotificationMail extends Mailable implements ShouldBeEncrypted
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $recipientId,
        public ?string $recipientName,
        public string $subjectLine,
        public string $messageBody,
    ) {}

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.notifications.campaign');
    }

    public function send($mailer)
    {
        $result = parent::send($mailer);

        NotificationRecipient::whereKey($this->recipientId)->update([
            'email_status' => 'sent',
            'email_sent_at' => now(),
            'email_error' => null,
        ]);

        return $result;
    }

    public function failed(\Throwable $exception): void
    {
        NotificationRecipient::whereKey($this->recipientId)->update([
            'email_status' => 'failed',
            'email_error' => mb_substr($exception->getMessage(), 0, 2000),
        ]);
    }
}
