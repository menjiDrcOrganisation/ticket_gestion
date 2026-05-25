<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create([
            'reference' => 'TRX-' . strtoupper(Str::random(10)),
            'montant' => 25000,
            'nombre_billet' => 2,
            'numero_telephone' => '0991234567',
            'statut' => 'completee',
            'type' => 'paiement',
            'methode_paiement' => 'M-Pesa',
            'devise' => 'CDF',
            'description' => 'Paiement de billets',
            'billet_id' => 1,
            'expires_at' => Carbon::now()->addHours(2),
        ]);

        Transaction::create([
            'reference' => 'TRX-' . strtoupper(Str::random(10)),
            'montant' => 15000,
            'nombre_billet' => 1,
            'numero_telephone' => '0819876543',
            'statut' => 'en_attente',
            'type' => 'paiement',
            'methode_paiement' => 'Orange Money',
            'devise' => 'CDF',
            'description' => 'Réservation en attente',
            'billet_id' => 2,
            'expires_at' => Carbon::now()->addDay(),
        ]);

        Transaction::create([
            'reference' => 'TRX-' . strtoupper(Str::random(10)),
            'montant' => 10000,
            'nombre_billet' => 1,
            'numero_telephone' => '0821112233',
            'statut' => 'annulee',
            'type' => 'remboursement',
            'methode_paiement' => 'Airtel Money',
            'devise' => 'CDF',
            'description' => 'Transaction annulée',
            'billet_id' => null,
            'expires_at' => null,
        ]);
    }
}