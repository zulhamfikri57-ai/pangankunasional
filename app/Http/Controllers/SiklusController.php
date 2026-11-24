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

        $siklusList = SiklusProduksi::with(['komoditas', 'jadwals'])
            ->where('user_id', $user->id)
            ->latest('tanggal_mulai')
            ->latest('id')
            ->get();

        $komoditasList = Komoditas::orderBy('sektor')->orderBy('nama')->get();

        return view('pages.siklus', compact('user', 'siklusList', 'komoditasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'nama_siklus' => 'required|string|max:150',
            'tanggal_mulai' => 'required|date',
            'luas_atau_kapasitas' => 'required|numeric|min:0.1',
            'satuan_kapasitas' => 'required|string|max:30',
            'alamat_lahan' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $komoditas = Komoditas::findOrFail($validated['komoditas_id']);
        $tanggalMulai = Carbon::parse($validated['tanggal_mulai']);

        // Estimasi lama siklus (hari) & proyeksi panen berdasarkan sektor/komoditas
        $durasiHari = match ($komoditas->sektor) {
            'darat' => 75,
            'ternak' => 45,
            'laut' => 60,
            default => 60,
        };

        $faktorHasil = match ($validated['satuan_kapasitas']) {
            'hektar' => 8000,
            'm2' => 0.8,
            'ekor' => 1.8,
            'kolam' => 500,
            'kapal' => 250,
            default => 1,
        };

        $estimasiTanggalPanen = $tanggalMulai->copy()->addDays($durasiHari);
        $estimasiHasilKg = round($validated['luas_atau_kapasitas'] * $faktorHasil, 1);

        $siklus = SiklusProduksi::create([
            'user_id' => auth()->id(),
            'komoditas_id' => $komoditas->id,
            'nama_siklus' => $validated['nama_siklus'],
            'tanggal_mulai' => $tanggalMulai->toDateString(),
            'estimasi_tanggal_panen' => $estimasiTanggalPanen->toDateString(),
            'luas_atau_kapasitas' => $validated['luas_atau_kapasitas'],
            'satuan_kapasitas' => $validated['satuan_kapasitas'],
            'estimasi_hasil_kg' => $estimasiHasilKg,
            'alamat_lahan' => $validated['alamat_lahan'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => 'berjalan',
        ]);

        // Generate jadwal perawatan otomatis berdasarkan sektor komoditas
        $templateJadwal = $this->getTemplateJadwal($komoditas->sektor, $komoditas->nama, $durasiHari);

        foreach ($templateJadwal as $item) {
            JadwalPerawatan::create([
                'siklus_produksi_id' => $siklus->id,
                'user_id' => auth()->id(),
                'judul_kegiatan' => $item['judul'],
                'jenis_kegiatan' => $item['jenis'],
                'tanggal_jadwal' => $tanggalMulai->copy()->addDays($item['hari_ke'])->toDateString(),
                'dosis_atau_catatan' => $item['catatan'],
                'status' => 'menunggu',
            ]);
        }
        app(\App\Services\HargaSyncService::class)->sinkronkanSemua();
        return redirect()->route('siklus.index')->with(
            'success',
            "Siklus \"{$siklus->nama_siklus}\" berhasil dibuat beserta " . count($templateJadwal) . " jadwal perawatan otomatis!"
        );
    }

    public function update(Request $request, $id)
    {
        $siklus = SiklusProduksi::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'nama_siklus' => 'required|string|max:150',
            'tanggal_mulai' => 'required|date',
            'estimasi_tanggal_panen' => 'required|date',
            'luas_atau_kapasitas' => 'required|numeric|min:0.1',
            'satuan_kapasitas' => 'required|string|max:30',
            'estimasi_hasil_kg' => 'required|numeric|min:0.1',
            'alamat_lahan' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:berjalan,selesai,gagal',
        ]);

        $siklus->update($validated);

        return redirect()->route('siklus.index')->with('success', "Parameter siklus \"{$siklus->nama_siklus}\" berhasil diperbarui!");
    }

    public function selesaikan(Request $request, $id)
    {
        $siklus = SiklusProduksi::with('komoditas')->where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:selesai,gagal',
            'tanggal_panen_aktual' => 'required|date',
            'hasil_aktual_kg' => 'required|numeric|min:0',
        ]);

        $siklus->update([
            'status' => $validated['status'],
            'tanggal_panen_aktual' => $validated['tanggal_panen_aktual'],
            'hasil_aktual_kg' => $validated['hasil_aktual_kg'],
        ]);

        // Otomatis catat ke tabel Produksi jika siklus selesai & ada hasil panen
        if ($validated['status'] === 'selesai' && $validated['hasil_aktual_kg'] > 0) {
            Produksi::updateOrCreate(
                ['siklus_produksi_id' => $siklus->id],
                [
                    'user_id' => auth()->id(),
                    'komoditas_id' => $siklus->komoditas_id,
                    'sektor' => $siklus->komoditas->sektor ?? 'darat',
                    'jumlah' => $validated['hasil_aktual_kg'],
                    'satuan' => $siklus->komoditas->satuan ?? 'kg',
                    'tanggal_panen' => $validated['tanggal_panen_aktual'],
                    'catatan' => "Otomatis dari Siklus: {$siklus->nama_siklus}",
                ]
            );
        }

        $pesan = $validated['status'] === 'selesai'
            ? "Panen siklus \"{$siklus->nama_siklus}\" ({$validated['hasil_aktual_kg']} kg) otomatis tercatat di Catatan Produksi & siap dipasarkan!"
            : "Status siklus \"{$siklus->nama_siklus}\" diperbarui menjadi Gagal Panen untuk evaluasi modal.";

        app(\App\Services\HargaSyncService::class)->sinkronkanSemua();
        return redirect()->route('siklus.index')->with('success', $pesan);
    }

    public function destroy($id)
    {
        $siklus = SiklusProduksi::where('user_id', auth()->id())->findOrFail($id);
        $nama = $siklus->nama_siklus;

        JadwalPerawatan::where('siklus_produksi_id', $siklus->id)->delete();
        $siklus->delete();

        return redirect()->route('siklus.index')->with('success', "Siklus \"{$nama}\" beserta jadwal perawatannya telah dihapus.");
    }

    public function storeJadwal(Request $request, $siklusId)
    {
        $siklus = SiklusProduksi::where('user_id', auth()->id())->findOrFail($siklusId);

        $validated = $request->validate([
            'judul_kegiatan' => 'required|string|max:150',
            'jenis_kegiatan' => 'required|string|max:50',
            'tanggal_jadwal' => 'required|date',
            'dosis_atau_catatan' => 'nullable|string|max:500',
        ]);

        JadwalPerawatan::create([
            'siklus_produksi_id' => $siklus->id,
            'user_id' => auth()->id(),
            'judul_kegiatan' => $validated['judul_kegiatan'],
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'tanggal_jadwal' => $validated['tanggal_jadwal'],
            'dosis_atau_catatan' => $validated['dosis_atau_catatan'],
            'status' => 'menunggu',
        ]);

        return redirect()->route('siklus.index')->with('success', 'Jadwal perawatan tambahan berhasil ditambahkan!');
    }

    public function toggleJadwal($id)
    {
        $jadwal = JadwalPerawatan::where('user_id', auth()->id())->findOrFail($id);
        $jadwal->update([
            'status' => $jadwal->status === 'selesai' ? 'menunggu' : 'selesai',
        ]);

        return redirect()->route('siklus.index')->with('success', 'Status checklist perawatan berhasil diperbarui!');
    }

    public function destroyJadwal($id)
    {
        $jadwal = JadwalPerawatan::where('user_id', auth()->id())->findOrFail($id);
        $jadwal->delete();

        return redirect()->route('siklus.index')->with('success', 'Item jadwal perawatan berhasil dihapus.');
    }

    private function getTemplateJadwal(string $sektor, string $namaKomoditas, int $durasiHari): array
    {
        return match ($sektor) {
            'ternak' => [
                ['hari_ke' => 3, 'jenis' => 'perawatan', 'judul' => "Pemberian Vitamin & Elektrolit Awal ({$namaKomoditas})", 'catatan' => 'Larutkan dalam air minum pagi hari untuk adaptasi kandang.'],
                ['hari_ke' => 14, 'jenis' => 'pengendalian_hama', 'judul' => 'Vaksinasi Tahap 1 & Sanitasi Kandang', 'catatan' => 'Semprot disinfektan area sekitar kandang dan cek sirkulasi udara.'],
                ['hari_ke' => 28, 'jenis' => 'pemupukan', 'judul' => 'Evaluasi Bobot & Penyesuaian Pakan Grower', 'catatan' => 'Timbang sampel 10% populasi untuk menghitung FCR.'],
                ['hari_ke' => max(35, $durasiHari - 5), 'jenis' => 'panen', 'judul' => 'Persiapan Panen & Buka Pre-Order Pasar', 'catatan' => 'Siapkan keranjang angkut dan publikasikan stok di Marketplace.'],
            ],
            'laut' => [
                ['hari_ke' => 5, 'jenis' => 'perawatan', 'judul' => "Pengecekan Kualitas Air / Pakan Awal ({$namaKomoditas})", 'catatan' => 'Pastikan kadar oksigen terlarut dan pH stabil.'],
                ['hari_ke' => 20, 'jenis' => 'pemupukan', 'judul' => 'Pemberian Probiotik & Pakan Intensif', 'catatan' => 'Berikan pakan terukur 2-3 kali sehari agar tidak mencemari dasar kolam/keramba.'],
                ['hari_ke' => 40, 'jenis' => 'perawatan', 'judul' => 'Sortasi Ukuran & Cek Kesehatan Ikan', 'catatan' => 'Pisahkan ukuran besar/kecil untuk mencegah kanibalisme.'],
                ['hari_ke' => max(50, $durasiHari - 5), 'jenis' => 'panen', 'judul' => 'Persiapan Rantai Dingin (Es Curah 1:1) & Panen', 'catatan' => 'Siapkan box berinsulasi agar mutu tetap Grade A.'],
            ],
            default => [
                ['hari_ke' => 7, 'jenis' => 'pemupukan', 'judul' => "Pemupukan Susulan Fase Vegetatif ({$namaKomoditas})", 'catatan' => 'Gunakan pupuk NPK berimbang di sekitar perakaran pagi/sore hari.'],
                ['hari_ke' => 21, 'jenis' => 'pengendalian_hama', 'judul' => 'Monitoring Hama, Gulma & Penyemprotan Preventif', 'catatan' => 'Periksa bagian bawah daun dari kutu kebul, ulat, atau jamur.'],
                ['hari_ke' => 45, 'jenis' => 'pemupukan', 'judul' => 'Pemupukan Fase Generatif (Pembungaan/Pembuahan)', 'catatan' => 'Tingkatkan unsur Kalium (K) & Kalsium untuk mencegah kerontokan bunga/buah.'],
                ['hari_ke' => max(60, $durasiHari - 7), 'jenis' => 'panen', 'judul' => 'Persiapan Sortasi Grade A/B & Pre-Order Panen', 'catatan' => 'Siapkan wadah panen bersih dan buka listing Pre-Order di Marketplace.'],
            ],
        };
    }
}