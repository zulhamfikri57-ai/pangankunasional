@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">🏛️ Pusat Komando Administrator PanganKu Nasional</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Pemantauan ketahanan pasokan nasional, moderasi verifikasi identitas produsen, dan kendali sistem</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.komoditas') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            📈 Kelola Komoditas
        </a>
        <a href="{{ route('admin.pengguna') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            👥 Kelola Pengguna
        </a>
        <a href="{{ route('admin.panduan') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            📖 Kelola Panduan
        </a>
    </div>
</div>

<!-- KARTU STATISTIK UTAMA -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-3xl border border-stone-200/80 shadow-xs">
        <span class="text-xs font-bold text-stone-400 uppercase">Total Pengguna Terdaftar</span>
        <p class="text-2xl font-extrabold text-stone-900 mt-1">{{ number_format($totalUser, 0, ',', '.') }} <span class="text-xs font-normal text-stone-500">Akun</span></p>
        <p class="text-[11px] text-stone-500 mt-1.5">
            🌾 {{ $rekapPeran['petani'] }} Petani • 🐄 {{ $rekapPeran['peternak'] }} Ternak • 🐟 {{ $rekapPeran['nelayan'] }} Nelayan
        </p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-stone-200/80 shadow-xs">
        <span class="text-xs font-bold text-stone-400 uppercase">Total Produksi Tercatat</span>
        <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ number_format($totalProduksiKg, 0, ',', '.') }} <span class="text-xs font-normal text-stone-500">kg</span></p>
        <p class="text-[11px] text-stone-500 mt-1.5">Akumulasi panen seluruh sektor</p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-stone-200/80 shadow-xs">
        <span class="text-xs font-bold text-stone-400 uppercase">Listing Pasar Aktif</span>
        <p class="text-2xl font-extrabold text-amber-700 mt-1">{{ number_format($totalListingAktif, 0, ',', '.') }} <span class="text-xs font-normal text-stone-500">Penawaran</span></p>
        <p class="text-[11px] text-stone-500 mt-1.5">Stok siap angkut & pre-order</p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-stone-200/80 shadow-xs">
        <span class="text-xs font-bold text-stone-400 uppercase">Perputaran Pemasukan Mitra</span>
        <p class="text-xl font-extrabold text-stone-900 mt-1">Rp {{ number_format($totalPerputaranUang, 0, ',', '.') }}</p>
        <p class="text-[11px] text-stone-500 mt-1.5">Tercatat pada pembukuan mitra</p>
    </div>
</div>

