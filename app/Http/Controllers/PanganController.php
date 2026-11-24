<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Komoditas;
use App\Models\Produksi;
use App\Models\ListingPanen;
use App\Models\Keuangan;
use App\Models\PantauanAlert;
use App\Models\PanduanTanam;

class PanganController extends Controller
{

    public function updateProduksi(Request $request, $id)
    {
        $produksi = Produksi::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'jumlah' => 'required|numeric|min:0.1',
            'satuan' => 'required|string|max:30',
            'tanggal_panen' => 'required|date',
            'catatan' => 'nullable|string|max:255',
        ]);

        $produksi->update([
            'komoditas_id' => $request->komoditas_id,
            'jumlah' => $request->jumlah,
            'satuan' => $request->satuan,
            'tanggal_panen' => $request->tanggal_panen,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('produksi', ['sektor' => $produksi->sektor])
            ->with('success', 'Catatan produksi berhasil diperbarui!');
    }

    public function destroyProduksi($id)
    {
        $produksi = Produksi::where('user_id', auth()->id())->findOrFail($id);
        $sektor = $produksi->sektor;
        $produksi->delete();

        return redirect()->route('produksi', ['sektor' => $sektor])
            ->with('success', 'Catatan produksi berhasil dihapus!');
    }

    public function updateAlert(Request $request, $id)
    {
        $alert = PantauanAlert::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'kondisi' => 'required|in:naik_di_atas,turun_di_bawah',
            'target_harga' => 'required|numeric|min:100',
        ]);

        $alert->update([
            'kondisi' => $request->kondisi,
            'target_harga' => $request->target_harga,
        ]);

        return redirect()->route('pantauan')->with('success', 'Aturan alert harga berhasil diperbarui!');
    }

    public function toggleAlert($id)
    {
        $alert = PantauanAlert::where('user_id', auth()->id())->findOrFail($id);
        $alert->update([
            'is_aktif' => !$alert->is_aktif,
        ]);

        $statusTeks = $alert->is_aktif ? 'diaktifkan kembali' : 'dijeda sementara';
        return redirect()->route('pantauan')->with('success', "Notifikasi alert harga berhasil {$statusTeks}.");
    }

    public function destroyAlert($id)
    {
        $alert = PantauanAlert::where('user_id', auth()->id())->findOrFail($id);
        $alert->delete();

        return redirect()->route('pantauan')->with('success', 'Aturan alert harga berhasil dihapus.');
    }

    private function getKonfigurasiSektor($user)
    {
        $peran = $user?->peran_sektor ?? 'konsumen';
        $komoditas = $user?->komoditas_utama ?: 'Pangan Umum';

        return match ($peran) {
            'petani' => [
                'ikon' => '🚜',
                'label_peran' => 'Petani (Tanaman)',
                'siklus' => "Musim Tanam 2026 — {$komoditas}",
                'aksi_utama' => '+ Catat Panen',
                'aksi_jual' => 'Jual Hasil Panen',
                'sektor_default' => 'darat',
                'kategori_biaya' => ['Bibit & Benih', 'Pupuk', 'Pestisida', 'Upah Tenaga', 'Irigasi & Listrik', 'Lain-lain'],
                'kategori_masuk' => ['Penjualan Panen', 'Pre-Order Pembeli', 'Subsidi/Bantuan', 'Lain-lain'],
                'info_ikon' => '🐞',
                'info_judul' => 'Peringatan Cuaca & Hama Tanaman',
                'info_desc' => 'Waspada kelembapan tinggi yang memicu jamur & lalat buah pada komoditas hortikultura.',
            ],
            'peternak' => [
                'ikon' => '🐾',
                'label_peran' => 'Peternak',
                'siklus' => "Siklus Ternak 2026 — {$komoditas}",
                'aksi_utama' => '+ Catat Produksi Ternak',
                'aksi_jual' => 'Jual Hasil Ternak',
                'sektor_default' => 'ternak',
                'kategori_biaya' => ['Bibit / Bakalan', 'Pakan Ternak', 'Obat & Vaksin', 'Upah Kandang', 'Listrik & Air', 'Lain-lain'],
                'kategori_masuk' => ['Penjualan Ternak/Daging', 'Penjualan Telur/Susu', 'Kemitraan', 'Lain-lain'],
                'info_ikon' => '🩺',
                'info_judul' => 'Biosekuriti & Manajemen Pakan',
                'info_desc' => 'Jaga sanitasi kandang dan jadwal vaksinasi saat peralihan cuaca untuk mencegah penurunan bobot.',
            ],
            'nelayan' => [
                'ikon' => '🐟',
                'label_peran' => 'Nelayan & Pembudidaya',
                'siklus' => "Operasional Melaut 2026 — {$komoditas}",
                'aksi_utama' => '+ Catat Tangkapan',
                'aksi_jual' => 'Jual Hasil Laut',
                'sektor_default' => 'laut',
                'kategori_biaya' => ['BBM & Solar Kapal', 'Es & Garam (Pendingin)', 'Umpan & Alat Tangkap', 'Bagi Hasil / ABK', 'Perawatan Perahu', 'Lain-lain'],
                'kategori_masuk' => ['Penjualan Tangkapan', 'Pasokan TPI / Pengepul', 'Hasil Tambak', 'Lain-lain'],
                'info_ikon' => '🌊',
                'info_judul' => 'Prakiraan Gelombang & Rantai Dingin',
                'info_desc' => 'Pastikan rasio es curah mencukupi (1:1) di palka agar mutu ikan tetap Grade A hingga pendaratan.',
            ],
            'pelaku_pangan' => [
                'ikon' => '🏪',
                'label_peran' => 'Pelaku Usaha Pangan',
                'siklus' => "Operasional Usaha 2026 — {$komoditas}",
                'aksi_utama' => '+ Catat Stok / Olahan',
                'aksi_jual' => 'Pasarkan Produk',
                'sektor_default' => 'darat',
                'kategori_biaya' => ['Bahan Baku Pangan', 'Kemasan & Label', 'Logistik & Pengiriman', 'Upah Produksi', 'Listrik / Gas / Air', 'Lain-lain'],
                'kategori_masuk' => ['Penjualan Produk', 'Pesanan Grosir / B2B', 'Distribusi Pasar', 'Lain-lain'],
                'info_ikon' => '📦',
                'info_judul' => 'Kontrol Mutu & Rotasi Stok (FIFO)',
                'info_desc' => 'Terapkan metode First-In First-Out untuk mencegah bahan baku kedaluwarsa atau susut bobot.',
            ],
            default => [
                'ikon' => '🛒',
                'label_peran' => $user ? 'Konsumen Pangan' : 'Pengunjung Umum',
                'siklus' => 'Pantauan Pangan Nasional 2026',
                'aksi_utama' => '📈 Cek Harga Hari Ini',
                'aksi_jual' => 'Pasar Pangan',
                'sektor_default' => 'darat',
                'kategori_biaya' => ['Beras & Pangan Pokok', 'Sayur & Buah', 'Ikan, Daging & Telur', 'Bumbu Dapur', 'Ongkos Kirim', 'Lain-lain'],
                'kategori_masuk' => ['Anggaran Belanja Bulanan', 'Cashback / Hemat', 'Lain-lain'],
                'info_ikon' => '📊',
                'info_judul' => 'Info Ketersediaan & Tren Harga Pangan',
                'info_desc' => 'Pasokan beras dan perikanan laut terpantau stabil minggu ini. Masuk untuk mengaktifkan Alert Harga.',
            ],
        };
    }

    public function beranda()
    {
        $user = auth()->user();
        $konfig = $this->getKonfigurasiSektor($user);

        $totalPemasukan = $user ? Keuangan::where('user_id', $user->id)->where('jenis', 'pemasukan')->sum('nominal') : 0;
        $totalPengeluaran = $user ? Keuangan::where('user_id', $user->id)->where('jenis', 'pengeluaran')->sum('nominal') : 0;
        $totalLaba = $totalPemasukan - $totalPengeluaran;

        $jam = now('Asia/Jakarta')->hour;
        $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

        $komoditasUnggulan = Komoditas::where('is_favorit', true)->take(4)->get();
        if ($komoditasUnggulan->isEmpty()) {
            $komoditasUnggulan = Komoditas::take(4)->get();
        }
        $totalListingPasar = ListingPanen::where('status', 'aktif')->count();

        return view('pages.beranda', compact(
            'user',
            'konfig',
            'totalLaba',
            'totalPengeluaran',
            'sapaan',
            'komoditasUnggulan',
            'totalListingPasar'
        ));
    }

    public function produksi(Request $request)
    {
        $userId = auth()->id();
        $konfig = $this->getKonfigurasiSektor(auth()->user());
        $sektor = $request->get('sektor', $konfig['sektor_default']);

        $komoditas = Komoditas::where('sektor', $sektor)->get();
        $riwayat = Produksi::with('komoditas')
            ->where('user_id', $userId)
            ->where('sektor', $sektor)
            ->latest()
            ->get();

        $totalBerat = $riwayat->sum('jumlah');
        $jumlahCatatan = $riwayat->count();

        return view('pages.produksi', compact('sektor', 'komoditas', 'riwayat', 'totalBerat', 'jumlahCatatan'));
    }

    public function storeProduksi(Request $request)
    {
        $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'sektor' => 'required|in:darat,ternak,laut',
            'jumlah' => 'required|numeric|min:0.1',
            'satuan' => 'required|string',
            'tanggal_panen' => 'required|date',
        ]);

        Produksi::create([
            'user_id' => auth()->id(),
            'komoditas_id' => $request->komoditas_id,
            'sektor' => $request->sektor,
            'jumlah' => $request->jumlah,
            'satuan' => $request->satuan,
            'tanggal_panen' => $request->tanggal_panen,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('produksi', ['sektor' => $request->sektor])->with('success', 'Catatan produksi Anda berhasil disimpan!');
    }

    public function jualHasil()
    {
        $user = auth()->user();
        $konfig = $this->getKonfigurasiSektor($user);

        $queryKomoditas = Komoditas::query();
        if ($user && in_array($user->peran_sektor, ['petani', 'peternak', 'nelayan'])) {
            $queryKomoditas->where('sektor', $konfig['sektor_default']);
        }
        $komoditas = $queryKomoditas->get();
        $semuaKomoditas = Komoditas::all();

        $listings = $user
            ? ListingPanen::with(['komoditas', 'siklus'])->where('user_id', $user->id)->latest()->get()
            : collect();

        // Ambil siklus berjalan (untuk Pre-Order) & siklus selesai panen (untuk Ready Stock)
        $siklusAktif = $user
            ? \App\Models\SiklusProduksi::with(['komoditas', 'produksi'])
                ->where('user_id', $user->id)
                ->whereIn('status', ['berjalan', 'selesai'])
                ->latest()
                ->get()
            : collect();

        $listingAktif = $listings->where('status', 'aktif');
        $totalPendapatan = $listingAktif->sum(fn($l) => $l->stok_kg * $l->harga_jual_per_kg);

        $pasarListings = ListingPanen::with(['komoditas', 'user', 'siklus'])
            ->where('status', 'aktif')
            ->latest()
            ->take(30)
            ->get();

        $titikPasar = [
            [
                'nama' => 'Pasar Raya Padang (Blok Sayur & Sembako)',
                'jenis' => 'Pasar Induk',
                'jam_operasional' => '05.00 - 18.00 WIB',
                'komoditas_utama' => 'Beras, Cabai, Bawang, Sayuran',
                'lat' => -0.9492,
                'lng' => 100.3543,
            ],
            [
                'nama' => 'TPI Muaro Padang (Tempat Pelelangan Ikan)',
                'jenis' => 'Pasar Ikan & Hasil Laut',
                'jam_operasional' => '06.00 - 17.00 WIB',
                'komoditas_utama' => 'Ikan Tongkol, Tuna, Udang, Cumi',
                'lat' => -0.9619,
                'lng' => 100.3578,
            ],
            [
                'nama' => 'Pasar Alai Padang',
                'jenis' => 'Pasar Tradisional',
                'jam_operasional' => '05.30 - 16.00 WIB',
                'komoditas_utama' => 'Ayam Potong, Telur, Sayur Segar',
                'lat' => -0.9235,
                'lng' => 100.3638,
            ],
            [
                'nama' => 'Pasar Lubuk Buaya',
                'jenis' => 'Pasar Regional',
                'jam_operasional' => '05.00 - 17.00 WIB',
                'komoditas_utama' => 'Beras Solok, Cabai Merah, Daging Sapi',
                'lat' => -0.8583,
                'lng' => 100.3489,
            ],
            [
                'nama' => 'Pasar Painan (Sentra Pesisir Selatan)',
                'jenis' => 'Pasar Kabupaten',
                'jam_operasional' => '05.30 - 17.00 WIB',
                'komoditas_utama' => 'Ikan Laut Segar, Pinang, Cabai, Beras',
                'lat' => -1.3514,
                'lng' => 100.5762,
            ],
            [
                'nama' => 'Pasar Balai Selasa (Ranah Pesisir)',
                'jenis' => 'Pasar Nagari / Sentra Tani',
                'jam_operasional' => '06.00 - 15.00 WIB',
                'komoditas_utama' => 'Padi, Jagung, Cabai, Hasil Kebun',
                'lat' => -1.6632,
                'lng' => 100.7081,
            ],
        ];

        return view('pages.jual', compact(
            'user',
            'konfig',
            'komoditas',
            'semuaKomoditas',
            'listings',
            'siklusAktif',
            'listingAktif',
            'totalPendapatan',
            'pasarListings',
            'titikPasar'
        ));
    }

    public function storeJualHasil(Request $request)
    {
        $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'stok_kg' => 'required|numeric|min:1',
            'harga_jual_per_kg' => 'required|numeric|min:100',
            'jenis_penjualan' => 'required|in:ready_stock,preorder',
            'estimasi_siap' => 'nullable|date',
            'lokasi_jual' => 'required|string|max:200',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'siklus_produksi_id' => 'nullable|exists:siklus_produksis,id',
        ]);

        $siklusId = $request->siklus_produksi_id ?: null;
        $produksiId = null;

        // Jika ditautkan ke siklus, otomatis hubungkan juga ke ID Catatan Produksi terkait (jika ada)
        if ($siklusId) {
            $produksiTerkait = Produksi::where('user_id', auth()->id())
                ->where('siklus_produksi_id', $siklusId)
                ->first();
            $produksiId = $produksiTerkait?->id;
        }

        ListingPanen::create([
            'user_id' => auth()->id(),
            'siklus_produksi_id' => $siklusId,
            'produksi_id' => $produksiId,
            'komoditas_id' => $request->komoditas_id,
            'stok_kg' => $request->stok_kg,
            'harga_jual_per_kg' => $request->harga_jual_per_kg,
            'jenis_penjualan' => $request->jenis_penjualan,
            'estimasi_siap' => $request->jenis_penjualan === 'preorder' ? $request->estimasi_siap : now()->toDateString(),
            'lokasi_jual' => $request->lokasi_jual,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'aktif',
        ]);

        app(\App\Services\HargaSyncService::class)->sinkronkanSemua();
        return redirect()->route('jual')->with('success', 'Produk & titik lokasi penjualan berhasil dipublikasikan ke Marketplace!');
    }

    public function updateJualHasil(Request $request, $id)
    {
        $listing = ListingPanen::with('komoditas')->where('user_id', auth()->id())->findOrFail($id);

        if ($request->input('aksi') === 'terjual' || $request->input('status') === 'terjual') {
            $listing->update([
                'stok_kg' => $request->filled('stok_kg') ? $request->stok_kg : $listing->stok_kg,
                'harga_jual_per_kg' => $request->filled('harga_jual_per_kg') ? $request->harga_jual_per_kg : $listing->harga_jual_per_kg,
                'status' => 'terjual',
            ]);

            $konfig = $this->getKonfigurasiSektor(auth()->user());
            $totalJual = $listing->stok_kg * $listing->harga_jual_per_kg;
            $kodeReferensi = "LISTING-{$listing->id}";

            // Anti-duplikat: gunakan updateOrCreate berdasarkan referensi_kode unik
            Keuangan::updateOrCreate(
                ['referensi_kode' => $kodeReferensi],
                [
                    'user_id' => auth()->id(),
                    'siklus_produksi_id' => $listing->siklus_produksi_id,
                    'sumber_transaksi' => 'pasar_panen',
                    'jenis' => 'pemasukan',
                    'kategori' => $konfig['kategori_masuk'][0] ?? 'Penjualan Produk',
                    'jumlah' => $totalJual,
                    'nominal' => $totalJual,
                    'keterangan' => 'Penjualan Pasar Panen: ' . ($listing->komoditas->nama ?? 'Komoditas') . " ({$listing->stok_kg} kg @ Rp " . number_format($listing->harga_jual_per_kg, 0, ',', '.') . ")",
                    'tanggal' => now()->toDateString(),
                ]
            );

            app(\App\Services\HargaSyncService::class)->sinkronkanSemua();
            return redirect()->route('jual')->with('success', 'Listing ditandai terjual & pemasukan tersinkron otomatis ke Keuangan & Laporan Siklus tanpa duplikasi!');
        }

        $request->validate([
            'stok_kg' => 'required|numeric|min:1',
            'harga_jual_per_kg' => 'required|numeric|min:100',
            'status' => 'required|in:aktif,terjual',
        ]);

        $listing->update([
            'stok_kg' => $request->stok_kg,
            'harga_jual_per_kg' => $request->harga_jual_per_kg,
            'status' => $request->status,
        ]);

        return redirect()->route('jual')->with('success', 'Data listing penjualan berhasil diperbarui!');
    }

    public function storeKeuangan(Request $request)
    {
        $request->validate([
            'siklus_produksi_id' => 'nullable|exists:siklus_produksis,id',
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'kategori' => 'required|string|max:100',
            'jumlah' => 'required|numeric|min:100',
            'keterangan' => 'nullable|string|max:255',
            'tanggal' => 'required|date',
        ]);

        Keuangan::create([
            'user_id' => auth()->id(),
            'siklus_produksi_id' => $request->siklus_produksi_id ?: null,
            'sumber_transaksi' => 'manual',
            'referensi_kode' => null,
            'jenis' => $request->jenis,
            'kategori' => $request->kategori,
            'jumlah' => $request->jumlah,
            'nominal' => $request->jumlah,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
        ]);

        return redirect()->route('keuangan')->with('success', 'Catatan transaksi keuangan manual berhasil disimpan!');
    }

    public function destroyJualHasil($id)
    {
        $listing = ListingPanen::where('user_id', auth()->id())->findOrFail($id);
        Keuangan::where('user_id', auth()->id())
            ->where('referensi_kode', "LISTING-{$listing->id}")
            ->delete();
        $listing->delete();

        app(\App\Services\HargaSyncService::class)->sinkronkanSemua();
        return redirect()->route('jual')->with('success', 'Listing penjualan & catatan pemasukan terkait berhasil dihapus!');
    }

    public function keuangan(Request $request)
    {
        $user = auth()->user();
        $konfig = $this->getKonfigurasiSektor($user);

        $filterSiklus = $request->get('siklus_id');
        $filterJenis = $request->get('jenis');

        $query = Keuangan::with('siklus')
            ->where('user_id', $user->id);

        if ($filterSiklus === 'umum') {
            $query->whereNull('siklus_produksi_id');
        } elseif (!empty($filterSiklus)) {
            $query->where('siklus_produksi_id', $filterSiklus);
        }

        if (!empty($filterJenis) && in_array($filterJenis, ['pemasukan', 'pengeluaran'])) {
            $query->where('jenis', $filterJenis);
        }

        $transaksis = $query->latest('tanggal')
            ->latest('id')
            ->get();

        $siklusList = \App\Models\SiklusProduksi::with('komoditas')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $totalMasuk = $transaksis->where('jenis', 'pemasukan')->sum('jumlah');
        $totalKeluar = $transaksis->where('jenis', 'pengeluaran')->sum('jumlah');
        $saldoBersih = $totalMasuk - $totalKeluar;

        return view('pages.keuangan', compact(
            'user',
            'konfig',
            'transaksis',
            'siklusList',
            'totalMasuk',
            'totalKeluar',
            'saldoBersih',
            'filterSiklus',
            'filterJenis'
        ));
    }

    public function updateKeuangan(Request $request, $id)
    {
        $transaksi = Keuangan::where('user_id', auth()->id())->findOrFail($id);

        // Lindungi transaksi otomatis dari Pasar Panen agar nominal tetap sinkron dengan stok
        if ($transaksi->sumber_transaksi === 'pasar_panen') {
            return redirect()->route('keuangan')
                ->with('error', 'Transaksi otomatis dari Pasar Panen tidak dapat diubah manual. Ubah melalui menu Pasar Panen.');
        }

        $request->validate([
            'siklus_produksi_id' => 'nullable|exists:siklus_produksis,id',
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'kategori' => 'required|string|max:100',
            'jumlah' => 'required|numeric|min:100',
            'keterangan' => 'nullable|string|max:255',
            'tanggal' => 'required|date',
        ]);

        $transaksi->update([
            'siklus_produksi_id' => $request->siklus_produksi_id ?: null,
            'jenis' => $request->jenis,
            'kategori' => $request->kategori,
            'jumlah' => $request->jumlah,
            'nominal' => $request->jumlah,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
        ]);

        return redirect()->route('keuangan')->with('success', 'Catatan transaksi keuangan berhasil diperbarui!');
    }

    public function destroyKeuangan($id)
    {
        $transaksi = Keuangan::where('user_id', auth()->id())->findOrFail($id);

        // Cegah penghapusan langsung transaksi otomatis Pasar Panen agar laporan omzet tidak timpang
        if ($transaksi->sumber_transaksi === 'pasar_panen') {
            return redirect()->route('keuangan')
                ->with('error', 'Transaksi otomatis dari Pasar Panen tidak bisa dihapus langsung di sini. Hapus atau ubah status listing-nya di menu Pasar Panen.');
        }

        $transaksi->delete();

        return redirect()->route('keuangan')->with('success', 'Transaksi keuangan manual berhasil dihapus!');
    }

    public function laporan()
    {
        $user = auth()->user();
        $konfig = $this->getKonfigurasiSektor($user);

        $siklusLaporan = \App\Models\SiklusProduksi::with(['komoditas', 'keuangans'])
            ->where('user_id', $user->id)
            ->orderBy('tanggal_mulai', 'asc')
            ->get()
            ->map(function ($siklus) {
                $modal = $siklus->keuangans->where('jenis', 'pengeluaran')->sum('jumlah');
                $omzet = $siklus->keuangans->where('jenis', 'pemasukan')->sum('jumlah');
                $labaBersih = $omzet - $modal;
                $roi = $modal > 0 ? round(($labaBersih / $modal) * 100, 1) : ($omzet > 0 ? 100 : 0);

                $hasilKg = $siklus->hasil_aktual_kg ?? $siklus->estimasi_hasil_kg;
                $hppPerKg = $hasilKg > 0 ? round($modal / $hasilKg, 0) : 0;

                $siklus->total_modal = $modal;
                $siklus->total_omzet = $omzet;
                $siklus->laba_bersih = $labaBersih;
                $siklus->roi_persen = $roi;
                $siklus->hpp_per_kg = $hppPerKg;

                return $siklus;
            });

        $totalModalSemua = $siklusLaporan->sum('total_modal');
        $totalOmzetSemua = $siklusLaporan->sum('total_omzet');
        $totalLabaSemua = $totalOmzetSemua - $totalModalSemua;

        // Data Grafik 1 & 3: Perbandingan Finansial & Tonase Antar Siklus
        $chartLabelsSiklus = $siklusLaporan->pluck('nama_siklus')->values();
        $chartModalSiklus = $siklusLaporan->pluck('total_modal')->map(fn($v) => (float) $v)->values();
        $chartOmzetSiklus = $siklusLaporan->pluck('total_omzet')->map(fn($v) => (float) $v)->values();
        $chartLabaSiklus = $siklusLaporan->pluck('laba_bersih')->map(fn($v) => (float) $v)->values();
        $chartEstimasiKg = $siklusLaporan->pluck('estimasi_hasil_kg')->map(fn($v) => (float) $v)->values();
        $chartAktualKg = $siklusLaporan->map(fn($s) => (float) ($s->hasil_aktual_kg ?? 0))->values();

        // Data Grafik 2: Komposisi Pengeluaran Modal per Kategori
        $pengeluaranPerKategori = Keuangan::where('user_id', $user->id)
            ->where('jenis', 'pengeluaran')
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        $chartKategoriLabels = $pengeluaranPerKategori->pluck('kategori')->values();
        $chartKategoriTotals = $pengeluaranPerKategori->pluck('total')->map(fn($v) => (float) $v)->values();

        return view('pages.laporan', compact(
            'user',
            'konfig',
            'siklusLaporan',
            'totalModalSemua',
            'totalOmzetSemua',
            'totalLabaSemua',
            'chartLabelsSiklus',
            'chartModalSiklus',
            'chartOmzetSiklus',
            'chartLabaSiklus',
            'chartEstimasiKg',
            'chartAktualKg',
            'chartKategoriLabels',
            'chartKategoriTotals'
        ));
    }

    public function harga(Request $request)
    {
        $kategori = $request->get('kategori', 'Semua');
        $query = Komoditas::query();
        if ($kategori !== 'Semua') {
            $query->where('kategori_harga', $kategori);
        }
        $komoditas = $query->get();
        app(\App\Services\HargaSyncService::class)->sinkronkanSemua();
        return view('pages.harga', compact('komoditas', 'kategori'));
    }

    public function pantauan()
    {
        $userId = auth()->id();
        $favorit = Komoditas::where('is_favorit', true)->get();
        $semuaKomoditas = Komoditas::all();
        $alerts = PantauanAlert::with('komoditas')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return view('pages.pantauan', compact('favorit', 'semuaKomoditas', 'alerts'));
    }

    public function storeAlert(Request $request)
    {
        $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'kondisi' => 'required|in:naik_di_atas,turun_di_bawah',
            'target_harga' => 'required|numeric|min:100',
        ]);

        PantauanAlert::create([
            'user_id' => auth()->id(),
            'komoditas_id' => $request->komoditas_id,
            'kondisi' => $request->kondisi,
            'target_harga' => $request->target_harga,
            'is_aktif' => true,
        ]);

        return redirect()->route('pantauan')->with('success', 'Alert harga pribadi Anda berhasil ditambahkan!');
    }

    public function profil()
    {
        $user = auth()->user();
        return view('pages.profil', compact('user'));
    }

    public function upgradePeran(Request $request)
    {
        $validated = $request->validate([
            'peran_sektor' => 'required|in:konsumen,petani,peternak,nelayan,pelaku_pangan',
            'komoditas_utama' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:150',
        ]);

        $user = auth()->user();
        $user->peran_sektor = $validated['peran_sektor'];
        $user->komoditas_utama = $validated['peran_sektor'] === 'konsumen'
            ? null
            : ucfirst($validated['komoditas_utama'] ?? '');
        $user->lokasi = $validated['lokasi'] ?? null;
        $user->save();

        return redirect()->route('profil')->with('success', 'Status akun berhasil diperbarui menjadi ' . strtoupper(str_replace('_', ' ', $user->peran_sektor)) . '!');
    }

    public function panduan()
    {
        $user = auth()->user();
        $peran = $user?->peran_sektor ?? 'konsumen';
        $konfig = $this->getKonfigurasiSektor($user);

        $panduans = PanduanTanam::where('sektor', $peran)->get();
        if ($panduans->isEmpty()) {
            $panduans = PanduanTanam::where('sektor', 'petani')->get();
        }

        $konteksPanduan = match ($peran) {
            'petani' => [
                'judul_panduan' => 'Panduan Teknis Budidaya & Pascapanen Pertanian',
                'sub_pakar' => 'Konsultasi hama tanaman, pemupukan, cuaca, atau strategi jual saat panen raya melimpah',
                'placeholder' => 'Contoh: Panen cabai saya minggu ini 150 kg tapi harga turun, bagaimana agar tidak busuk dan tetap untung?',
                'sektor_komoditas' => 'darat',
                'prompts' => [
                    '📉 Panen melimpah & harga turun, bagaimana solusinya?',
                    '🐞 Cara mengatasi lalat buah & jamur di musim hujan?',
                    '🌱 Takaran pupuk agar tanaman berbuah lebat?',
                ],
            ],
            'peternak' => [
                'judul_panduan' => 'Panduan Teknis Pemeliharaan & Pascapanen Peternakan',
                'sub_pakar' => 'Konsultasi efisiensi pakan, kesehatan ternak, atau penyerapan stok telur/daging berlebih',
                'placeholder' => 'Contoh: Harga ayam potong sedang turun dan stok siap panen menumpuk di kandang, apa langkah terbaik?',
                'sektor_komoditas' => 'ternak',
                'prompts' => [
                    '🐔 Stok ternak siap panen menumpuk & harga turun, harus bagaimana?',
                    '🌾 Cara menekan biaya pakan tanpa menurunkan bobot ternak?',
                    '💉 Langkah biosekuriti mencegah penyakit saat cuaca ekstrem?',
                ],
            ],
            'nelayan' => [
                'judul_panduan' => 'Panduan Teknis Penangkapan, Rantai Dingin & Hasil Laut',
                'sub_pakar' => 'Konsultasi penyimpanan rantai dingin, cuaca melaut, atau pengolahan saat tangkapan ikan melimpah',
                'placeholder' => 'Contoh: Hasil tangkapan ikan tongkol melimpah dan harga di TPI anjlok, bagaimana cara mengawetkannya?',
                'sektor_komoditas' => 'laut',
                'prompts' => [
                    '🐟 Tangkapan melimpah & harga TPI turun, bagaimana agar ikan tidak rusak?',
                    '🧊 Rasio es dan cara simpan ikan agar tetap Grade A?',
                    '🌊 Strategi efisiensi BBM kapal saat cuaca berubah?',
                ],
            ],
            'pelaku_pangan' => [
                'judul_panduan' => 'Panduan Manajemen Stok, Pengolahan & Rantai Pasok Pangan',
                'sub_pakar' => 'Konsultasi rotasi stok gudang, pengolahan anti-kedaluwarsa, dan kemitraan pasokan',
                'placeholder' => 'Contoh: Stok bahan baku di gudang berlebih, bagaimana strategi pengolahan dan distribusi agar tidak rugi?',
                'sektor_komoditas' => null,
                'prompts' => [
                    '📦 Stok gudang menumpuk, bagaimana strategi promo & olahan awetan?',
                    '🤝 Cara mengamankan pasokan bahan baku langsung dari petani/nelayan?',
                    '🏭 Pengemasan terbaik agar masa simpan produk lebih lama?',
                ],
            ],
            default => [
                'judul_panduan' => 'Panduan Belanja Cerdas & Penyimpanan Pangan Rumah Tangga',
                'sub_pakar' => 'Tanya tips memilih bahan pangan segar, cara menyimpan agar awet, dan info tren harga (wajib login untuk bertanya)',
                'placeholder' => 'Contoh: Bagaimana cara menyimpan cabai, bawang, dan ikan di kulkas agar awet berminggu-minggu?',
                'sektor_komoditas' => null,
                'prompts' => [
                    '❄️ Cara simpan sayur, cabai & ikan agar tidak cepat busuk?',
                    '🛒 Kapan waktu terbaik belanja pangan saat harga sedang turun?',
                    '🥗 Tips mengatur stok belanja mingguan agar tidak terbuang?',
                ],
            ],
        };

        $prediksiRisiko = [];
        $queryKomoditas = Komoditas::query();
        if (!empty($konteksPanduan['sektor_komoditas'])) {
            $queryKomoditas->where('sektor', $konteksPanduan['sektor_komoditas']);
        }
        $komoditasList = $queryKomoditas->get();

        foreach ($komoditasList as $item) {
            $totalProduksi = $user
                ? Produksi::where('user_id', $user->id)->where('komoditas_id', $item->id)->sum('jumlah')
                : Produksi::where('komoditas_id', $item->id)->sum('jumlah');

            $stokPasar = $user
                ? ListingPanen::where('user_id', $user->id)->where('komoditas_id', $item->id)->where('status', 'aktif')->sum('stok_kg')
                : ListingPanen::where('komoditas_id', $item->id)->where('status', 'aktif')->sum('stok_kg');

            $totalStok = $totalProduksi + $stokPasar;

            if ($totalStok >= 80 && $item->persentase_perubahan < 0) {
                $prediksiRisiko[] = [
                    'level' => 'tinggi',
                    'ikon' => '⚠️',
                    'judul' => "Risiko Surplus & Turunnya Harga {$item->nama}",
                    'analisis' => "Tercatat stok {$totalStok} {$item->satuan} sementara harga pasar turun {$item->persentase_perubahan}%. Jika tidak segera diserap, bahan pangan berisiko menumpuk dan rusak.",
                    'rekomendasi' => $this->getRekomendasiSektor($peran, $item->nama),
                ];
            } elseif ($totalStok >= 100) {
                $prediksiRisiko[] = [
                    'level' => 'sedang',
                    'ikon' => '📦',
                    'judul' => "Potensi Penumpukan Stok {$item->nama} ({$totalStok} {$item->satuan})",
                    'analisis' => "Volume stok {$item->nama} tergolong tinggi. Tanpa manajemen penyimpanan atau penjualan bertahap, kualitas bahan bisa menurun dalam 3–5 hari ke depan.",
                    'rekomendasi' => $this->getRekomendasiSektor($peran, $item->nama),
                ];
            }
        }

        if (empty($prediksiRisiko)) {
            $prediksiRisiko[] = $this->getPrediksiDefaultSektor($peran);
        }

        return view('pages.panduan', compact('panduans', 'prediksiRisiko', 'user', 'konfig', 'konteksPanduan'));
    }

    private function getRekomendasiSektor($peran, $namaKomoditas)
    {
        return match ($peran) {
            'petani' => "Pecah listing jual {$namaKomoditas} ke paket grosir di menu Jual Hasil, lakukan sortasi Grade A/B, dan salurkan Grade B ke UMKM saus/olahan kering.",
            'peternak' => "Atur jadwal panen bertahap agar bobot tidak berlebih, tawarkan kontrak pasok ke rumah makan/katering, atau masuk ke rantai dingin (frozen).",
            'nelayan' => "Pastikan es curah 1:1 di palka, alihkan sebagian hasil tangkapan {$namaKomoditas} ke pengolahan ikan asap/asin/beku agar tidak terbuang saat harga TPI turun.",
            'pelaku_pangan' => "Terapkan rotasi stok FEFO, buat paket bundling promo, dan olah stok {$namaKomoditas} menjadi produk turunan siap simpan.",
            default => "Pasokan {$namaKomoditas} sedang melimpah! Ini waktu tepat untuk membeli stok mingguan dan menyimpannya dengan teknik pembekuan/wadah kedap udara.",
        };
    }

    private function getPrediksiDefaultSektor($peran)
    {
        return match ($peran) {
            'petani' => [
                'level' => 'aman',
                'ikon' => '🌱',
                'judul' => 'Prediksi Pasokan Pertanian & Cuaca Lahan',
                'analisis' => 'Stok hasil tani Anda masih dalam batas serapan pasar. Namun kelembapan cuaca berpotensi mempercepat pembusukan pascapanen jika penyimpanan kurang kering.',
                'rekomendasi' => 'Catat setiap hasil panen secara berkala agar sistem memberi peringatan dini apabila stok mendekati batas serapan pasar.'
            ],
            'peternak' => [
                'level' => 'aman',
                'ikon' => '🐾',
                'judul' => 'Prediksi Populasi Ternak & Serapan Pasar',
                'analisis' => 'Stok produksi ternak Anda terpantau seimbang. Keterlambatan panen saat bobot ideal tercapai dapat meningkatkan rasio konsumsi pakan (FCR) dan menekan margin.',
                'rekomendasi' => 'Jadwalkan pre-order penjualan 3–5 hari sebelum masa panen kandang tiba.'
            ],
            'nelayan' => [
                'level' => 'aman',
                'ikon' => '🐟',
                'judul' => 'Prediksi Hasil Tangkapan & Kapasitas Rantai Dingin',
                'analisis' => 'Volume tangkapan Anda terpantau normal. Penurunan mutu paling sering terjadi akibat keterbatasan es curah saat hasil tangkapan mendadak tinggi.',
                'rekomendasi' => 'Selalu siapkan cadangan es berinsulasi dan catat hasil tangkapan segera setelah sandar.'
            ],
            'pelaku_pangan' => [
                'level' => 'aman',
                'ikon' => '🏪',
                'judul' => 'Prediksi Ketersediaan Bahan Baku & Perputaran Stok',
                'analisis' => 'Perputaran stok usaha Anda terpantau stabil. Pantau fluktuasi harga komoditas utama untuk mencegah lonjakan HPP produksi.',
                'rekomendasi' => 'Aktifkan Alert Harga pada bahan baku utama Anda di menu Pantauan & Alert.'
            ],
            default => [
                'level' => 'aman',
                'ikon' => '🛒',
                'judul' => 'Prediksi Ketersediaan & Stabilitas Harga Pangan',
                'analisis' => 'Pasokan pangan pokok dan lauk-pauk di pasar terpantau stabil tanpa kelangkaan ekstrem dalam beberapa hari ke depan.',
                'rekomendasi' => 'Gunakan fitur Alert Harga untuk mendapatkan pengingat otomatis saat harga komoditas favorit Anda turun.'
            ],
        };
    }

    public function tanyaPakar(Request $request)
    {
        $request->validate([
            'pertanyaan' => 'required|string|max:1000',
        ]);

        $apiKey = trim((string) env('GEMINI_API_KEY'));

        if (empty($apiKey)) {
            return response()->json([
                'jawaban' => 'API Key Gemini belum diatur di file .env (GEMINI_API_KEY).'
            ], 500);
        }

        $user = auth()->user();
        $peran = $user?->peran_sektor ?? 'pelaku pangan';
        $komoditas = $user?->komoditas_utama ?? 'pangan umum';
        $lokasi = $user?->lokasi ?? 'Indonesia';

        $promptSistem = "Anda adalah Pakar Agronomi, Peternakan, Perikanan, dan Agribisnis dari platform PanganKu Nasional. "
            . "Pengguna saat ini berperan sebagai {$peran} dengan fokus komoditas {$komoditas} di wilayah {$lokasi}. "
            . "Berikan jawaban praktis, terstruktur, jelas, dan langsung dapat diterapkan di lapangan dalam Bahasa Indonesia.\n\n"
            . "Pertanyaan pengguna: " . $request->pertanyaan;

        $models = [
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-flash-latest',
        ];

        $lastStatus = 500;
        $lastError = '';

        try {
            foreach ($models as $model) {
                $response = Http::withOptions([
                        'force_ip_resolve' => 'v4',
                    ])
                    ->connectTimeout(15)
                    ->timeout(45)
                    ->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                        [
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => $promptSistem]
                                    ]
                                ]
                            ]
                        ]
                    );

                if ($response->successful()) {
                    $data = $response->json();
                    $jawaban = $data['candidates'][0]['content']['parts'][0]['text']
                        ?? 'Maaf, Pakar AI belum dapat merumuskan jawaban saat ini.';

                    return response()->json([
                        'jawaban' => $jawaban,
                        'model' => $model,
                    ]);
                }

                $lastStatus = $response->status();
                $lastError = $response->json('error.message') ?? $response->body();

                // Lanjut ke model cadangan jika 404 (tidak ditemukan), 503 (server sibuk), atau 429 (limit sementara)
                if (!in_array($lastStatus, [404, 503, 429])) {
                    break;
                }
            }

            return response()->json([
                'jawaban' => "Gagal terhubung ke layanan Pakar AI (Kode: {$lastStatus}). Detail: {$lastError}"
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'jawaban' => 'Terjadi gangguan koneksi internet saat menghubungi Pakar AI: ' . $e->getMessage()
            ], 500);
        }
    }
}