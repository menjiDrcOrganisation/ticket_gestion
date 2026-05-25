<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            if (!Schema::hasColumn('transactions', 'montant_unitaire')) {
                $table->decimal('montant_unitaire', 10, 2)->nullable()->after('montant');
            }

            if (!Schema::hasColumn('transactions', 'nom_complet_client')) {
                $table->string('nom_complet_client')->nullable()->after('numero_telephone');
            }

            if (!Schema::hasColumn('transactions', 'evenement_id')) {
                $table->foreignId('evenement_id')->nullable()->constrained('evenements')->nullOnDelete()->after('billet_id');
            }

            if (!Schema::hasColumn('transactions', 'type_billet_id')) {
                $table->foreignId('type_billet_id')->nullable()->constrained('type_billets')->nullOnDelete()->after('evenement_id');
            }

            if (!Schema::hasColumn('transactions', 'provider_reference')) {
                $table->string('provider_reference')->nullable()->after('description');
            }

            if (!Schema::hasColumn('transactions', 'gateway_reference')) {
                $table->string('gateway_reference')->nullable()->after('provider_reference');
            }

            if (!Schema::hasColumn('transactions', 'callback_payload')) {
                $table->json('callback_payload')->nullable()->after('gateway_reference');
            }

            if (!Schema::hasColumn('transactions', 'payment_started_at')) {
                $table->timestamp('payment_started_at')->nullable()->after('callback_payload');
            }

            if (!Schema::hasColumn('transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_started_at');
            }

            if (!Schema::hasColumn('transactions', 'failed_at')) {
                $table->timestamp('failed_at')->nullable()->after('paid_at');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY statut ENUM('en_attente','paiement_en_cours','paye','echoue','paye_sans_billet','completee','echouee','annulee') NOT NULL DEFAULT 'en_attente'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY statut ENUM('en_attente','completee','echouee','annulee') NOT NULL DEFAULT 'en_attente'");
        }

        Schema::table('transactions', function (Blueprint $table): void {
            $columns = [
                'montant_unitaire',
                'nom_complet_client',
                'provider_reference',
                'gateway_reference',
                'callback_payload',
                'payment_started_at',
                'paid_at',
                'failed_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('transactions', 'type_billet_id')) {
                $table->dropConstrainedForeignId('type_billet_id');
            }

            if (Schema::hasColumn('transactions', 'evenement_id')) {
                $table->dropConstrainedForeignId('evenement_id');
            }
        });
    }
};
