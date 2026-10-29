<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    protected $fillable = [
        'notification_campaign_id',
        'name',
        'email',
        'phone',
        'email_status',
        'sms_status',
        'email_error',
        'sms_error',
        'email_sent_at',
        'sms_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'email_sent_at' => 'datetime',
            'sms_sent_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(NotificationCampaign::class, 'notification_campaign_id');
    }
}
