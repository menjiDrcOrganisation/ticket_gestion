<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationGroup extends Model
{
    protected $fillable = ['name', 'members'];

    protected function casts(): array
    {
        return ['members' => 'array'];
    }

    public function campaigns()
    {
        return $this->hasMany(NotificationCampaign::class);
    }
}
