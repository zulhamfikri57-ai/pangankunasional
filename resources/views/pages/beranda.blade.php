@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">
            {{ $sapaan }}, {{ $user ? $user->name : 'Pengunjung PanganKu' }}! 👋
        </h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">{{ $konfig['siklus'] }}</p>
    </div>
    <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit">
        {{ $konfig['ikon'] }} {{ $konfig['label_peran'] }}
    </span>
</div>

@guest
<!-- Banner Ajakan Login untuk Pengunjung Umum -->
<div class="mb-6 p-5 sm:p-6 rounded-3xl bg-gradient-to-r from-emerald-800 to-emerald-700 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
    <div>
        <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-[11px] font-bold uppercase">Ekosistem Pangan Nasional</span>
        <h3 class="font-extrabold text-base sm:text-lg mt-1.5">Kelola Panen, Ternak, Tangkapan & Keuangan Usaha Anda</h3>
        <p class="text-xs text-emerald-100 mt-0.5">Masuk atau daftar akun gratis untuk mencatat produksi, pembukuan laba-rugi, dan konsultasi Pakar AI.</p>
    </div>
    <div class="flex gap-2.5 flex-shrink-0">
        <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-stone-100 text-emerald-900 font-extrabold text-xs transition">
            🔑 Masuk
        </a>
        <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-emerald-950 font-extrabold text-xs transition">
            ✨ Daftar Gratis
        </a>
    </div>
</div>
@endguest

@auth
<!-- Kartu Ringkasan Keuangan Pribadi (Hanya untuk Akun yang Sudah Login) -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
        <div class="flex items-center justify-between">
            <span class="text-2xl">💰</span>
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold">Laba Bersih</span>
        </div>
        <p class="fluid-metric font-extrabold mt-2 break-words {{ $totalLaba >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
            Rp {{ number_format($totalLaba, 0, ',', '.') }}
        </p>
        <p class="text-xs text-stone-500 mt-1">Akumulasi pemasukan dikurangi pengeluaran usaha Anda</p>
    </div>

    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
        <div class="flex items-center justify-between">
            <span class="text-2xl">📉</span>
            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[11px] font-bold">Total Biaya</span>
        </div>
        <p class="fluid-metric font-extrabold text-stone-800 mt-2 break-words">
            Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
        </p>
        <p class="text-xs text-stone-500 mt-1">Modal bibit, pakan, solar, pupuk & operasional tercatat</p>
    </div>
</div>
@endauth

<!-- Akses Cepat Fitur Utama -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 mb-6">
    <a href="{{ route('harga') }}" class="bg-white p-4 rounded-3xl border border-stone-100 shadow-xs hover:border-emerald-300 transition flex flex-col justify-between">
        <span class="text-2xl mb-2">📈</span>
        <div>
            <h3 class="font-extrabold text-xs sm:text-sm text-stone-800">Pantauan Harga</h3>
            <p class="text-[11px] text-stone-500 mt-0.5">Cek tren harga pasar terkini</p>
        </div>
    </a>

    <a href="{{ route('jual') }}" class="bg-white p-4 rounded-3xl border border-stone-100 shadow-xs hover:border-emerald-300 transition flex flex-col justify-between">
        <span class="text-2xl mb-2">🏪</span>
        <div>
            <h3 class="font-extrabold text-xs sm:text-sm text-stone-800">{{ $konfig['aksi_jual'] }}</h3>
            <p class="text-[11px] text-stone-500 mt-0.5">{{ $totalListingPasar }} stok aktif di pasar</p>
        </div>
    </a>

    <a href="{{ route('produksi') }}" class="bg-white p-4 rounded-3xl border border-stone-100 shadow-xs hover:border-emerald-300 transition flex flex-col justify-between">
        <span class="text-2xl mb-2">📦</span>
        <div>
            <h3 class="font-extrabold text-xs sm:text-sm text-stone-800">{{ $konfig['aksi_utama'] }}</h3>
            <p class="text-[11px] text-stone-500 mt-0.5">Catat hasil panen & stok</p>
        </div>
    </a>

    <a href="{{ route('panduan') }}" class="bg-white p-4 rounded-3xl border border-stone-100 shadow-xs hover:border-emerald-300 transition flex flex-col justify-between">
        <span class="text-2xl mb-2">🤖</span>
        <div>
            <h3 class="font-extrabold text-xs sm:text-sm text-stone-800">Tanya Pakar AI</h3>
            <p class="text-[11px] text-stone-500 mt-0.5">Prediksi risiko & panduan</p>
        </div>
    </a>
</div>

<!-- Info Peringatan Sektor -->
<div class="bg-amber-50/80 border border-amber-200 p-4 sm:p-5 rounded-3xl mb-6 flex items-start gap-3.5">
    <span class="text-2xl">{{ $konfig['info_ikon'] }}</span>
    <div>
        <h3 class="font-extrabold text-sm text-amber-950">{{ $konfig['info_judul'] }}</h3>
        <p class="text-xs text-amber-800 mt-1 leading-relaxed">{{ $konfig['info_desc'] }}</p>
    </div>
</div>

<!-- Sorotan Harga Komoditas Hari Ini -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-extrabold text-sm sm:text-base text-stone-800">🌾 Sorotan Harga Komoditas Nasional</h3>
        <a href="{{ route('harga') }}" class="text-xs font-bold text-emerald-700 hover:underline">Lihat Semua →</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        @foreach($komoditasUnggulan as $k)
        <div class="p-4 rounded-2xl bg-stone-50/70 border border-stone-200/70">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xl">{{ $k->ikon }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $k->persentase_perubahan >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                    {{ $k->persentase_perubahan >= 0 ? '+' : '' }}{{ $k->persentase_perubahan }}%
                </span>
            </div>
            <p class="font-bold text-sm text-stone-800">{{ $k->nama }}</p>
            <p class="font-extrabold text-base text-emerald-700 mt-1">Rp {{ number_format($k->harga_pasar, 0, ',', '.') }}<span class="text-[11px] font-normal text-stone-500">/kg</span></p>
        </div>
        @endforeach
    </div>
</div>

@endsection