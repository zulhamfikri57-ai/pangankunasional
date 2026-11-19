@extends('layouts.app')
@section('content')

@if($user->peran_sektor === 'konsumen')
<div id="bannerUpgrade"
     style="padding: 8px 14px; min-height: 0; height: auto; margin-bottom: 16px;"
     class="rounded-xl bg-emerald-900 text-white flex items-center justify-between gap-3 border border-emerald-700/60 shadow-sm">
    <div class="flex items-center gap-2.5 min-w-0">
        <span style="padding: 2px 8px; font-size: 10px;" class="rounded bg-amber-400 text-emerald-950 font-extrabold uppercase tracking-wide flex-shrink-0">
            Konsumen
        </span>
        <p style="font-size: 12px; margin: 0;" class="text-emerald-50 truncate">
            Petani, peternak, nelayan, atau pelaku pangan? <span class="text-emerald-300">Aktifkan mode produsen.</span>
        </p>
    </div>
    <div class="flex items-center gap-1.5 flex-shrink-0">
        <a href="{{ route('profil') }}" style="padding: 4px 12px; font-size: 11px;" class="rounded-lg bg-amber-400 hover:bg-amber-300 text-emerald-950 font-bold transition whitespace-nowrap">
            Upgrade
        </a>
        <button type="button" onclick="tutupBannerUpgrade()" title="Tutup" style="width: 24px; height: 24px; font-size: 12px; padding: 0;" class="rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center font-bold transition cursor-pointer">
            ✕
        </button>
    </div>
</div>
<script>
    if (sessionStorage.getItem('hideUpgradeBanner') === 'true') { document.getElementById('bannerUpgrade')?.remove(); }
    function tutupBannerUpgrade() {
        const banner = document.getElementById('bannerUpgrade');
        if (banner) { banner.style.display = 'none'; sessionStorage.setItem('hideUpgradeBanner', 'true'); }
    }
</script>
@endif

