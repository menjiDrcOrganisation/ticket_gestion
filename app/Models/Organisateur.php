<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisateur extends Model
{
    /** @use HasFactory<\Database\Factories\OrganisateurFactory> */
    use HasFactory;
    protected     $fillable = [
       'telephone', 
       'user_id'
    ];

    function user()
    {
        return $this->belongsTo(User::class);
    }   
    function events()
    {
        return $this->hasMany(Evenement::class);
    }
}
