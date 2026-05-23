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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->decimal('montant', 10, 2);
            $table->integer('nombre_billet')->nullable();
            $table->string('numero_telephone')->nullable();
            $table->enum('statut', ['en_attente', 'completee', 'echouee', 'annulee'])->default('en_attente');
            $table->enum('type', ['paiement', 'remboursement'])->default('paiement');
            $table->string('methode_paiement')->nullable();
            $table->string('devise')->default('CDF');
            $table->text('description')->nullable();
            $table->foreignId('billet_id')->nullable()->constrained('billets')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
