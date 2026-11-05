<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute le statut « annule » aux billets : un billet remboursé reste en base
 * (historique du remplissage) mais n'est plus compté comme vendu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->changerStatuts(['valide', 'utilisee', 'annule']);
    }

    public function down(): void
    {
        DB::table('billets')->where('statut', 'annule')->update(['statut' => 'valide']);

        $this->changerStatuts(['valide', 'utilisee']);
    }

    private function changerStatuts(array $statuts): void
    {
        if (DB::getDriverName() === 'mysql') {
            $liste = implode(',', array_map(fn ($statut) => "'{$statut}'", $statuts));
            DB::statement("ALTER TABLE billets MODIFY statut ENUM({$liste}) NOT NULL DEFAULT 'valide'");

            return;
        }

        Schema::table('billets', function (Blueprint $table) use ($statuts): void {
            $table->enum('statut', $statuts)->default('valide')->change();
        });
    }
};
