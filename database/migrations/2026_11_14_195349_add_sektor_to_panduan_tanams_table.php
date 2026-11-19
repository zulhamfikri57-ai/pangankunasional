<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panduan_tanams', function (Blueprint $table) {
            if (!Schema::hasColumn('panduan_tanams', 'sektor')) {
                $table->string('sektor')->default('petani')->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('panduan_tanams', function (Blueprint $table) {
            $table->dropColumn('sektor');
        });
    }
};