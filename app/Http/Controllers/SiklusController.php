<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Komoditas;
use App\Models\SiklusProduksi;
use App\Models\JadwalPerawatan;
use App\Models\Produksi;

class SiklusController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $peran = $user->peran_sektor ?? 'petani';

        $sektorDefault = match ($peran) {
            'peternak' => 'ternak',
            'nelayan' => 'laut',
            'pelaku_pangan' => 'olahan',
            default => 'darat',
        };

        $komoditas = in_array($peran, ['petani', 'peternak', 'nelayan'])
            ? Komoditas::where('sektor', $sektorDefault)->get()
            : Komoditas::all();

        $siklusBerjalan = SiklusProduksi::with(['komoditas', 'jadwals'])
            ->where('user_id', $user->id)
            ->where('status', 'berjalan')
            ->latest()
            ->get();

        $siklusSelesai = SiklusProduksi::with(['komoditas'])
            ->where('user_id', $user->id)
            ->where('status', 'selesai')
            ->latest('tanggal_panen_aktual')
            ->get();

        $jadwalMendatang = JadwalPerawatan::with('siklus.komoditas')
            ->where('user_id', $user->id)
            ->where('status', 'menunggu')
            ->orderBy('tanggal_jadwal')
            ->take(10)
            ->get();

        return view('pages.siklus', compact(
            'user',
            'peran',
            'sektorDefault',
            'komoditas',
            'siklusBerjalan',
            'siklusSelesai',
            'jadwalMendatang'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'nama_siklus' => 'required|string|max:120',
            'tanggal_mulai' => 'required|date',
            'luas_atau_kapasitas' => 'required|numeric|min:0.01',
            'satuan_kapasitas' => 'required|string|max:30',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'alamat_lahan' => 'nullable|string|max:200',
        ]);

        $user = auth()->user();
        $komoditas = Komoditas::findOrFail($request->komoditas_id);
        $mulai = Carbon::parse($request->tanggal_mulai);
        $kapasitas = (float) $request->luas_atau_kapasitas;
        $satuan = $request->satuan_kapasitas;

        // Hitung prediksi hasil & durasi otomatis sesuai sektor dan komoditas
        $prediksi = $this->hitungPrediksiDanJadwal($komoditas, $mulai, $kapasitas, $satuan);

        $siklus = SiklusProduksi::create([
            'user_id' => $user->id,
            'komoditas_id' => $komoditas->id,
            'nama_siklus' => $request->nama_siklus,
            'sektor' => $komoditas->sektor ?? 'darat',
            'tanggal_mulai' => $mulai->toDateString(),
            'estimasi_tanggal_panen' => $prediksi['estimasi_panen']->toDateString(),
            'luas_atau_kapasitas' => $kapasitas,
            'satuan_kapasitas' => $satuan,
            'estimasi_hasil_kg' => $prediksi['estimasi_kg'],
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'alamat_lahan' => $request->alamat_lahan,
            'status' => 'berjalan',
        ]);

        // Simpan seluruh jadwal perawatan / pemupukan otomatis
        foreach ($prediksi['jadwals'] as $j) {
            JadwalPerawatan::create([
                'siklus_produksi_id' => $siklus->id,
                'user_id' => $user->id,
                'judul_kegiatan' => $j['judul'],
                'jenis_kegiatan' => $j['jenis'],
                'tanggal_jadwal' => $j['tanggal']->toDateString(),
                'dosis_atau_catatan' => $j['catatan'],
                'status' => 'menunggu',
            ]);
        }

        return redirect()->route('siklus.index')->with('success', 'Rencana siklus & jadwal perawatan otomatis berhasil dibuat!');
    }

    public function toggleJadwal($id)
    {
        $jadwal = JadwalPerawatan::where('user_id', auth()->id())->findOrFail($id);
        $jadwal->status = $jadwal->status === 'menunggu' ? 'selesai' : 'menunggu';
        $jadwal->save();

        return redirect()->route('siklus.index')->with('success', 'Status jadwal kegiatan berhasil diperbarui!');
    }

    public function selesaikan(Request $request, $id)
    {
        $request->validate([
            'hasil_aktual_kg' => 'required|numeric|min:0.1',
            'tanggal_panen_aktual' => 'required|date',
        ]);

        $siklus = SiklusProduksi::where('user_id', auth()->id())->findOrFail($id);
        $siklus->update([
            'hasil_aktual_kg' => $request->hasil_aktual_kg,
            'tanggal_panen_aktual' => $request->tanggal_panen_aktual,
            'status' => 'selesai',
        ]);

        // Tandai sisa jadwal panen selesai
        JadwalPerawatan::where('siklus_produksi_id', $siklus->id)->update(['status' => 'selesai']);

        // Otomatis catat ke riwayat produksi utama
        Produksi::create([
            'user_id' => auth()->id(),
            'komoditas_id' => $siklus->komoditas_id,
            'sektor' => $siklus->sektor,
            'jumlah' => $request->hasil_aktual_kg,
            'satuan' => 'kg',
            'tanggal_panen' => $request->tanggal_panen_aktual,
            'catatan' => "Realisasi Panen: {$siklus->nama_siklus}",
        ]);

        return redirect()->route('siklus.index')->with('success', 'Panen selesai dicatat! Hasil aktual otomatis tersimpan ke data Produksi.');
    }

    private function hitungPrediksiDanJadwal(Komoditas $komoditas, Carbon $mulai, float $kapasitas, string $satuan): array
    {
        $nama = strtolower($komoditas->nama);
        $sektor = $komoditas->sektor;

        // Konversi luas ke meter persegi untuk pertanian
        $luasM2 = match ($satuan) {
            'hektar' => $kapasitas * 10000,
            'rante' => $kapasitas * 400,
            default => $kapasitas,
        };

        if ($sektor === 'ternak') {
            $isAyam = str_contains($nama, 'ayam') || str_contains($nama, 'bebek');
            $hariPanen = $isAyam ? 35 : 90;
            $estimasiKg = $isAyam ? round($kapasitas * 1.85, 1) : round($kapasitas * 28, 1);
            $pakanKg = round($kapasitas * ($isAyam ? 3.2 : 45), 1);

            return [
                'estimasi_panen' => $mulai->copy()->addDays($hariPanen),
                'estimasi_kg' => $estimasiKg,
                'jadwals' => [
                    [
                        'judul' => 'Vaksinasi Awal & Vitamin Anti-Stres',
                        'jenis' => 'pakan_vaksin',
                        'tanggal' => $mulai->copy()->addDays(4),
                        'catatan' => "Berikan vitamin & vaksin tahap pertama untuk {$kapasitas} {$satuan}.",
                    ],
                    [
                        'judul' => 'Pergantian Pakan Grower & Cek Bobot',
                        'jenis' => 'pakan_vaksin',
                        'tanggal' => $mulai->copy()->addDays((int) round($hariPanen * 0.45)),
                        'catatan' => "Kebutuhan pakan fase pembesaran sekitar {$pakanKg} kg total siklus.",
                    ],
                    [
                        'judul' => 'Vaksin Booster & Sanitasi Kandang',
                        'jenis' => 'perawatan',
                        'tanggal' => $mulai->copy()->addDays((int) round($hariPanen * 0.7)),
                        'catatan' => 'Semprot disinfektan area kandang dan pastikan sirkulasi udara optimal.',
                    ],
                    [
                        'judul' => "Perkiraan Panen {$komoditas->nama}",
                        'jenis' => 'panen',
                        'tanggal' => $mulai->copy()->addDays($hariPanen),
                        'catatan' => "Target bobot panen estimasi {$estimasiKg} kg.",
                    ],
                ],
            ];
        }

        if ($sektor === 'laut') {
            $hariPanen = 75;
            $estimasiKg = $satuan === 'kolam' ? round($kapasitas * 450, 1) : round($luasM2 * 2.5, 1);

            return [
                'estimasi_panen' => $mulai->copy()->addDays($hariPanen),
                'estimasi_kg' => $estimasiKg,
                'jadwals' => [
                    [
                        'judul' => 'Cek pH Air, Oksigen Terlarut & Plankton',
                        'jenis' => 'perawatan',
                        'tanggal' => $mulai->copy()->addDays(10),
                        'catatan' => 'Pastikan pH air di kisaran 7.5–8.2 dan salinitas stabil.',
                    ],
                    [
                        'judul' => 'Sampling Pertumbuhan & Penyesuaian Pakan',
                        'jenis' => 'pakan_vaksin',
                        'tanggal' => $mulai->copy()->addDays(35),
                        'catatan' => 'Timbang sampel 10–20 ekor untuk menyesuaikan dosis pelet harian (3% biomassa).',
                    ],
                    [
                        'judul' => 'Probiotik Air & Pencegahan Penyakit',
                        'jenis' => 'perawatan',
                        'tanggal' => $mulai->copy()->addDays(55),
                        'catatan' => 'Aplikasikan probiotik pengurai amonia dasar kolam/tambak.',
                    ],
                    [
                        'judul' => "Perkiraan Panen {$komoditas->nama}",
                        'jenis' => 'panen',
                        'tanggal' => $mulai->copy()->addDays($hariPanen),
                        'catatan' => "Siapkan wadah rantai dingin & es curah untuk estimasi hasil {$estimasiKg} kg.",
                    ],
                ],
            ];
        }

        // Default: Pertanian (Darat)
        $hariPanen = match (true) {
            str_contains($nama, 'padi') || str_contains($nama, 'beras') => 105,
            str_contains($nama, 'jagung') => 95,
            str_contains($nama, 'cabai') => 75,
            str_contains($nama, 'bawang') => 65,
            str_contains($nama, 'sayur') || str_contains($nama, 'kangkung') || str_contains($nama, 'bayam') => 30,
            default => 70,
        };

        $yieldPerM2 = match (true) {
            str_contains($nama, 'padi') || str_contains($nama, 'beras') => 0.65,
            str_contains($nama, 'jagung') => 0.85,
            str_contains($nama, 'cabai') => 1.20,
            str_contains($nama, 'bawang') => 1.40,
            default => 1.00,
        };

        $estimasiKg = round($luasM2 * $yieldPerM2, 1);
        $pupukNpkKg = round(($luasM2 / 1000) * 25, 1); // ±25 kg NPK per 1.000 m2

        return [
            'estimasi_panen' => $mulai->copy()->addDays($hariPanen),
            'estimasi_kg' => $estimasiKg,
            'jadwals' => [
                [
                    'judul' => 'Pemupukan Susulan I (Fase Vegetatif)',
                    'jenis' => 'pemupukan',
                    'tanggal' => $mulai->copy()->addDays(14),
                    'catatan' => "Rekomendasi dosis: ±{$pupukNpkKg} kg NPK (16-16-16) untuk luas {$kapasitas} {$satuan}.",
                ],
                [
                    'judul' => 'Pemupukan Susulan II & Penyiangan Gulma',
                    'jenis' => 'pemupukan',
                    'tanggal' => $mulai->copy()->addDays((int) round($hariPanen * 0.42)),
                    'catatan' => "Tambahkan ±{$pupukNpkKg} kg pupuk susulan + bersihkan gulma di sekitar perakaran.",
                ],
                [
                    'judul' => 'Pengendalian Hama & Pupuk Buah/Umbi',
                    'jenis' => 'perawatan',
                    'tanggal' => $mulai->copy()->addDays((int) round($hariPanen * 0.68)),
                    'catatan' => 'Semprot fungisida/pestisida nabati sore hari & berikan pupuk Kalium (KCl) penguat hasil.',
                ],
                [
                    'judul' => "Perkiraan Panen {$komoditas->nama}",
                    'jenis' => 'panen',
                    'tanggal' => $mulai->copy()->addDays($hariPanen),
                    'catatan' => "Estimasi hasil panen: {$estimasiKg} kg. Siapkan karung/keranjang panen.",
                ],
            ],
        ];
    }
}