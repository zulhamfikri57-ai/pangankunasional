<?php

namespace App\Http\Controllers;

use App\Models\komoditas;
use App\Models\ListingPanen;
use Illuminate\Http\Request;

class SpkDistribusiController extends Controller
{
    public function index(Request $request)
    {
        $daftarKomoditas = komoditas::orderBy('nama')->get();
        $komoditasId = $request->get('komoditas_id', $daftarKomoditas->first()?->id);
        $komoditasTerpilih = komoditas::find($komoditasId);

        // Data alternatif wilayah tujuan distribusi & proyeksi kebutuhan
        // Kriteria SAW:
        // C1 (Benefit, Bobot 0.40): Selisih Harga Pasar Tujuan vs Harga Produsen (Rp/kg)
        // C2 (Benefit, Bobot 0.35): Tingkat Defisit / Permintaan Pasar (%)
        // C3 (Cost,    Bobot 0.25): Estimasi Biaya & Jarak Logistik (Rp/kg)
        $alternatifWilayah = [
            [
                'wilayah' => 'Kota Padang (Pasar Raya & Induk)',
                'harga_pasar_tujuan' => ($komoditasTerpilih->harga_pasar ?? 30000) * 1.18,
                'tingkat_defisit' => 85,
                'biaya_logistik_kg' => 1500,
            ],
            [
                'wilayah' => 'Kota Bukittinggi & Agam',
                'harga_pasar_tujuan' => ($komoditasTerpilih->harga_pasar ?? 30000) * 1.12,
                'tingkat_defisit' => 70,
                'biaya_logistik_kg' => 2200,
            ],
            [
                'wilayah' => 'Kota Pekanbaru (Lintas Provinsi)',
                'harga_pasar_tujuan' => ($komoditasTerpilih->harga_pasar ?? 30000) * 1.28,
                'tingkat_defisit' => 92,
                'biaya_logistik_kg' => 3800,
            ],
            [
                'wilayah' => 'Kabupaten Pesisir Selatan (Pasar Lokal)',
                'harga_pasar_tujuan' => ($komoditasTerpilih->harga_pasar ?? 30000) * 1.02,
                'tingkat_defisit' => 45,
                'biaya_logistik_kg' => 600,
            ],
        ];

        $hargaProdusen = $komoditasTerpilih->harga_petani ?? 24000;

        // Hitung nilai mentah setiap kriteria
        $matriks = collect($alternatifWilayah)->map(function ($item) use ($hargaProdusen) {
            $selisihHarga = max(100, $item['harga_pasar_tujuan'] - $hargaProdusen);
            return [
                'wilayah' => $item['wilayah'],
                'harga_pasar_tujuan' => round($item['harga_pasar_tujuan']),
                'c1_selisih' => $selisihHarga,
                'c2_defisit' => $item['tingkat_defisit'],
                'c3_logistik' => $item['biaya_logistik_kg'],
            ];
        });

        // Nilai Max (Benefit) & Min (Cost) untuk Normalisasi SAW
        $maxC1 = $matriks->max('c1_selisih') ?: 1;
        $maxC2 = $matriks->max('c2_defisit') ?: 1;
        $minC3 = $matriks->min('c3_logistik') ?: 1;

        // Bobot Kriteria (Total = 1.00)
        $w1 = 0.40; // Margin Harga
        $w2 = 0.35; // Tingkat Defisit/Permintaan
        $w3 = 0.25; // Efisiensi Biaya Logistik (Cost)

        // Hitung Skor Akhir SAW & Rekomendasi Harga Jual Wajar Produsen
        $hasilSpk = $matriks->map(function ($row) use ($maxC1, $maxC2, $minC3, $w1, $w2, $w3, $hargaProdusen) {
            $r1 = $row['c1_selisih'] / $maxC1;
            $r2 = $row['c2_defisit'] / $maxC2;
            $r3 = $minC3 / $row['c3_logistik'];

            $skorAkhir = ($r1 * $w1) + ($r2 * $w2) + ($r3 * $w3);

            // Rekomendasi harga jual wajar produsen (harga dasar + 45% margin bersih setelah logistik)
            $marginBersih = max(0, $row['harga_pasar_tujuan'] - $hargaProdusen - $row['c3_logistik']);
            $rekomendasiHargaProdusen = round($hargaProdusen + ($marginBersih * 0.45), -2);

            return array_merge($row, [
                'r1' => round($r1, 3),
                'r2' => round($r2, 3),
                'r3' => round($r3, 3),
                'skor_saw' => round($skorAkhir, 4),
                'rekomendasi_harga' => $rekomendasiHargaProdusen,
                'estimasi_profit_bersih' => round($rekomendasiHargaProdusen - $hargaProdusen),
            ]);
        })->sortByDesc('skor_saw')->values();

        // Total stok tersedia dari listing panen aktif untuk komoditas terpilih
        $totalStokTersedia = ListingPanen::where('komoditas_id', $komoditasId)
            ->where('status', 'tersedia')
            ->sum('stok_kg');

        return view('pages.spk-distribusi', compact(
            'daftarKomoditas',
            'komoditasTerpilih',
            'hasilSpk',
            'totalStokTersedia',
            'hargaProdusen'
        ));
    }
}