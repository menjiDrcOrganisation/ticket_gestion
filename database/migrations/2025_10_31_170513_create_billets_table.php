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
    $table->integer('quantite_reelle');
    $table->integer('quantite_fictive');
    $table->dateTime('date_achat');
    $table->string('nom_auteur');
    $table->string('numero');
    $table->string('email')->nullable();
    $table->string('code_billet');
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
