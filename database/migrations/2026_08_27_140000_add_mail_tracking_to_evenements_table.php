<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenements', function (Blueprint $table): void {
            if (!Schema::hasColumn('evenements', 'mail_send_attempts')) {
                $table->unsignedInteger('mail_send_attempts')->default(0)->after('statut');
            }

            if (!Schema::hasColumn('evenements', 'mail_sent_at')) {
                $table->timestamp('mail_sent_at')->nullable()->after('mail_send_attempts');
            }

            if (!Schema::hasColumn('evenements', 'last_mail_error')) {
                $table->text('last_mail_error')->nullable()->after('mail_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evenements', function (Blueprint $table): void {
            if (Schema::hasColumn('evenements', 'last_mail_error')) {
                $table->dropColumn('last_mail_error');
            }

            if (Schema::hasColumn('evenements', 'mail_sent_at')) {
                $table->dropColumn('mail_sent_at');
            }

            if (Schema::hasColumn('evenements', 'mail_send_attempts')) {
                $table->dropColumn('mail_send_attempts');
            }
        });
    }
};
