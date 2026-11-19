@extends('layouts.app')
@section('content')

<h2 class="fluid-title font-extrabold text-stone-800 mb-5">Hasil Produksi</h2>

<!-- Tab 3 Sektor -->
<div class="grid grid-cols-3 gap-2 sm:gap-4 mb-6">
    <a href="{{ route('produksi', ['sektor' => 'darat']) }}"
       class="p-3 sm:p-4 rounded-2xl border text-center font-bold text-xs sm:text-sm transition flex flex-col sm:flex-row items-center justify-center gap-1.5 {{ $sektor == 'darat' ? 'bg-emerald-700 text-white border-emerald-700 shadow' : 'bg-white text-stone-700 border-stone-200 hover:bg-stone-50' }}">
        <span class="text-base sm:text-lg">🚜</span>
        <span>Darat <span class="hidden sm:inline">(Tanaman)</span></span>
    </a>
    <a href="{{ route('produksi', ['sektor' => 'ternak']) }}"
       class="p-3 sm:p-4 rounded-2xl border text-center font-bold text-xs sm:text-sm transition flex flex-col sm:flex-row items-center justify-center gap-1.5 {{ $sektor == 'ternak' ? 'bg-amber-700 text-white border-amber-700 shadow' : 'bg-white text-stone-700 border-stone-200 hover:bg-stone-50' }}">
        <span class="text-base sm:text-lg">🐾</span>
        <span>Ternak<span class="hidden sm:inline">/Daging</span></span>
    </a>
    <a href="{{ route('produksi', ['sektor' => 'laut']) }}"
       class="p-3 sm:p-4 rounded-2xl border text-center font-bold text-xs sm:text-sm transition flex flex-col sm:flex-row items-center justify-center gap-1.5 {{ $sektor == 'laut' ? 'bg-cyan-700 text-white border-cyan-700 shadow' : 'bg-white text-stone-700 border-stone-200 hover:bg-stone-50' }}">
        <span class="text-base sm:text-lg">🐟</span>
        <span>Laut <span class="hidden sm:inline">& Ikan</span></span>
    </a>
</div>

<!-- Banner Ringkasan -->
<div class="rounded-3xl p-5 sm:p-6 text-white mb-6 shadow {{ $sektor == 'darat' ? 'bg-emerald-700' : ($sektor == 'ternak' ? 'bg-amber-700' : 'bg-cyan-700') }}">
    <p class="text-xs sm:text-sm opacity-90">Total tangkapan/panen ({{ ucfirst($sektor) }})</p>
    <p class="fluid-metric font-extrabold mt-1">{{ number_format($totalBerat, 0, ',', '.') }} kg</p>
    <p class="text-xs opacity-85 mt-1">{{ $jumlahCatatan }} catatan tersimpan di database</p>
</div>

<!-- Form Input Produksi -->
<div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-100 mb-6">
    <h3 class="font-bold text-base sm:text-lg text-stone-800 mb-4">Catat Hasil {{ ucfirst($sektor) }}</h3>
    <form action="{{ route('produksi.store') }}" method="POST" class="space-y-5">
        @csrf
        <input type="hidden" name="sektor" value="{{ $sektor }}">

        <div>
            <label class="block text-xs sm:text-sm font-bold text-stone-700 mb-3">Pilih Komoditas</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5 sm:gap-3">
                @foreach ($komoditas as $item)
                    <label class="cursor-pointer">
                        <input type="radio" name="komoditas_id" value="{{ $item->id }}" class="peer sr-only" required {{ $loop->first ? 'checked' : '' }}>
                        <div class="p-3 rounded-2xl border-2 border-stone-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 text-center transition h-full flex flex-col items-center justify-center">
                            <div class="text-xl sm:text-2xl mb-1">{{ $item->ikon }}</div>
                            <div class="text-xs font-bold text-stone-800 leading-tight">{{ $item->nama }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Jumlah</label>
                <input type="number" step="0.1" name="jumlah" placeholder="Contoh: 120" required
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:outline-none focus:border-emerald-600">
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Satuan</label>
                <select name="satuan" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm">
                    <option value="kg">kg</option>
                    <option value="kuintal">kuintal</option>
                    <option value="ton">ton</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal</label>
                <input type="date" name="tanggal_panen" value="{{ date('Y-m-d') }}" required
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Simpan Catatan Produksi
        </button>
    </form>
</div>

<!-- Riwayat Produksi Sektor Ini -->
<div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-100">
    <h3 class="font-bold text-stone-800 mb-3 text-sm sm:text-base">Riwayat Pencatatan ({{ ucfirst($sektor) }})</h3>
    <div class="divide-y divide-stone-100">
        @forelse ($riwayat as $r)
            <div class="py-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xl sm:text-2xl flex-shrink-0">{{ $r->komoditas->ikon ?? '📦' }}</span>
                    <div class="min-w-0">
                        <p class="font-bold text-sm text-stone-800 truncate">{{ $r->komoditas->nama ?? 'Komoditas' }}</p>
                        <p class="text-xs text-stone-400">{{ $r->tanggal_panen }}</p>
                    </div>
                </div>
                <span class="font-extrabold text-sm text-emerald-700 whitespace-nowrap">
                    {{ number_format($r->jumlah, 0, ',', '.') }} {{ $r->satuan }}
                </span>
            </div>
        @empty
            <p class="text-xs text-stone-500 py-4 text-center">Belum ada catatan produksi untuk sektor ini.</p>
        @endforelse
    </div>
</div>

@endsection