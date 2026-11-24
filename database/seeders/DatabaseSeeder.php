<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Komoditas;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $komoditasList = [
            // SEKTOR PERTANIAN & BAHAN POKOK (DARAT)
            [
                'nama' => 'Beras Medium',
                'sektor' => 'darat',
                'kategori_harga' => 'Bahan Pokok',
                'satuan' => 'kg',
                'ikon' => '🌾',
                'harga_sekarang' => 14500,
                'harga_acuan_pemerintah' => 14500,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
            [
                'nama' => 'Cabai Merah Keriting',
                'sektor' => 'darat',
                'kategori_harga' => 'Hortikultura',
                'satuan' => 'kg',
                'ikon' => '🌶️',
                'harga_sekarang' => 38000,
                'harga_acuan_pemerintah' => 38000,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
            [
                'nama' => 'Bawang Merah',
                'sektor' => 'darat',
                'kategori_harga' => 'Hortikultura',
                'satuan' => 'kg',
                'ikon' => '🧅',
                'harga_sekarang' => 32000,
                'harga_acuan_pemerintah' => 32000,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
            [
                'nama' => 'Jagung Pipilan Kering',
                'sektor' => 'darat',
                'kategori_harga' => 'Bahan Pokok',
                'satuan' => 'kg',
                'ikon' => '🌽',
                'harga_sekarang' => 6500,
                'harga_acuan_pemerintah' => 6500,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
            [
                'nama' => 'Tomat Segar',
                'sektor' => 'darat',
                'kategori_harga' => 'Hortikultura',
                'satuan' => 'kg',
                'ikon' => '🍅',
                'harga_sekarang' => 11000,
                'harga_acuan_pemerintah' => 11000,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],

            // SEKTOR PETERNAKAN (TERNAK)
            [
                'nama' => 'Daging Ayam Ras',
                'sektor' => 'ternak',
                'kategori_harga' => 'Protein Hewani',
                'satuan' => 'kg',
                'ikon' => '🍗',
                'harga_sekarang' => 34000,
                'harga_acuan_pemerintah' => 34000,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
            [
                'nama' => 'Telur Ayam Ras',
                'sektor' => 'ternak',
                'kategori_harga' => 'Protein Hewani',
                'satuan' => 'kg',
                'ikon' => '🥚',
                'harga_sekarang' => 28500,
                'harga_acuan_pemerintah' => 28500,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
            [
                'nama' => 'Daging Sapi Murni',
                'sektor' => 'ternak',
                'kategori_harga' => 'Protein Hewani',
                'satuan' => 'kg',
                'ikon' => '🥩',
                'harga_sekarang' => 135000,
                'harga_acuan_pemerintah' => 135000,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],

            // SEKTOR PERIKANAN & KELAUTAN (LAUT)
            [
                'nama' => 'Ikan Tongkol Segar',
                'sektor' => 'laut',
                'kategori_harga' => 'Perikanan',
                'satuan' => 'kg',
                'ikon' => '🐟',
                'harga_sekarang' => 26000,
                'harga_acuan_pemerintah' => 26000,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
            [
                'nama' => 'Ikan Nila Budidaya',
                'sektor' => 'laut',
                'kategori_harga' => 'Perikanan',
                'satuan' => 'kg',
                'ikon' => '🐠',
                'harga_sekarang' => 30000,
                'harga_acuan_pemerintah' => 30000,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
            [
                'nama' => 'Udang Vaname Segar',
                'sektor' => 'laut',
                'kategori_harga' => 'Perikanan',
                'satuan' => 'kg',
                'ikon' => '🦐',
                'harga_sekarang' => 68000,
                'harga_acuan_pemerintah' => 68000,
                'persentase_perubahan' => 0,
                'sumber_harga' => 'Acuan Pasar & Mitra',
            ],
        ];

        foreach ($komoditasList as $item) {
            Komoditas::updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );
        }
        // Akun Administrator Utama (Bukan Akun Demo)
        \App\Models\User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'zulhamfikri57@gmail.com')],
            [
                'name' => 'Zulham Fikri Habib',
                'password' => \Illuminate\Support\Facades\Hash::make(env('ADMIN_PASSWORD', 'AdminPanganKu2026')),
                'peran_sektor' => 'admin',
                'is_admin' => true,
                'is_kontak_verified' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}