<!-- PANEL MODERASI VERIFIKASI IDENTITAS & UPGRADE PERAN PRODUSEN -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/80 shadow-xs mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-stone-100">
        <div>
            <h3 class="font-extrabold text-base text-stone-800">🛡️ Moderasi Verifikasi Peran Usaha Pangan</h3>
            <p class="text-xs text-stone-500">Periksa keaslian NIK KTP, alamat lokasi usaha, dan dokumen bukti sebelum mengaktifkan hak akses produsen</p>
        </div>
        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-extrabold w-fit">
            Menunggu Verifikasi: {{ $pengajuanPeran->where('status_verifikasi_peran', 'pending')->count() }}
        </span>
    </div>

    <div class="space-y-4">
        @forelse($pengajuanPeran as $calon)
        <div class="p-4 rounded-2xl border {{ $calon->status_verifikasi_peran === 'pending' ? 'border-amber-300 bg-amber-50/40' : 'border-stone-200/70 bg-stone-50/50' }}">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-extrabold text-sm text-stone-800">{{ $calon->name }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800">
                            Mengajukan: {{ $calon->peran_diajukan }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase
                            {{ $calon->status_verifikasi_peran === 'pending' ? 'bg-amber-200 text-amber-950' : ($calon->status_verifikasi_peran === 'approved' ? 'bg-emerald-600 text-white' : 'bg-rose-100 text-rose-800') }}">
                            {{ $calon->status_verifikasi_peran }}
                        </span>
                    </div>

                    <p class="text-xs text-stone-600">
                        🪪 NIK: <strong>{{ $calon->nik_identitas }}</strong> •
                        📱 HP/WA: <strong>{{ $calon->no_hp }}</strong> •
                        ✉️ {{ str_ends_with($calon->email, '@noemail.panganku.local') ? 'Daftar via HP' : $calon->email }}
                    </p>
                    <p class="text-xs text-stone-600">
                        📍 Lokasi Usaha: <strong>{{ $calon->alamat_usaha_riil }}</strong>
                    </p>

                    @if($calon->dokumen_verifikasi_path)
                        <div class="pt-1.5">
                            <a href="{{ asset('storage/' . $calon->dokumen_verifikasi_path) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-100 hover:bg-sky-200 text-sky-900 text-xs font-bold transition">
                                📄 Buka & Periksa Dokumen Asli (KTP / Surat Usaha / Foto Lahan) ↗
                            </a>
                        </div>
                    @endif
                </div>

                @if($calon->status_verifikasi_peran === 'pending')
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                    <form action="{{ route('admin.verifikasi.peran', $calon->id) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="keputusan" value="approved">
                        <input type="text" name="catatan_admin" placeholder="Catatan opsional..."
                            class="px-3 py-2 rounded-xl border border-stone-300 bg-white text-xs w-40">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition cursor-pointer">
                            ✅ Setujui
                        </button>
                    </form>

                    <form action="{{ route('admin.verifikasi.peran', $calon->id) }}" method="POST"
                        onsubmit="const alasan = prompt('Masukkan alasan penolakan dokumen:'); if(!alasan) return false; this.querySelector('[name=catatan_admin]').value = alasan;">
                        @csrf
                        <input type="hidden" name="keputusan" value="rejected">
                        <input type="hidden" name="catatan_admin" value="">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition cursor-pointer">
                            ❌ Tolak
                        </button>
                    </form>
                </div>
                @else
                <div class="text-xs text-stone-500">
                    Catatan Admin: <em>{{ $calon->catatan_admin_verifikasi ?? '-' }}</em>
                </div>
                @endif
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 py-4 text-center">Belum ada pengajuan verifikasi peran usaha dari pengguna.</p>
        @endforelse
    </div>
</div>

<!-- GRID DETEKSI PASOKAN NASIONAL & AKTIVITAS PRODUKSI TERBARU -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Deteksi Surplus / Peringatan Pasokan Nasional -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/80 shadow-xs">
        <h3 class="font-extrabold text-base text-stone-800 mb-1">🚨 Deteksi Surplus & Peringatan Pasokan Nasional</h3>
        <p class="text-xs text-stone-500 mb-4">Komoditas dengan akumulasi stok tinggi atau penurunan harga pasar</p>

        <div class="space-y-3">
            @forelse($peringatanNasional as $warn)
            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $warn['ikon'] ?? '🌾' }}</span>
                    <div>
                        <p class="font-extrabold text-sm text-stone-800">{{ $warn['nama'] }}</p>
                        <span class="text-[11px] font-semibold text-stone-500 uppercase">Sektor {{ $warn['sektor'] }}</span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-extrabold text-sm text-amber-900">{{ number_format($warn['stok'], 0, ',', '.') }} kg</p>
                    <span class="text-[11px] font-bold {{ $warn['perubahan'] < 0 ? 'text-emerald-700' : 'text-stone-500' }}">
                        Harga {{ $warn['perubahan'] > 0 ? '+' : '' }}{{ $warn['perubahan'] }}%
                    </span>
                </div>
            </div>
            @empty
            <p class="text-xs text-stone-500 py-6 text-center">Tidak ada peringatan surplus komoditas saat ini.</p>
            @endforelse
        </div>
    </div>

    <!-- Log Produksi Terbaru Mitra -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/80 shadow-xs">
        <h3 class="font-extrabold text-base text-stone-800 mb-1">📦 Aktivitas Pencatatan Produksi Terbaru</h3>
        <p class="text-xs text-stone-500 mb-4">Laporan hasil panen, ternak, dan tangkapan terkini dari mitra</p>

        <div class="space-y-3">
            @forelse($produksiTerbaru as $prod)
            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-stone-50 border border-stone-200/60">
                <div>
                    <p class="font-extrabold text-sm text-stone-800">
                        {{ $prod->komoditas->ikon ?? '🌾' }} {{ $prod->komoditas->nama ?? 'Komoditas' }}
                    </p>
                    <p class="text-[11px] text-stone-500">
                        Mitra: <strong>{{ $prod->user->name ?? 'Pengguna' }}</strong> • {{ $prod->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-extrabold text-xs">
                    +{{ number_format($prod->jumlah, 0, ',', '.') }} {{ $prod->komoditas->satuan ?? 'kg' }}
                </span>
            </div>
            @empty
            <p class="text-xs text-stone-500 py-6 text-center">Belum ada aktivitas pencatatan produksi dari mitra.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection