<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['produksis', 'keuangans', 'listing_panens', 'pantauan_alerts'];
        $firstUserId = DB::table('users')->value('id');

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
                }
            });

            if ($firstUserId) {
                DB::table($tableName)->whereNull('user_id')->update(['user_id' => $firstUserId]);
            }
        }
    }

    public function down(): void
    {
        $tables = ['produksis', 'keuangans', 'listing_panens', 'pantauan_alerts'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
            });
        }
    }
};