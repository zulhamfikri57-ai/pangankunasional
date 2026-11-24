<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('keuangans') && Schema::hasColumn('keuangans', 'nominal')) {
            // Ubah kolom nominal agar punya default 0 dan boleh null
            DB::statement('ALTER TABLE keuangans MODIFY nominal DECIMAL(15,2) NULL DEFAULT 0');

            // Salin nilai nominal lama ke kolom jumlah jika jumlah masih 0
            DB::statement('UPDATE keuangans SET jumlah = nominal WHERE (jumlah = 0 OR jumlah IS NULL) AND nominal > 0');
        }
    }

    public function down(): void
    {
        //
    }
};