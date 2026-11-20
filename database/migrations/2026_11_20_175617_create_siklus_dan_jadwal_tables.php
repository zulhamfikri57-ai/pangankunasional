<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siklus_produksis', function (Blueprint $table) {$table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();$table->foreignId('komoditas_id')->constrained('komoditas')->cascadeOnDelete();
            $table->string('nama_siklus');$table->string('sektor')->default('darat'); // darat, ternak, laut, olahan
            $table->date('tanggal_mulai');$table->date('estimasi_tanggal_panen');
            $table->date('tanggal_panen_aktual')->nullable();$table->decimal('luas_atau_kapasitas', 10, 2);
            $table->string('satuan_kapasitas')->default('m2'); // m2, hektar, ekor, kolam, batch$table->decimal('estimasi_hasil_kg', 12, 2);
            $table->decimal('hasil_aktual_kg', 12, 2)->nullable();$table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();$table->string('alamat_lahan')->nullable();
            $table->string('status')->default('berjalan'); // berjalan, selesai$table->timestamps();
        });

        Schema::create('jadwal_perawatans', function (Blueprint $table) {$table->id();
            $table->foreignId('siklus_produksi_id')->constrained('siklus_produksis')->cascadeOnDelete();$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul_kegiatan');$table->string('jenis_kegiatan')->default('perawatan'); // pemupukan, pakan_vaksin, perawatan, panen
            $table->date('tanggal_jadwal');$table->text('dosis_atau_catatan')->nullable();
            $table->string('status')->default('menunggu'); // menunggu, selesai$table->timestamps();
        });

        // Siapkan kolom relasi di keuangans & listing_panens untuk Modul 2 & 3
        if (Schema::hasTable('keuangans') && !Schema::hasColumn('keuangans', 'siklus_produksi_id')) {
            Schema::table('keuangans', function (Blueprint $table) {$table->foreignId('siklus_produksi_id')->nullable()->after('user_id')->constrained('siklus_produksis')->nullOnDelete();
            });
        }

        if (Schema::hasTable('listing_panens') && !Schema::hasColumn('listing_panens', 'siklus_produksi_id')) {
            Schema::table('listing_panens', function (Blueprint $table) {$table->foreignId('siklus_produksi_id')->nullable()->after('user_id')->constrained('siklus_produksis')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('listing_panens') && Schema::hasColumn('listing_panens', 'siklus_produksi_id')) {
            Schema::table('listing_panens', function (Blueprint $table) {$table->dropConstrainedForeignId('siklus_produksi_id');
            });
        }
        if (Schema::hasTable('keuangans') && Schema::hasColumn('keuangans', 'siklus_produksi_id')) {
            Schema::table('keuangans', function (Blueprint $table) {$table->dropConstrainedForeignId('siklus_produksi_id');
            });
        }
        Schema::dropIfExists('jadwal_perawatans');
        Schema::dropIfExists('siklus_produksis');
    }
};