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
        Schema::create('billets', function (Blueprint $table) {
    $table->id();
    $table->dateTime('date_achat');
    $table->string('nom_auteur');
    $table->string('numero');
    $table->string('email')->nullable();
    $table->string('code_billet');

    $table->integer('quantite');
    $table->integer('quantite_fictif');
    $table->enum('statut', ['valide', 'utilisee'])->default('valide');

    $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
    $table->foreignId('type_billet_id')->constrained('type_billets')->onDelete('cascade');
    $table->timestamps();

});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billets');
    }
};
