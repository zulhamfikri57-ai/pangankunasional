@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">📈 Harga & Ketersediaan Komoditas Real-Time</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Perpaduan acuan Panel Harga Pangan dengan transaksi & stok riil mitra PanganKu Nasional</p>
    </div>

    @auth
    <form action="{{ route('harga.sync') }}" method="POST">
        @csrf
        <button type="submit" class="px-4 py-2.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition cursor-pointer">
            🔄 Sinkronkan Harga & Stok Sekarang
        </button>
    </form>
    @endauth
</div>

<!-- TAB FILTER KATEGORI -->
<div class="flex flex-wrap items-center gap-2 mb-6">
    @foreach(['Semua', 'Bahan Pokok', 'Hortikultura', 'Protein Hewani', 'Perikanan'] as $kat)
        <a href="{{ route('harga', ['kategori' => $kat]) }}"
            class="px-4 py-2 rounded-2xl text-xs font-bold transition {{ $kategori === $kat ? 'bg-stone-800 text-white' : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-100' }}">
            {{ $kat }}
        </a>
    @endforeach
</div>

<!-- GRID KARTU HARGA & KETERSEDIAAN REAL-TIME -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($komoditas as $item)
    @php
        $badgeStok = match ($item->status_ketersediaan) {
            'surplus' => ['bg' => 'bg-emerald-100 text-emerald-800', 'label' => '🟢 Pasokan Melimpah'],
            'terbatas' => ['bg' => 'bg-amber-100 text-amber-900', 'label' => '🟡 Stok Terbatas'],
            default => ['bg' => 'bg-rose-100 text-rose-800', 'label' => '🔴 Stok Menipis'],
        };
    @endphp
    <div class="bg-white p-5 rounded-3xl border border-stone-200/80 shadow-xs flex flex-col justify-between gap-4">
        <div>
            <!-- Header Komoditas & Status Ketersediaan -->
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">{{ $item->ikon ?? '🌾' }}</span>
                    <div>
                        <h3 class="font-extrabold text-base text-stone-800 leading-tight">{{ $item->nama }}</h3>
                        <span class="text-[11px] font-semibold text-stone-400">{{ $item->kategori_harga ?? ucfirst($item->sektor) }}</span>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $badgeStok['bg'] }}">
                    {{ $badgeStok['label'] }}
                </span>
            </div>

            <!-- Harga Utama & Persentase Perubahan -->
            <div class="mt-4 flex items-baseline justify-between">
                <p class="font-extrabold text-2xl text-stone-900">
                    Rp {{ number_format($item->harga_sekarang, 0, ',', '.') }}
                    <span class="text-xs font-normal text-stone-500">/ {{ $item->satuan ?? 'kg' }}</span>
                </p>

                @if($item->persentase_perubahan > 0)
                    <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-extrabold">
                        ▲ +{{ $item->persentase_perubahan }}%
                    </span>
                @elseif($item->persentase_perubahan < 0)
                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-extrabold">
                        ▼ {{ $item->persentase_perubahan }}%
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-lg bg-stone-100 text-stone-600 text-xs font-bold">
                        Stabil 0%
                    </span>
                @endif
            </div>

            <!-- Rincian Ketersediaan Stok Real-Time -->
            <div class="mt-4 grid grid-cols-2 gap-2.5 p-3 rounded-2xl bg-stone-50 border border-stone-200/60">
                <div>
                    <span class="text-[10px] font-bold text-stone-500 uppercase block">Siap Angkut (Ready)</span>
                    <strong class="text-sm font-extrabold text-emerald-700">
                        {{ number_format($item->stok_ready_kg ?? 0, 0, ',', '.') }} {{ $item->satuan ?? 'kg' }}
                    </strong>
                </div>
                <div class="border-l border-stone-200 pl-2.5">
                    <span class="text-[10px] font-bold text-stone-500 uppercase block">Akan Panen / PO</span>
                    <strong class="text-sm font-extrabold text-amber-700">
                        {{ number_format($item->stok_preorder_kg ?? 0, 0, ',', '.') }} {{ $item->satuan ?? 'kg' }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- Footer Sumber Harga & Waktu Sinkronisasi -->
        <div class="pt-3 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span class="truncate max-w-[68%]" title="{{ $item->sumber_harga }}">
                📡 {{ $item->sumber_harga ?? 'Acuan Pasar & Mitra' }}
            </span>
            <a href="{{ route('jual') }}" class="font-bold text-emerald-700 hover:underline shrink-0">
                Lihat Pasar →
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white p-8 rounded-3xl border border-stone-100 text-center">
        <p class="text-sm font-bold text-stone-600">Belum ada data komoditas pada kategori ini.</p>
    </div>
    @endforelse
</div>

@endsection