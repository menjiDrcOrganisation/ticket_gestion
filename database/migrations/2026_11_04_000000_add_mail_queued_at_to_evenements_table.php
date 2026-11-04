<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('evenements', 'mail_queued_at')) {
            Schema::table('evenements', function (Blueprint $table): void {
                $table->timestamp('mail_queued_at')->nullable()->after('mail_sent_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('evenements', 'mail_queued_at')) {
            Schema::table('evenements', function (Blueprint $table): void {
                $table->dropColumn('mail_queued_at');
            });
        }
    }
};
