<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use App\Models\Komoditas;
use App\Models\ListingPanen;
use App\Models\Produksi;
use App\Models\SiklusProduksi;
use App\Models\User;
use App\Models\WilayahDistribusi;
use App\Services\HargaSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileApiController extends Controller
{
    // 1. LOGIN & TERBITKAN TOKEN SANCTUM
    public function login(Request $request)
    {
        $request->validate([
            'identitas' => 'required|string', // Email atau No. HP
            'password'  => 'required|string',
        ]);

        $identitas = trim($request->identitas);
        $user = User::where('email', $identitas)
            ->orWhere('no_hp', $identitas)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email/Nomor HP atau kata sandi salah.',
            ], 401);
        }

        // Hapus token lama perangkat yang sama (opsional) & buat token baru
        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'no_hp'        => $user->no_hp,
                'peran_sektor' => $user->peran_sektor,
                'lokasi'       => $user->lokasi,
            ],
        ]);
    }

    // 2. LOGOUT (CABUT TOKEN)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari sesi perangkat.',
        ]);
    }

    // 3. PANTAUAN HARGA KOMODITAS NASIONAL (PUBLIK / TERAUTENTIKASI)
    public function komoditas(Request $request)
    {
        $query = Komoditas::query();
        if ($request->filled('sektor')) {
            $query->where('sektor', $request->sektor);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('nama')->get(),
        ]);
    }

    // 4. RINGKASAN DASHBOARD & KEUANGAN PENGGUNA
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $totalPemasukan = Keuangan::where('user_id', $user->id)
            ->where('jenis', 'pemasukan')
            ->sum('nominal');

        $totalPengeluaran = Keuangan::where('user_id', $user->id)
            ->where('jenis', 'pengeluaran')
            ->sum('nominal');

        $siklusAktif = SiklusProduksi::with('komoditas')
            ->where('user_id', $user->id)
            ->where('status', 'berjalan')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'user'              => $user,
                'total_pemasukan'   => (float) $totalPemasukan,
                'total_pengeluaran' => (float) $totalPengeluaran,
                'laba_bersih'       => (float) ($totalPemasukan - $totalPengeluaran),
                'siklus_aktif'      => $siklusAktif,
            ],
        ]);
    }

    // 5. CATAT HASIL PRODUKSI / PANEN DARI MOBILE (ANTI DUPLIKAT)
    public function storeProduksi(Request $request)
    {
        $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'siklus_id'    => 'nullable|exists:siklus_produksis,id',
            'jumlah'       => 'required|numeric|min:0.1',
            'tanggal'      => 'nullable|date',
            'catatan'      => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $komoditas = Komoditas::findOrFail($request->komoditas_id);

        $produksi = Produksi::create([
            'user_id'      => $user->id,
            'komoditas_id' => $komoditas->id,
            'siklus_id'    => $request->siklus_id,
            'jumlah'       => $request->jumlah,
            'tanggal'      => $request->tanggal ?? now()->toDateString(),
            'catatan'      => $request->catatan,
        ]);

        // Sinkronisasi pasokan & tren harga otomatis
        if (class_exists(HargaSyncService::class)) {
            HargaSyncService::sinkronkan($komoditas->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hasil produksi berhasil dicatat dan disinkronkan ke stok nasional.',
            'data'    => $produksi->load('komoditas'),
        ], 201);
    }

    // 6. PASAR PANEN (DAFTAR & POSTING PENAWARAN)
    public function pasarPanen()
    {
        $listings = ListingPanen::with(['komoditas', 'user:id,name,no_hp,lokasi'])
            ->where('status', 'aktif')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $listings,
        ]);
    }

    public function storePasarPanen(Request $request)
    {
        $request->validate([
            'komoditas_id'  => 'required|exists:komoditas,id',
            'stok_kg'       => 'required|numeric|min:1',
            'harga_per_kg'  => 'required|numeric|min:100',
            'lokasi_jemput' => 'required|string|max:150',
        ]);

        $listing = ListingPanen::create([
            'user_id'       => $request->user()->id,
            'komoditas_id'  => $request->komoditas_id,
            'stok_kg'       => $request->stok_kg,
            'harga_per_kg'  => $request->harga_per_kg,
            'lokasi_jemput' => $request->lokasi_jemput,
            'status'        => 'aktif',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penawaran panen berhasil diterbitkan ke Pasar Nasional.',
            'data'    => $listing->load('komoditas'),
        ], 201);
    }

    // 7. SPK DISTRIBUSI SAW DINAMIS UNTUK MOBILE
    public function spkDistribusi(Request $request)
    {
        $komoditasId = $request->get('komoditas_id', Komoditas::first()?->id);
        $komoditas = Komoditas::findOrFail($komoditasId);

        $hargaDasarPasar = (float) ($komoditas->harga_pasar ?? 30000);
        $hargaProdusen = (float) ($komoditas->harga_petani ?? 24000);

        $wilayahDb = WilayahDistribusi::where('is_aktif', true)->get();

        $matriks = $wilayahDb->map(function ($w) use ($hargaDasarPasar, $hargaProdusen) {
            $hargaTujuan = $hargaDasarPasar * (float) $w->faktor_harga;
            return [
                'wilayah'            => $w->nama_wilayah,
                'provinsi'           => $w->provinsi,
                'jarak_km'           => (float) $w->jarak_km,
                'harga_pasar_tujuan' => round($hargaTujuan),
                'c1_selisih'         => max(100, $hargaTujuan - $hargaProdusen),
                'c2_defisit'         => (int) $w->tingkat_defisit,
                'c3_logistik'        => max(100, (float) $w->biaya_logistik_kg),
            ];
        });

        $maxC1 = $matriks->max('c1_selisih') ?: 1;
        $maxC2 = $matriks->max('c2_defisit') ?: 1;
        $minC3 = $matriks->min('c3_logistik') ?: 1;

        $hasil = $matriks->map(function ($row) use ($maxC1, $maxC2, $minC3, $hargaProdusen) {
            $r1 = $row['c1_selisih'] / $maxC1;
            $r2 = $row['c2_defisit'] / $maxC2;
            $r3 = $minC3 / $row['c3_logistik'];
            $skor = ($r1 * 0.40) + ($r2 * 0.35) + ($r3 * 0.25);

            $marginBersih = max(0, $row['harga_pasar_tujuan'] - $hargaProdusen - $row['c3_logistik']);
            $rekomendasiHarga = round($hargaProdusen + ($marginBersih * 0.45), -2);

            return [
                'wilayah'           => $row['wilayah'],
                'provinsi'          => $row['provinsi'],
                'jarak_km'          => $row['jarak_km'],
                'skor_saw'          => round($skor, 4),
                'rekomendasi_harga' => $rekomendasiHarga,
                'estimasi_profit'   => round($rekomendasiHarga - $hargaProdusen),
                'biaya_logistik'    => $row['c3_logistik'],
                'tingkat_defisit'   => $row['c2_defisit'],
            ];
        })->sortByDesc('skor_saw')->values();

        return response()->json([
            'success'   => true,
            'komoditas' => $komoditas,
            'ranking'   => $hasil,
        ]);
    }
}