<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('komoditas')) {
            Schema::table('komoditas', function (Blueprint $table) {
                if (!Schema::hasColumn('komoditas', 'harga_acuan_pemerintah')) {
                    $table->decimal('harga_acuan_pemerintah', 15, 2)->nullable();
                }
                if (!Schema::hasColumn('komoditas', 'harga_rata_marketplace')) {
                    $table->decimal('harga_rata_marketplace', 15, 2)->nullable();
                }
                if (!Schema::hasColumn('komoditas', 'sumber_harga')) {
                    $table->string('sumber_harga')->default('Acuan Pasar & Mitra');
                }
                if (!Schema::hasColumn('komoditas', 'terakhir_sync')) {
                    $table->timestamp('terakhir_sync')->nullable();
                }
                if (!Schema::hasColumn('komoditas', 'stok_ready_kg')) {
                    $table->decimal('stok_ready_kg', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('komoditas', 'stok_preorder_kg')) {
                    $table->decimal('stok_preorder_kg', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('komoditas', 'status_ketersediaan')) {
                    $table->string('status_ketersediaan')->default('menipis');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};