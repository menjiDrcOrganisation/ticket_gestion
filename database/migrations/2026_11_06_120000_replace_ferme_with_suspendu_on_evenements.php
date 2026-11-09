<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changerStatuts(['encours', 'ferme', 'suspendu']);

        DB::table('evenements')
            ->where('statut', 'ferme')
            ->update(['statut' => 'suspendu']);

        $this->changerStatuts(['encours', 'suspendu']);
    }

    public function down(): void
    {
        $this->changerStatuts(['encours', 'suspendu', 'ferme']);

        DB::table('evenements')
            ->where('statut', 'suspendu')
            ->update(['statut' => 'ferme']);

        $this->changerStatuts(['encours', 'ferme']);
    }

    private function changerStatuts(array $statuts): void
    {
        if (DB::getDriverName() === 'mysql') {
            $liste = implode(',', array_map(fn (string $statut) => "'{$statut}'", $statuts));
            DB::statement("ALTER TABLE evenements MODIFY statut ENUM({$liste}) NOT NULL DEFAULT 'encours'");

            return;
        }

        Schema::table('evenements', function (Blueprint $table) use ($statuts): void {
            $table->enum('statut', $statuts)->default('encours')->change();
        });
    }
};
