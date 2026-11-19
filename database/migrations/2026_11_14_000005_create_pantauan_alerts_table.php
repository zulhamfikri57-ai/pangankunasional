<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pantauan_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komoditas_id')->constrained('komoditas')->onDelete('cascade');
            $table->enum('kondisi', ['naik_di_atas', 'turun_di_bawah']);
            $table->integer('target_harga');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pantauan_alerts');
    }
};
