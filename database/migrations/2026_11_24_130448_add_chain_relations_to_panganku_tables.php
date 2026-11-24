<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hubungkan tabel produksis ke siklus_produksis (nullable agar tetap bisa input tanpa siklus)
        Schema::table('produksis', function (Blueprint $table) {
            if (!Schema::hasColumn('produksis', 'siklus_produksi_id')) {
                $table->unsignedBigInteger('siklus_produksi_id')->nullable()->unique()->after('user_id');
            }
        });

        // 2. Hubungkan tabel listing_panens ke siklus_produksis & produksis (nullable untuk mode tanpa siklus)
        Schema::table('listing_panens', function (Blueprint $table) {
            if (!Schema::hasColumn('listing_panens', 'siklus_produksi_id')) {
                $table->unsignedBigInteger('siklus_produksi_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('listing_panens', 'produksi_id')) {
                $table->unsignedBigInteger('produksi_id')->nullable()->after('siklus_produksi_id');
            }
        });

        // 3. Tambahkan pelacak sumber & pengunci anti-duplikat pada tabel keuangans
        Schema::table('keuangans', function (Blueprint $table) {
            if (!Schema::hasColumn('keuangans', 'sumber_transaksi')) {
                $table->string('sumber_transaksi', 30)->default('manual')->after('siklus_produksi_id'); // 'manual' | 'pasar_panen'
            }
            if (!Schema::hasColumn('keuangans', 'referensi_kode')) {
                $table->string('referensi_kode', 100)->nullable()->unique()->after('sumber_transaksi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->dropColumn(['siklus_produksi_id']);
        });
        Schema::table('listing_panens', function (Blueprint $table) {
            $table->dropColumn(['siklus_produksi_id', 'produksi_id']);
        });
        Schema::table('keuangans', function (Blueprint $table) {
            $table->dropColumn(['sumber_transaksi', 'referensi_kode']);
        });
    }
};