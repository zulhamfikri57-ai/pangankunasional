<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah_distribusis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_wilayah', 120); // Contoh: Kota Padang, Bukittinggi & Agam
            $table->string('provinsi', 100)->default('Sumatera Barat');
            $table->unsignedTinyInteger('tingkat_defisit')->default(50); // 1 - 100 (%) kriteria Benefit
            $table->decimal('biaya_logistik_kg', 12, 2)->default(1500); // Rp/kg kriteria Cost
            $table->decimal('faktor_harga', 5, 2)->default(1.00); // Pengali harga pasar lokal (mis. 1.05 = +5%)
            $table->decimal('jarak_km', 8, 1)->default(0); // Estimasi jarak tempuh (km)
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_distribusis');
    }
};