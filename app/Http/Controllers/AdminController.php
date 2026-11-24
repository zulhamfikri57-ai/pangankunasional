<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Komoditas;
use App\Models\Produksi;
use App\Models\ListingPanen;
use App\Models\Keuangan;
use App\Models\PanduanTanam;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUser = User::where('peran_sektor', '!=', 'admin')->count();
        $rekapPeran = [
            'petani' => User::where('peran_sektor', 'petani')->count(),
            'peternak' => User::where('peran_sektor', 'peternak')->count(),
            'nelayan' => User::where('peran_sektor', 'nelayan')->count(),
            'pelaku_pangan' => User::where('peran_sektor', 'pelaku_pangan')->count(),
            'konsumen' => User::where('peran_sektor', 'konsumen')->count(),
        ];

        $totalProduksiKg = Produksi::sum('jumlah');
        $totalListingAktif = ListingPanen::where('status', 'aktif')->count();
        $totalPerputaranUang = Keuangan::where('jenis', 'pemasukan')->sum('nominal');

        $pengajuanPeran = User::whereIn('status_verifikasi_peran', ['pending', 'approved', 'rejected'])
            ->orderByRaw("FIELD(status_verifikasi_peran, 'pending', 'approved', 'rejected')")
            ->latest('updated_at')
            ->get();

        // Deteksi Nasional: Komoditas dengan stok berlebih & harga turun
        $peringatanNasional = [];
        foreach (Komoditas::all() as $item) {
            $stokTotal = Produksi::where('komoditas_id', $item->id)->sum('jumlah')
                       + ListingPanen::where('komoditas_id', $item->id)->where('status', 'aktif')->sum('stok_kg');

            if ($stokTotal >= 100 || ($stokTotal >= 50 && $item->persentase_perubahan < 0)) {
                $peringatanNasional[] = [
                    'nama' => $item->nama,
                    'ikon' => $item->ikon,
                    'sektor' => $item->sektor,
                    'stok' => $stokTotal,
                    'perubahan' => $item->persentase_perubahan,
                ];
            }
        }

        $produksiTerbaru = Produksi::with(['user', 'komoditas'])->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'totalUser',
            'rekapPeran',
            'totalProduksiKg',
            'totalListingAktif',
            'totalPerputaranUang',
            'pengajuanPeran',
            'peringatanNasional',
            'produksiTerbaru'
        ));
    }

    // --- MANAJEMEN HARGA & KOMODITAS ---
    public function komoditas()
    {
        $komoditas = Komoditas::orderBy('sektor')->orderBy('nama')->get();
        return view('admin.komoditas', compact('komoditas'));
    }

    public function storeKomoditas(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'sektor' => 'required|in:darat,ternak,laut',
            'kategori_harga' => 'required|string|max:50',
            'harga_pasar' => 'required|numeric|min:100',
            'harga_petani' => 'required|numeric|min:100',
            'persentase_perubahan' => 'required|numeric',
            'ikon' => 'nullable|string|max:10',
        ]);

        Komoditas::create([
            'nama' => $request->nama,
            'sektor' => $request->sektor,
            'kategori_harga' => $request->kategori_harga,
            'satuan' => 'kg',
            'harga_pasar' => $request->harga_pasar,
            'harga_petani' => $request->harga_petani,
            'harga_sekarang' => $request->harga_pasar,
            'harga_acuan_pemerintah' => $request->harga_pasar,
            'persentase_perubahan' => $request->persentase_perubahan,
            'is_favorit' => $request->has('is_favorit'),
            'ikon' => $request->ikon ?: '🌾',
        ]);

        return redirect()->route('admin.komoditas')->with('success', 'Komoditas baru berhasil ditambahkan!');
    }

    public function updateKomoditas(Request $request, $id)
    {
        $request->validate([
            'harga_pasar' => 'required|numeric|min:100',
            'harga_petani' => 'required|numeric|min:100',
            'persentase_perubahan' => 'required|numeric',
        ]);

        $item = Komoditas::findOrFail($id);
        $item->update([
            'harga_pasar' => $request->harga_pasar,
            'harga_petani' => $request->harga_petani,
            'harga_sekarang' => $request->harga_pasar,
            'harga_acuan_pemerintah' => $request->harga_pasar,
            'persentase_perubahan' => $request->persentase_perubahan,
            'is_favorit' => $request->has('is_favorit'),
        ]);

        return redirect()->route('admin.komoditas')->with('success', "Harga komoditas {$item->nama} berhasil diperbarui!");
    }

    public function destroyKomoditas($id)
    {
        Komoditas::findOrFail($id)->delete();
        return redirect()->route('admin.komoditas')->with('success', 'Komoditas berhasil dihapus!');
    }

    // --- MANAJEMEN PENGGUNA ---
    public function pengguna()
    {
        $users = User::latest()->get();
        return view('admin.pengguna', compact('users'));
    }

    public function updatePengguna(Request $request, $id)
    {
        $request->validate([
            'peran_sektor' => 'required|in:konsumen,petani,peternak,nelayan,pelaku_pangan,admin',
        ]);

        $user = User::findOrFail($id);
        $user->peran_sektor = $request->peran_sektor;
        $user->save();

        return redirect()->route('admin.pengguna')->with('success', "Peran akun {$user->name} berhasil diubah!");
    }

    public function destroyPengguna($id)
    {
        if (auth()->id() == $id) {
            return redirect()->route('admin.pengguna')->with('success', 'Tidak dapat menghapus akun admin yang sedang digunakan.');
        }

        User::findOrFail($id)->delete();
        return redirect()->route('admin.pengguna')->with('success', 'Akun pengguna berhasil dihapus!');
    }

    // --- MANAJEMEN PANDUAN TEKNIS ---
    public function panduan()
    {
        $panduans = PanduanTanam::orderBy('sektor')->latest()->get();
        return view('admin.panduan', compact('panduans'));
    }

    public function storePanduan(Request $request)
    {
        $request->validate([
            'sektor' => 'required|in:petani,peternak,nelayan,pelaku_pangan,konsumen',
            'komoditas_fokus' => 'required|string|max:100',
            'judul' => 'required|string|max:150',
            'deskripsi' => 'required|string',
            'ikon' => 'nullable|string|max:10',
        ]);

        PanduanTanam::create([
            'sektor' => $request->sektor,
            'komoditas_fokus' => $request->komoditas_fokus,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'ikon' => $request->ikon ?: '📖',
        ]);

        return redirect()->route('admin.panduan')->with('success', 'Materi panduan baru berhasil diterbitkan!');
    }

    public function destroyPanduan($id)
    {
        PanduanTanam::findOrFail($id)->delete();
        return redirect()->route('admin.panduan')->with('success', 'Materi panduan berhasil dihapus!');
    }
}