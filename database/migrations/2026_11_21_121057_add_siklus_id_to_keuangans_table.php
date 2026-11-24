<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('keuangans')) {
            Schema::table('keuangans', function (Blueprint $table) {
                if (!Schema::hasColumn('keuangans', 'siklus_produksi_id')) {
                    $table->foreignId('siklus_produksi_id')
                        ->nullable()
                        ->after('user_id')
                        ->constrained('siklus_produksis')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('keuangans') && Schema::hasColumn('keuangans', 'siklus_produksi_id')) {
            Schema::table('keuangans', function (Blueprint $table) {
                $table->dropConstrainedForeignId('siklus_produksi_id');
            });
        }
    }
};