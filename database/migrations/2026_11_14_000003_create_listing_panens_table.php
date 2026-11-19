<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_panens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komoditas_id')->constrained('komoditas')->onDelete('cascade');
            $table->decimal('stok_kg', 10, 2);
            $table->integer('harga_jual_per_kg');
            $table->enum('status', ['aktif', 'terjual'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_panens');
    }
};
