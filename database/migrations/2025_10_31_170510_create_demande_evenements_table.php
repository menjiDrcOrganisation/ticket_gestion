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
        Schema::create('demande_evenements', function (Blueprint $table) {
    $table->id();
    //$table->foreignId('type_evenement_id')->constrained('type_evenements')->onDelete('cascade');
    $table->string('type_evenement');
    $table->string('contact_organisateur');
    $table->string('nom_evenement');
    $table->string('affiche')->nullable();
    $table->text('description');
    $table->string('statut');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_evenements');
    }
};
