<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCampaign extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'message',
        'channels',
        'trigger',
        'scheduled_at',
        'sent_at',
        'cancelled_at',
        'created_by',
        'event_id',
        'notification_group_id',
        'source_key',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function group()
    {
        return $this->belongsTo(NotificationGroup::class, 'notification_group_id');
    }

    public function event()
    {
        return $this->belongsTo(Evenement::class, 'event_id');
    }
}
