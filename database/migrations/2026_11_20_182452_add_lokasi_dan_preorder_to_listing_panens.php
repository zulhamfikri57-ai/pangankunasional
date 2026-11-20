<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_panens', function (Blueprint $table) {
            if (!Schema::hasColumn('listing_panens', 'jenis_penjualan')) {
                $table->string('jenis_penjualan')->default('ready_stock')->after('harga_jual_per_kg'); // ready_stock atau preorder
            }
            if (!Schema::hasColumn('listing_panens', 'estimasi_siap')) {
                $table->date('estimasi_siap')->nullable()->after('jenis_penjualan');
            }
            if (!Schema::hasColumn('listing_panens', 'lokasi_jual')) {
                $table->string('lokasi_jual')->nullable()->after('estimasi_siap');
            }
            if (!Schema::hasColumn('listing_panens', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('lokasi_jual');
            }
            if (!Schema::hasColumn('listing_panens', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listing_panens', function (Blueprint $table) {
            $table->dropColumn(['jenis_penjualan', 'estimasi_siap', 'lokasi_jual', 'latitude', 'longitude']);
        });
    }
};