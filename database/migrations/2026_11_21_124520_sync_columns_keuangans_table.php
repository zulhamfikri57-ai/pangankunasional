<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('keuangans')) {
            Schema::table('keuangans', function (Blueprint $table) {
                if (!Schema::hasColumn('keuangans', 'siklus_produksi_id')) {
                    $table->foreignId('siklus_produksi_id')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('keuangans', 'jenis')) {
                    $table->string('jenis')->default('pengeluaran');
                }
                if (!Schema::hasColumn('keuangans', 'kategori')) {
                    $table->string('kategori')->nullable();
                }
                if (!Schema::hasColumn('keuangans', 'jumlah')) {
                    $table->decimal('jumlah', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('keuangans', 'keterangan')) {
                    $table->string('keterangan')->nullable();
                }
                if (!Schema::hasColumn('keuangans', 'tanggal')) {
                    $table->date('tanggal')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};