<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY peran_sektor VARCHAR(50) NOT NULL DEFAULT 'konsumen'");
    }

    public function down(): void
    {
        //
    }
};