<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['produksis', 'listing_panens', 'keuangans', 'pantauan_alerts'];
        foreach ($tables as$tbl) {
            Schema::table($tbl, function (Blueprint $table) {$table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        $tables = ['produksis', 'listing_panens', 'keuangans', 'pantauan_alerts'];
        foreach ($tables as$tbl) {
            Schema::table($tbl, function (Blueprint$table) {
                $table->dropForeign(['user_id']);$table->dropColumn('user_id');
            });
        }
    }
};