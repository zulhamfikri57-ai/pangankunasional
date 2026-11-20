<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('siklus_produksis')) {
            Schema::table('siklus_produksis', function (Blueprint $table) {
                if (!Schema::hasColumn('siklus_produksis', 'komoditas_id')) {
                    $table->foreignId('komoditas_id')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('siklus_produksis', 'nama_siklus')) {
                    $table->string('nama_siklus')->nullable();
                }
                if (!Schema::hasColumn('siklus_produksis', 'sektor')) {
                    $table->string('sektor')->default('darat');
                }
                if (!Schema::hasColumn('siklus_produksis', 'tanggal_mulai')) {
                    $table->date('tanggal_mulai')->nullable();
                }
                if (!Schema::hasColumn('siklus_produksis', 'estimasi_tanggal_panen')) {
                    $table->date('estimasi_tanggal_panen')->nullable();
                }
                if (!Schema::hasColumn('siklus_produksis', 'tanggal_panen_aktual')) {
                    $table->date('tanggal_panen_aktual')->nullable();
                }
                if (!Schema::hasColumn('siklus_produksis', 'luas_atau_kapasitas')) {
                    $table->decimal('luas_atau_kapasitas', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('siklus_produksis', 'satuan_kapasitas')) {
                    $table->string('satuan_kapasitas')->default('m2');
                }
                if (!Schema::hasColumn('siklus_produksis', 'estimasi_hasil_kg')) {
                    $table->decimal('estimasi_hasil_kg', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('siklus_produksis', 'hasil_aktual_kg')) {
                    $table->decimal('hasil_aktual_kg', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('siklus_produksis', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable();
                }
                if (!Schema::hasColumn('siklus_produksis', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable();
                }
                if (!Schema::hasColumn('siklus_produksis', 'alamat_lahan')) {
                    $table->string('alamat_lahan')->nullable();
                }
                if (!Schema::hasColumn('siklus_produksis', 'status')) {
                    $table->string('status')->default('berjalan');
                }
            });
        }

        if (Schema::hasTable('jadwal_perawatans')) {
            Schema::table('jadwal_perawatans', function (Blueprint $table) {
                if (!Schema::hasColumn('jadwal_perawatans', 'siklus_produksi_id')) {
                    $table->foreignId('siklus_produksi_id')->nullable();
                }
                if (!Schema::hasColumn('jadwal_perawatans', 'user_id')) {
                    $table->foreignId('user_id')->nullable();
                }
                if (!Schema::hasColumn('jadwal_perawatans', 'judul_kegiatan')) {
                    $table->string('judul_kegiatan')->nullable();
                }
                if (!Schema::hasColumn('jadwal_perawatans', 'jenis_kegiatan')) {
                    $table->string('jenis_kegiatan')->default('perawatan');
                }
                if (!Schema::hasColumn('jadwal_perawatans', 'tanggal_jadwal')) {
                    $table->date('tanggal_jadwal')->nullable();
                }
                if (!Schema::hasColumn('jadwal_perawatans', 'dosis_atau_catatan')) {
                    $table->text('dosis_atau_catatan')->nullable();
                }
                if (!Schema::hasColumn('jadwal_perawatans', 'status')) {
                    $table->string('status')->default('menunggu');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};