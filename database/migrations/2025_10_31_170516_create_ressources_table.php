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
      Schema::create('ressources', function (Blueprint $table) {
    $table->id();
    $table->string('nom_artiste');
    $table->string('phrase_accroche')->nullable();
    $table->string('photo_affiche')->nullable();
    $table->text('a_propos')->nullable();
    $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ressources');
    }
};
