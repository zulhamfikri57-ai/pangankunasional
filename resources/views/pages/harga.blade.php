@extends('layouts.app')
@section('content')

<div class="bg-emerald-800 text-white rounded-3xl p-5 sm:p-7 mb-5 shadow-sm">
    <h2 class="fluid-title font-extrabold">Harga Komoditas Hari Ini</h2>
    <p class="text-xs sm:text-sm text-emerald-100 mt-1">Pantau perbandingan harga pasar dan harga tingkat produsen/petani</p>
</div>

<!-- Filter Kategori Geser Horizontal (Swipeable di Mobile) -->
<div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 mb-5">
    @foreach(['Semua', 'Pangan Pokok', 'Sayuran', 'Bumbu', 'Peternakan', 'Perikanan'] as $kat)
    <a href="{{ route('harga', ['kategori' => $kat]) }}"
       class="flex-shrink-0 px-4 py-2 rounded-full text-xs sm:text-sm font-bold border transition {{ $kategori == $kat ? 'bg-stone-800 text-white border-stone-800 shadow-xs' : 'bg-white text-stone-700 border-stone-200 hover:bg-stone-50' }}">
        {{ $kat }}
    </a>
    @endforeach
</div>

<!-- Grid Kartu Harga Komoditas -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
    @foreach($komoditas as $item)
    <div class="bg-white p-4 sm:p-6 rounded-3xl border border-stone-100 shadow-sm">
        <div class="flex items-center justify-between gap-2 mb-4">
            <div class="flex items-center gap-3 min-w-0">
                <span class="text-2xl sm:text-3xl flex-shrink-0">{{ $item->ikon }}</span>
                <div class="min-w-0">
                    <h3 class="font-bold text-base sm:text-lg text-stone-800 truncate">{{ $item->nama }}</h3>
                    <span class="text-xs text-stone-400 block truncate">{{ $item->satuan }} • {{ $item->kategori_harga }}</span>
                </div>
            </div>
            <span class="flex-shrink-0 px-2.5 sm:px-3 py-1 rounded-full text-xs font-bold {{ $item->persentase_perubahan >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                {{ $item->persentase_perubahan >= 0 ? '↗ +' : '↘ ' }}{{ $item->persentase_perubahan }}%
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-stone-100">
            <div class="min-w-0">
                <p class="text-[11px] sm:text-xs text-stone-500">Harga Pasar</p>
                <p class="text-base sm:text-xl font-extrabold text-stone-800 break-words mt-0.5">
                    Rp {{ number_format($item->harga_pasar, 0, ',', '.') }}<span class="text-xs font-normal text-stone-500">/kg</span>
                </p>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] sm:text-xs text-stone-500">Harga Produsen</p>
                <p class="text-base sm:text-xl font-extrabold text-emerald-700 break-words mt-0.5">
                    Rp {{ number_format($item->harga_petani, 0, ',', '.') }}<span class="text-xs font-normal text-emerald-600">/kg</span>
                </p>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection