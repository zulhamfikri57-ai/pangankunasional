<?php

namespace App\Http\Controllers;

use App\Models\Komoditas;
use App\Models\ListingPanen;
use App\Models\WilayahDistribusi;
use Illuminate\Http\Request;

class SpkDistribusiController extends Controller
{
    public function index(Request $request)
    {
        $daftarKomoditas = Komoditas::orderBy('nama')->get();
        $komoditasId = $request->get('komoditas_id', $daftarKomoditas->first()?->id);
        $komoditasTerpilih = Komoditas::find($komoditasId);

        $hargaDasarPasar = $komoditasTerpilih->harga_pasar ?? 30000;
        $hargaProdusen = $komoditasTerpilih->harga_petani ?? 24000;

        // Ambil alternatif wilayah aktif dari database
        $wilayahDb = WilayahDistribusi::where('is_aktif', true)->get();

        // Hitung nilai mentah setiap kriteria SAW:
        // C1 (Benefit, 0.40): Selisih Harga Pasar Tujuan vs Harga Produsen (Rp/kg)
        // C2 (Benefit, 0.35): Tingkat Defisit / Permintaan Pasar (%)
        // C3 (Cost,    0.25): Estimasi Biaya Logistik (Rp/kg)
        $matriks = $wilayahDb->map(function ($w) use ($hargaDasarPasar, $hargaProdusen) {
            $hargaTujuan = $hargaDasarPasar * (float) $w->faktor_harga;
            $selisihHarga = max(100, $hargaTujuan - $hargaProdusen);

            return [
                'wilayah' => $w->nama_wilayah,
                'provinsi' => $w->provinsi,
                'jarak_km' => (float) $w->jarak_km,
                'harga_pasar_tujuan' => round($hargaTujuan),
                'c1_selisih' => $selisihHarga,
                'c2_defisit' => (int) $w->tingkat_defisit,
                'c3_logistik' => max(100, (float) $w->biaya_logistik_kg),
            ];
        });

        // Nilai Max (Benefit) & Min (Cost) untuk Normalisasi SAW
        $maxC1 = $matriks->max('c1_selisih') ?: 1;
        $maxC2 = $matriks->max('c2_defisit') ?: 1;
        $minC3 = $matriks->min('c3_logistik') ?: 1;

        // Bobot Kriteria (Total = 1.00)
        $w1 = 0.40;
        $w2 = 0.35;
        $w3 = 0.25;

        // Hitung Skor Akhir SAW & Rekomendasi Harga Jual Wajar Produsen
        $hasilSpk = $matriks->map(function ($row) use ($maxC1, $maxC2, $minC3, $w1, $w2, $w3, $hargaProdusen) {
            $r1 = $row['c1_selisih'] / $maxC1;
            $r2 = $row['c2_defisit'] / $maxC2;
            $r3 = $minC3 / $row['c3_logistik'];

            $skorAkhir = ($r1 * $w1) + ($r2 * $w2) + ($r3 * $w3);

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

        // Sinkron dengan status 'aktif' di ListingPanen
        $totalStokTersedia = ListingPanen::where('komoditas_id', $komoditasId)
            ->where('status', 'aktif')
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