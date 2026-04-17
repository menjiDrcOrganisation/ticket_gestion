
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billets', function (Blueprint $table) {
            $table->string('billetImage')->nullable()->after('code_billet');
        });
    }

    public function down(): void
    {
        Schema::table('billets', function (Blueprint $table) {
            $table->dropColumn('billetImage');
        });
    }
};