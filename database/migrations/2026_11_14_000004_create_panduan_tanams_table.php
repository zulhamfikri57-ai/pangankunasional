<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panduan_tanams', function (Blueprint $table) {
            $table->id();
            $table->string('komoditas_fokus')->default('Cabai');
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('ikon')->default('🌱');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panduan_tanams');
    }
};
