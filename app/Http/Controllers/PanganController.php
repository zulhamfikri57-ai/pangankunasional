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
    private function getKonfigurasiSektor($user)
    {
        $peran = $user->peran_sektor ?? 'konsumen';
        $komoditas = $user->komoditas_utama ?: 'Pangan Umum';

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
                'label_peran' => 'Konsumen Pangan',
                'siklus' => 'Pantauan Belanja Pangan 2026',
                'aksi_utama' => '📈 Cek Harga Hari Ini',
                'aksi_jual' => 'Pasar Pangan',
                'sektor_default' => 'darat',
                'kategori_biaya' => ['Beras & Pangan Pokok', 'Sayur & Buah', 'Ikan, Daging & Telur', 'Bumbu Dapur', 'Ongkos Kirim', 'Lain-lain'],
                'kategori_masuk' => ['Anggaran Belanja Bulanan', 'Cashback / Hemat', 'Lain-lain'],
                'info_ikon' => '📊',
                'info_judul' => 'Info Ketersediaan & Tren Harga Pangan',
                'info_desc' => 'Pasokan beras dan perikanan laut terpantau stabil minggu ini. Aktifkan Alert Harga untuk notifikasi.',
            ],
        };
    }

    public function beranda()
    {
        $user = auth()->user();
        $konfig = $this->getKonfigurasiSektor($user);

        $totalPemasukan = Keuangan::where('user_id', $user->id)->where('jenis', 'pemasukan')->sum('nominal');
        $totalPengeluaran = Keuangan::where('user_id', $user->id)->where('jenis', 'pengeluaran')->sum('nominal');
        $totalLaba = $totalPemasukan - $totalPengeluaran;

        $jam = now('Asia/Jakarta')->hour;
        $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

        return view('pages.beranda', compact('user', 'konfig', 'totalLaba', 'totalPengeluaran', 'sapaan'));
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

        // Filter komoditas sesuai sektor produsen (jika konsumen/pelaku pangan, tampilkan semua)
        $queryKomoditas = Komoditas::query();
        if (in_array($user->peran_sektor, ['petani', 'peternak', 'nelayan'])) {
            $queryKomoditas->where('sektor', $konfig['sektor_default']);
        }
        $komoditas = $queryKomoditas->get();
        $semuaKomoditas = Komoditas::all();

        // Listing milik pengguna yang sedang login
        $listings = ListingPanen::with('komoditas')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $listingAktif = $listings->where('status', 'aktif');
        $totalPendapatan = $listingAktif->sum(fn($l) => $l->stok_kg * $l->harga_jual_per_kg);

        // Etalase pasar dari seluruh produsen (berguna untuk konsumen & pelaku pangan)
        $pasarListings = ListingPanen::with(['komoditas', 'user'])
            ->where('status', 'aktif')
            ->latest()
            ->take(12)
            ->get();

        return view('pages.jual', compact(
            'user',
            'konfig',
            'komoditas',
            'semuaKomoditas',
            'listings',
            'listingAktif',
            'totalPendapatan',
            'pasarListings'
        ));
    }

    public function storeJualHasil(Request $request)
    {
        $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'stok_kg' => 'required|numeric|min:1',
            'harga_jual_per_kg' => 'required|numeric|min:100',
        ]);

        ListingPanen::create([
            'user_id' => auth()->id(),
            'komoditas_id' => $request->komoditas_id,
            'stok_kg' => $request->stok_kg,
            'harga_jual_per_kg' => $request->harga_jual_per_kg,
            'status' => 'aktif',
        ]);

        return redirect()->route('jual')->with('success', 'Produk pangan Anda berhasil dipublikasikan ke pasar!');
    }

    public function updateJualHasil(Request $request, $id)
    {
        $listing = ListingPanen::where('user_id', auth()->id())->findOrFail($id);

        // Jika tombol "Tandai Terjual" diklik, otomatis catat juga ke pemasukan keuangan
        if ($request->input('aksi') === 'terjual') {
            $listing->update(['status' => 'terjual']);

            $konfig = $this->getKonfigurasiSektor(auth()->user());
            Keuangan::create([
                'user_id' => auth()->id(),
                'jenis' => 'pemasukan',
                'kategori' => $konfig['kategori_masuk'][0] ?? 'Penjualan Produk',
                'nominal' => $listing->stok_kg * $listing->harga_jual_per_kg,
                'musim_tanam' => $konfig['siklus'],
                'tanggal' => now()->toDateString(),
            ]);

            return redirect()->route('jual')->with('success', 'Listing ditandai terjual & pemasukan otomatis dicatat ke Keuangan!');
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

    public function destroyJualHasil($id)
    {
        $listing = ListingPanen::where('user_id', auth()->id())->findOrFail($id);
        $listing->delete();

        return redirect()->route('jual')->with('success', 'Listing penjualan berhasil dihapus!');
    }

    public function keuangan()
    {
        $user = auth()->user();
        $konfig = $this->getKonfigurasiSektor($user);

        $pemasukan = Keuangan::where('user_id', $user->id)->where('jenis', 'pemasukan')->sum('nominal');
        $pengeluaran = Keuangan::where('user_id', $user->id)->where('jenis', 'pengeluaran')->sum('nominal');
        $labaBersih = $pemasukan - $pengeluaran;
        $transaksi = Keuangan::where('user_id', $user->id)->latest()->take(15)->get();

        return view('pages.keuangan', compact('user', 'konfig', 'pemasukan', 'pengeluaran', 'labaBersih', 'transaksi'));
    }

    public function storeKeuangan(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'kategori' => 'required|string',
            'nominal' => 'required|numeric|min:100',
        ]);

        $user = auth()->user();
        $konfig = $this->getKonfigurasiSektor($user);

        Keuangan::create([
            'user_id' => $user->id,
            'jenis' => $request->jenis,
            'kategori' => $request->kategori,
            'nominal' => $request->nominal,
            'musim_tanam' => $konfig['siklus'],
            'tanggal' => now()->toDateString(),
        ]);

        $label = $request->jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran';
        return redirect()->route('keuangan')->with('success', "{$label} berhasil dicatat ke pembukuan Anda!");
    }

    public function updateKeuangan(Request $request, $id)
    {
        $request->validate([
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'kategori' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:100',
            'tanggal' => 'required|date',
        ]);

        $transaksi = Keuangan::where('user_id', auth()->id())->findOrFail($id);
        $transaksi->update([
            'jenis' => $request->jenis,
            'kategori' => $request->kategori,
            'nominal' => $request->nominal,
            'tanggal' => $request->tanggal,
        ]);

        return redirect()->route('keuangan')->with('success', 'Transaksi keuangan berhasil diperbarui!');
    }

    public function destroyKeuangan($id)
    {
        $transaksi = Keuangan::where('user_id', auth()->id())->findOrFail($id);
        $transaksi->delete();

        return redirect()->route('keuangan')->with('success', 'Transaksi keuangan berhasil dihapus!');
    }

    public function laporan()
    {
        $userId = auth()->id();
        $konfig = $this->getKonfigurasiSektor(auth()->user());

        $pemasukan = Keuangan::where('user_id', $userId)->where('jenis', 'pemasukan')->sum('nominal');
        $pengeluaran = Keuangan::where('user_id', $userId)->where('jenis', 'pengeluaran')->sum('nominal');
        $labaBersih = $pemasukan - $pengeluaran;

        $rincianBiaya = Keuangan::where('user_id', $userId)
            ->where('jenis', 'pengeluaran')
            ->selectRaw('kategori, SUM(nominal) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        return view('pages.laporan', compact('konfig', 'pemasukan', 'labaBersih', 'pengeluaran', 'rincianBiaya'));
    }

    public function harga(Request $request)
    {
        $kategori = $request->get('kategori', 'Semua');
        $query = Komoditas::query();
        if ($kategori !== 'Semua') {
            $query->where('kategori_harga', $kategori);
        }
        $komoditas = $query->get();

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
        $peran = $user->peran_sektor ?? 'konsumen';
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
                'sub_pakar' => 'Tanya tips memilih bahan pangan segar, cara menyimpan agar awet, dan info tren harga',
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
            // Hitung stok milik akun pengguna yang sedang login
            $totalProduksi = Produksi::where('user_id', $user->id)->where('komoditas_id', $item->id)->sum('jumlah');
            $stokPasar = ListingPanen::where('user_id', $user->id)->where('komoditas_id', $item->id)->where('status', 'aktif')->sum('stok_kg');
            $totalStok = $totalProduksi + $stokPasar;

            if ($totalStok >= 80 && $item->persentase_perubahan < 0) {
                $prediksiRisiko[] = [
                    'level' => 'tinggi',
                    'ikon' => '⚠️',
                    'judul' => "Risiko Surplus & Turunnya Harga {$item->nama}",
                    'analisis' => "Stok pribadi Anda tercatat {$totalStok} {$item->satuan} sementara harga pasar turun {$item->persentase_perubahan}%. Jika tidak segera diserap, bahan pangan berisiko menumpuk, rusak, dan tersia-siakan.",
                    'rekomendasi' => $this->getRekomendasiSektor($peran, $item->nama),
                ];
            } elseif ($totalStok >= 100) {
                $prediksiRisiko[] = [
                    'level' => 'sedang',
                    'ikon' => '📦',
                    'judul' => "Potensi Penumpukan Stok {$item->nama} ({$totalStok} {$item->satuan})",
                    'analisis' => "Volume stok {$item->nama} Anda tergolong tinggi. Tanpa manajemen penyimpanan atau penjualan bertahap, kualitas bahan bisa menurun dalam 3–5 hari ke depan.",
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

        $user = auth()->user();
        $peran = $user->peran_sektor ?? 'konsumen';
        $pertanyaan = $request->pertanyaan;

        $sektorFilter = match ($peran) {
            'petani' => 'darat',
            'peternak' => 'ternak',
            'nelayan' => 'laut',
            default => null,
        };

        $queryStok = Produksi::with('komoditas')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5);

        if ($sektorFilter) {
            $queryStok->where('sektor', $sektorFilter);
        }

        $ringkasanStok = $queryStok->get()
            ->map(fn($p) => "{$p->komoditas->nama}: {$p->jumlah} {$p->satuan}")
            ->implode(', ');

        $peranPakarAI = match ($peran) {
            'petani' => 'Pakar Agronomi, Budidaya Tanaman, dan Pascapanen Pertanian',
            'peternak' => 'Pakar Peternakan, Nutrisi Pakan, Kesehatan Hewan, dan Agribisnis Ternak',
            'nelayan' => 'Pakar Perikanan Tangkap, Budidaya Perairan, dan Teknologi Rantai Dingin Hasil Laut',
            'pelaku_pangan' => 'Pakar Teknologi Pangan, Manajemen Gudang, Pengolahan, dan Rantai Pasok',
            default => 'Pakar Gizi, Manajemen Belanja Pangan Keluarga, dan Penyimpanan Bahan Makanan Anti-Food Waste',
        };

        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'jawaban' => "⚠️ API Key Gemini belum dipasang di file .env.\n\nSaran cepat untuk {$user->name} ({$peran}): Untuk mengatasi \"{$pertanyaan}\", pastikan pencatatan stok rutin dilakukan, pisahkan produk berdasarkan mutu kesegaran agar tidak saling membusukkan, dan manfaatkan fitur Pantauan Alert untuk menentukan waktu jual/beli terbaik."
            ]);
        }

        $promptSistem = "Anda adalah {$peranPakarAI} di aplikasi PanganKu Nasional. "
            . "Pengguna saat ini bernama {$user->name}, jenis akun: '{$peran}', fokus komoditas: '" . ($user->komoditas_utama ?: 'Umum') . "', wilayah: '" . ($user->lokasi ?: 'Indonesia') . "'. "
            . "Data stok pribadi pengguna tercatat: [" . ($ringkasanStok ?: 'Belum ada data stok') . "]. "
            . "Berikan jawaban yang 100% relevan dengan bidang '{$peran}', praktis, terstruktur dalam poin-poin singkat berbahasa Indonesia, serta sertakan langkah antisipasi risiko ke depan agar bahan pangan tidak tersia-siakan (mencegah food waste & kerugian harga).\n\n"
            . "Pertanyaan Pengguna: {$pertanyaan}";

        try {
            $response = Http::timeout(25)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
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
                $jawaban = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, pakar AI belum dapat merumuskan jawaban saat ini.';
                return response()->json(['jawaban' => $jawaban]);
            }

            return response()->json([
                'jawaban' => 'Gagal terhubung ke layanan Pakar AI (Kode: ' . $response->status() . '). Periksa kembali GEMINI_API_KEY di file .env.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'jawaban' => 'Terjadi gangguan koneksi internet saat menghubungi Pakar AI: ' . $e->getMessage()
            ], 500);
        }
    }
}