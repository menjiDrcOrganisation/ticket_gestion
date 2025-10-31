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
        Schema::create('retraits', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organisateur_id')->constrained('organisateurs')->onDelete('cascade');
    $table->string('nom_detenteur');
    $table->decimal('montant', 15, 2);
    $table->dateTime('date');
    $table->string('statut');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retraits');
    }
};
