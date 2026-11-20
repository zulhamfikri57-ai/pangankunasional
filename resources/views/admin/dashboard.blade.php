@extends('layouts.admin')
@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-stone-800">Dashboard Monitoring Pangan Nasional</h2>
    <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Rekapitulasi stok produksi, distribusi pasar, dan sebaran pelaku pangan</p>
</div>

<!-- 4 Kartu Metrik Utama -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-3xl border border-stone-200/70 shadow-xs">
        <p class="text-xs font-bold text-stone-500">Total Akun Terdaftar</p>
        <p class="text-3xl font-extrabold text-emerald-800 mt-1">{{ $totalUser }}</p>
        <p class="text-[11px] text-stone-400 mt-1">Produsen & Konsumen aktif</p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-stone-200/70 shadow-xs">
        <p class="text-xs font-bold text-stone-500">Akumulasi Produksi</p>
        <p class="text-3xl font-extrabold text-stone-800 mt-1">{{ number_format($totalProduksiKg, 0, ',', '.') }} <span class="text-sm font-bold">kg</span></p>
        <p class="text-[11px] text-stone-400 mt-1">Darat, Ternak & Laut</p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-stone-200/70 shadow-xs">
        <p class="text-xs font-bold text-stone-500">Listing Pasar Aktif</p>
        <p class="text-3xl font-extrabold text-orange-600 mt-1">{{ $totalListingAktif }}</p>
        <p class="text-[11px] text-stone-400 mt-1">Penawaran langsung produsen</p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-stone-200/70 shadow-xs">
        <p class="text-xs font-bold text-stone-500">Nilai Transaksi Tercatat</p>
        <p class="text-2xl font-extrabold text-emerald-700 mt-1 break-words">Rp {{ number_format($totalPerputaranUang, 0, ',', '.') }}</p>
        <p class="text-[11px] text-stone-400 mt-1">Total pemasukan seluruh akun</p>
    </div>
</div>

<!-- Sebaran Pelaku Pangan -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/70 shadow-xs mb-6">
    <h3 class="font-extrabold text-sm sm:text-base text-stone-800 mb-4">Sebaran Sektor Pengguna</h3>
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="p-3.5 rounded-2xl bg-emerald-50 text-center">
            <span class="text-xl">🚜</span>
            <p class="text-xl font-extrabold text-emerald-900 mt-1">{{ $rekapPeran['petani'] }}</p>
            <p class="text-xs font-bold text-emerald-700">Petani</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-amber-50 text-center">
            <span class="text-xl">🐾</span>
            <p class="text-xl font-extrabold text-amber-900 mt-1">{{ $rekapPeran['peternak'] }}</p>
            <p class="text-xs font-bold text-amber-700">Peternak</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-cyan-50 text-center">
            <span class="text-xl">🐟</span>
            <p class="text-xl font-extrabold text-cyan-900 mt-1">{{ $rekapPeran['nelayan'] }}</p>
            <p class="text-xs font-bold text-cyan-700">Nelayan</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-purple-50 text-center">
            <span class="text-xl">🏪</span>
            <p class="text-xl font-extrabold text-purple-900 mt-1">{{ $rekapPeran['pelaku_pangan'] }}</p>
            <p class="text-xs font-bold text-purple-700">Pelaku Pangan</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-stone-100 text-center col-span-2 sm:col-span-1">
            <span class="text-xl">🛒</span>
            <p class="text-xl font-extrabold text-stone-800 mt-1">{{ $rekapPeran['konsumen'] }}</p>
            <p class="text-xs font-bold text-stone-600">Konsumen</p>
        </div>
    </div>
</div>

<!-- Peringatan Surplus Nasional & Log Produksi Terbaru -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/70 shadow-xs">
        <h3 class="font-extrabold text-sm sm:text-base text-stone-800 mb-3">⚠️ Radar Surplus & Risiko Pemborosan Pangan</h3>
        <div class="space-y-3">
            @forelse($peringatanNasional as $p)
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $p['ikon'] }}</span>
                    <div>
                        <p class="font-bold text-sm text-rose-950">{{ $p['nama'] }} ({{ ucfirst($p['sektor']) }})</p>
                        <p class="text-xs text-rose-700">Total stok nasional: <strong>{{ number_format($p['stok'], 0, ',', '.') }} kg</strong> • Tren harga: {{ $p['perubahan'] }}%</p>
                    </div>
                </div>
                <a href="{{ route('admin.komoditas') }}" class="text-xs font-bold text-rose-800 underline whitespace-nowrap">Atur Harga</a>
            </div>
            @empty
            <p class="text-xs text-stone-500 py-4 text-center">Seluruh stok komoditas nasional terpantau seimbang.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/70 shadow-xs">
        <h3 class="font-extrabold text-sm sm:text-base text-stone-800 mb-3">📦 Aktivitas Pencatatan Produksi Terbaru</h3>
        <div class="divide-y divide-stone-100">
            @forelse($produksiTerbaru as $prod)
            <div class="py-2.5 flex items-center justify-between gap-2 text-xs">
                <div>
                    <p class="font-bold text-stone-800">{{ $prod->komoditas->nama ?? '-' }} ({{ number_format($prod->jumlah, 0, ',', '.') }} {{ $prod->satuan }})</p>
                    <p class="text-stone-400">Oleh: {{ $prod->user->name ?? 'Pengguna' }} • {{ $prod->tanggal_panen }}</p>
                </div>
                <span class="px-2 py-0.5 rounded bg-stone-100 font-bold uppercase text-[10px]">{{ $prod->sektor }}</span>
            </div>
            @empty
            <p class="text-xs text-stone-500 py-4 text-center">Belum ada aktivitas produksi.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection