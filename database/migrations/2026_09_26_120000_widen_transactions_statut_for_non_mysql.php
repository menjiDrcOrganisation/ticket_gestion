<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La migration 2026_05_24_120000 n'élargit l'enum transactions.statut que sous MySQL.
 * Sur les autres moteurs (SQLite utilisé par les tests), la contrainte CHECK d'origine
 * refusait les statuts du flux de paiement (paiement_en_cours, paye, echoue, paye_sans_billet).
 */
return new class extends Migration
{
    private const STATUTS = ['en_attente', 'paiement_en_cours', 'paye', 'echoue', 'paye_sans_billet', 'completee', 'echouee', 'annulee'];

    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            return;
        }

        Schema::table('transactions', function (Blueprint $table): void {
            $table->enum('statut', self::STATUTS)->default('en_attente')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            return;
        }

        Schema::table('transactions', function (Blueprint $table): void {
            $table->enum('statut', ['en_attente', 'completee', 'echouee', 'annulee'])->default('en_attente')->change();
        });
    }
};
