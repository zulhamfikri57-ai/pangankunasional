<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@panganku.id'],
            [
                'name' => 'Administrator Nasional',
                'peran_sektor' => 'admin',
                'komoditas_utama' => 'Nasional',
                'lokasi' => 'Pusat Nasional',
                'password' => Hash::make('admin123'),
            ]
        );
    }
}