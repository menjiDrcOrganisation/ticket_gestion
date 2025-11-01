<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billet extends Model
{
    /** @use HasFactory<\Database\Factories\BilletFactory> */
    use HasFactory;
    protected     $fillable = [
       'date_achat',
       'nom_auteur',
       'numero',
       'email',
       'code_billet',
       'quantite_reelle',
       'quantite_fective'
    ];
}
