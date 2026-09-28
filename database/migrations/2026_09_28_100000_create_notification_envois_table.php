<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suivi de chaque notification (e-mail) mise en file d'attente.
 *
 * `cle_active` porte la clé de déduplication tant que la notification est en attente ou en cours,
 * puis repasse à NULL : l'index UNIQUE empêche donc deux envois identiques simultanés,
 * tout en autorisant un nouvel envoi une fois le précédent terminé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_envois', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 100);
            $table->string('canal', 20)->default('mail');
            $table->string('destinataire');
            $table->string('cle', 191);
            $table->string('cle_active', 191)->nullable()->unique();
            $table->string('statut', 20)->default('en_attente');
            $table->nullableMorphs('sujet');
            $table->unsignedInteger('tentatives')->default(0);
            $table->text('derniere_erreur')->nullable();
            $table->uuid('job_uuid')->nullable();
            $table->timestamp('envoye_at')->nullable();
            $table->timestamp('echoue_at')->nullable();
            $table->timestamps();

            $table->index('cle');
            $table->index(['statut', 'created_at']);
            $table->index('job_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_envois');
    }
};
