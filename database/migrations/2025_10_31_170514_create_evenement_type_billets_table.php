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
    Schema::create('evenement_type_billets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
    $table->foreignId('type_billet_id')->constrained('type_billets')->onDelete('cascade');
    $table->integer('nombre_billet');
    $table->enum('devise', ['USD', 'CDF'])->default('CDF');
    $table->integer('prix_unitaire');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenement_type_billets');
    }
};
