<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('siklus_produksis') && !Schema::hasColumn('siklus_produksis', 'created_at')) {
            Schema::table('siklus_produksis', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        if (Schema::hasTable('jadwal_perawatans') && !Schema::hasColumn('jadwal_perawatans', 'created_at')) {
            Schema::table('jadwal_perawatans', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        //
    }
};