<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('evenements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organisateur_id')->constrained('organisateurs')->onDelete('cascade');
    $table->foreignId('scanneur_id')->constrained('scanneurs')->onDelete('cascade');
    $table->foreignId('demande_evenement_id')->constrained('demande_evenements')->onDelete('cascade');
    $table->string('nom');
    $table->dateTime('date_debut');
    $table->dateTime('date_fin');
    $table->string('adresse');
    $table->string('salle');
    $table->time('heure_debut');
    $table->time('heure_fin');
    $table->integer('nombre_billet');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
