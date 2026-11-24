<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'no_hp')) {
                    $table->string('no_hp', 20)->nullable()->unique()->after('email');
                }
                if (!Schema::hasColumn('users', 'kode_otp')) {
                    $table->string('kode_otp', 10)->nullable();
                }
                if (!Schema::hasColumn('users', 'otp_expires_at')) {
                    $table->timestamp('otp_expires_at')->nullable();
                }
                if (!Schema::hasColumn('users', 'is_kontak_verified')) {
                    $table->boolean('is_kontak_verified')->default(false);
                }
                // Kolom Verifikasi Dokumen Upgrade Peran Produsen
                if (!Schema::hasColumn('users', 'peran_diajukan')) {
                    $table->string('peran_diajukan', 30)->nullable();
                }
                if (!Schema::hasColumn('users', 'nik_identitas')) {
                    $table->string('nik_identitas', 20)->nullable();
                }
                if (!Schema::hasColumn('users', 'alamat_usaha_riil')) {
                    $table->text('alamat_usaha_riil')->nullable();
                }
                if (!Schema::hasColumn('users', 'dokumen_verifikasi_path')) {
                    $table->string('dokumen_verifikasi_path')->nullable();
                }
                if (!Schema::hasColumn('users', 'status_verifikasi_peran')) {
                    $table->string('status_verifikasi_peran', 20)->default('none'); // none, pending, approved, rejected
                }
                if (!Schema::hasColumn('users', 'catatan_admin_verifikasi')) {
                    $table->string('catatan_admin_verifikasi')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};