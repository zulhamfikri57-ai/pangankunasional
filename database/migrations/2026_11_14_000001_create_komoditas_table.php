<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komoditas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('sektor', ['darat', 'ternak', 'laut']);
            $table->string('kategori_harga')->default('Pangan Pokok');
            $table->string('satuan')->default('kg');
            $table->integer('harga_pasar')->default(0);
            $table->integer('harga_petani')->default(0);
            $table->decimal('persentase_perubahan', 5, 2)->default(0);
            $table->boolean('is_favorit')->default(false);
            $table->string('ikon')->default('🌾');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komoditas');
    }
};