<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komoditas_id')->constrained('komoditas')->onDelete('cascade');
            $table->enum('sektor', ['darat', 'ternak', 'laut']);
            $table->decimal('jumlah', 10, 2);
            $table->string('satuan')->default('kg');
            $table->date('tanggal_panen');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksis');
    }
};