<!-- ===================================================================== -->
<!-- LETAK PENYESUAIAN NOMOR 1 (KARTU HERO ELASTIS)                         -->
<!-- ===================================================================== -->
<div class="hero-gradient rounded-3xl p-5 sm:p-7 lg:p-8 text-white shadow-lg mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-white/90 text-xs sm:text-sm font-medium">{{ $sapaan }},</p>
            <!-- Menggunakan fluid-title agar ukuran nama menyesuaikan layar -->
            <h2 class="fluid-title font-extrabold tracking-tight">{{ $user->name }}</h2>
            <span class="inline-flex items-center gap-2 mt-2 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-sm text-xs font-semibold">
                {{ $konfig['ikon'] }} {{ $konfig['siklus'] }}
            </span>
        </div>

        <!-- Tombol aksi fleksibel (melebar penuh di HP, otomatis di desktop) -->
        <div class="flex flex-wrap gap-2.5 sm:gap-3">
            <a href="{{ $user->peran_sektor === 'konsumen' ? route('harga') : route('produksi') }}"
               class="flex-1 sm:flex-none text-center px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl bg-white text-emerald-800 font-bold text-xs sm:text-sm shadow hover:bg-stone-50 transition">
                {{ $konfig['aksi_utama'] }}
            </a>
            <a href="{{ route('jual') }}"
               class="flex-1 sm:flex-none text-center px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl bg-white/25 backdrop-blur-sm text-white font-bold text-xs sm:text-sm hover:bg-white/35 transition">
                🏷️ {{ $konfig['aksi_jual'] }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
        <div class="bg-white/15 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/20">
            <p class="text-xs sm:text-sm text-white/90">{{ $user->peran_sektor === 'konsumen' ? 'Sisa Anggaran Pangan' : 'Total Laba / Surplus Bulan Ini' }}</p>
            <!-- Menggunakan fluid-metric agar nominal Rp tidak terpotong di layar sempit -->
            <p class="fluid-metric font-extrabold mt-1 break-words">Rp {{ number_format($totalLaba, 0, ',', '.') }}</p>
            <span class="inline-block mt-2 px-2.5 py-0.5 rounded-lg bg-emerald-900/30 text-emerald-100 text-xs font-semibold">
                {{ $konfig['label_peran'] }}
            </span>
        </div>

        <a href="{{ route('keuangan') }}" class="bg-white/15 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/20 flex items-center justify-between hover:bg-white/20 transition">
            <div class="min-w-0 pr-2">
                <p class="text-xl sm:text-2xl font-bold break-words">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                <p class="text-xs sm:text-sm text-white/85 mt-1">{{ $user->peran_sektor === 'konsumen' ? 'Total belanja pangan bulan ini' : 'Biaya operasional bulan ini' }}</p>
            </div>
            <span class="text-2xl font-bold flex-shrink-0">›</span>
        </a>
    </div>
</div>
<!-- ===================================================================== -->

<!-- Grid 8 Menu Utama -->
<div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-100 mb-6">
    <h3 class="font-bold text-stone-800 mb-4 sm:mb-5 text-sm sm:text-base">Layanan Ekosistem PanganKu Nasional</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <a href="{{ route('produksi') }}" class="p-3.5 sm:p-4 rounded-2xl bg-emerald-50/70 hover:bg-emerald-100/70 text-center transition">
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl sm:text-2xl mb-2">🚜</div>
            <span class="font-bold text-xs sm:text-sm text-stone-800">Catat Produksi</span>
        </a>
        <a href="{{ route('jual') }}" class="p-3.5 sm:p-4 rounded-2xl bg-orange-50/70 hover:bg-orange-100/70 text-center transition">
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-orange-100 text-orange-700 flex items-center justify-center text-xl sm:text-2xl mb-2">🏷️</div>
            <span class="font-bold text-xs sm:text-sm text-stone-800">Jual / Pasarkan</span>
        </a>
        <a href="{{ route('keuangan') }}" class="p-3.5 sm:p-4 rounded-2xl bg-amber-50/70 hover:bg-amber-100/70 text-center transition">
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl sm:text-2xl mb-2">💳</div>
            <span class="font-bold text-xs sm:text-sm text-stone-800">Keuangan</span>
        </a>
        <a href="{{ route('panduan') }}" class="p-3.5 sm:p-4 rounded-2xl bg-blue-50/70 hover:bg-blue-100/70 text-center transition">
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl sm:text-2xl mb-2">🤖</div>
            <span class="font-bold text-xs sm:text-sm text-stone-800">Pakar & Prediksi</span>
        </a>
        <a href="{{ route('laporan') }}" class="p-3.5 sm:p-4 rounded-2xl bg-rose-50/70 hover:bg-rose-100/70 text-center transition">
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl sm:text-2xl mb-2">📊</div>
            <span class="font-bold text-xs sm:text-sm text-stone-800">Laporan</span>
        </a>
        <a href="{{ route('harga') }}" class="p-3.5 sm:p-4 rounded-2xl bg-purple-50/70 hover:bg-purple-100/70 text-center transition">
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl sm:text-2xl mb-2">📶</div>
            <span class="font-bold text-xs sm:text-sm text-stone-800">Harga Pasar</span>
        </a>
        <a href="{{ route('pantauan') }}" class="p-3.5 sm:p-4 rounded-2xl bg-teal-50/70 hover:bg-teal-100/70 text-center transition">
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center text-xl sm:text-2xl mb-2">🔔</div>
            <span class="font-bold text-xs sm:text-sm text-stone-800">Pantauan Alert</span>
        </a>
        <a href="{{ route('profil') }}" class="p-3.5 sm:p-4 rounded-2xl bg-stone-100 hover:bg-stone-200/70 text-center transition">
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-stone-200 text-stone-700 flex items-center justify-center text-xl sm:text-2xl mb-2">👤</div>
            <span class="font-bold text-xs sm:text-sm text-stone-800">Profil & Peran</span>
        </a>
    </div>
</div>

<!-- Info Sektor Dinamis -->
<div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex items-start sm:items-center gap-3">
        <span class="text-2xl flex-shrink-0">{{ $konfig['info_ikon'] }}</span>
        <div>
            <h4 class="font-bold text-sm text-amber-900">{{ $konfig['info_judul'] }}</h4>
            <p class="text-xs text-amber-700 mt-0.5">{{ $konfig['info_desc'] }}</p>
        </div>
    </div>
    <a href="{{ route('panduan') }}" class="text-xs font-bold text-amber-900 underline whitespace-nowrap self-end sm:self-center">Cek Prediksi →</a>
</div>

@endsection