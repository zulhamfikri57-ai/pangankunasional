<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('komoditas')) {
            Schema::table('komoditas', function (Blueprint $table) {
                if (!Schema::hasColumn('komoditas', 'harga_sekarang')) {
                    $table->decimal('harga_sekarang', 15, 2)->default(0);
                }
            });

            // Salin nilai dari kolom harga lama jika ada (harga / harga_pasar / harga_per_kg)
            foreach (['harga', 'harga_pasar', 'harga_per_kg'] as $kolomLama) {
                if (Schema::hasColumn('komoditas', $kolomLama)) {
                    DB::statement("UPDATE komoditas SET harga_sekarang = `{$kolomLama}` WHERE (harga_sekarang = 0 OR harga_sekarang IS NULL) AND `{$kolomLama}` > 0");
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};