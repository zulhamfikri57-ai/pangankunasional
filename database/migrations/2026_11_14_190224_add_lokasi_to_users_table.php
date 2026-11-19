<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'peran_sektor')) {
                $table->string('peran_sektor')->default('konsumen')->after('email');
            }
            if (!Schema::hasColumn('users', 'komoditas_utama')) {
                $table->string('komoditas_utama')->nullable()->after('peran_sektor');
            }
            if (!Schema::hasColumn('users', 'lokasi')) {
                $table->string('lokasi')->nullable()->after('komoditas_utama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('lokasi');
        });
    }
};