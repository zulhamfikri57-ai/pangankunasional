@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">🔔 Pantauan Harga & Alert Pribadi</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Atur target batas harga komoditas agar Anda tidak melewatkan momen jual atau beli terbaik</p>
    </div>
    <a href="{{ route('harga') }}" class="px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold w-fit">
        📈 Lihat Semua Harga Pasar →
    </a>
</div>

<!-- FORM TAMBAH ALERT HARGA BARU -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">➕ Buat Pengingat (Alert) Target Harga</h3>

    <form action="{{ route('pantauan.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-3.5 items-end">
        @csrf
        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Komoditas</label>
            <select name="komoditas_id" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-stone-800 text-sm font-semibold">
                @foreach($semuaKomoditas as $k)
                    <option value="{{ $k->id }}">{{ $k->ikon }} {{ $k->nama }} (Rp {{ number_format($k->harga_pasar, 0, ',', '.') }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Kondisi Peringatan</label>
            <select name="kondisi" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-stone-800 text-sm font-semibold">
                <option value="naik_di_atas">📈 Harga Naik Di Atas</option>
                <option value="turun_di_bawah">📉 Harga Turun Di Bawah</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Target Harga / kg (Rp)</label>
            <input type="number" name="target_harga" placeholder="Contoh: 35000" required min="100"
                class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-stone-800 text-sm">
        </div>

        <button type="submit" class="w-full py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Simpan Alert
        </button>
    </form>
</div>

<!-- DAFTAR ALERT AKTIF MILIK PENGGUNA -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">📋 Daftar Alert Harga Anda ({{ $alerts->count() }})</h3>

    <div class="divide-y divide-stone-100">
        @forelse($alerts as $a)
        @php
            $hargaSekarang = $a->komoditas->harga_pasar ?? 0;
            $terpicu = ($a->kondisi === 'naik_di_atas' && $hargaSekarang >= $a->target_harga)
                    || ($a->kondisi === 'turun_di_bawah' && $hargaSekarang <= $a->target_harga);
        @endphp
        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-2xl">{{ $a->komoditas->ikon ?? '🌾' }}</span>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-extrabold text-sm text-stone-800">{{ $a->komoditas->nama ?? 'Komoditas' }}</p>
                        @if($terpicu)
                            <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-extrabold uppercase">
                                🚨 Target Tercapai!
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">
                                Memantau
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-stone-600 mt-0.5">
                        Alert jika <strong>{{ $a->kondisi === 'naik_di_atas' ? 'Naik di atas' : 'Turun di bawah' }}</strong>
                        <span class="font-bold text-emerald-800">Rp {{ number_format($a->target_harga, 0, ',', '.') }}/kg</span>
                    </p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <p class="text-[11px] text-stone-500">Harga Pasar Saat Ini</p>
                <p class="font-extrabold text-sm text-stone-800">Rp {{ number_format($hargaSekarang, 0, ',', '.') }}/kg</p>
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 py-4 text-center">Belum ada alert harga yang Anda pasang. Buat alert pertama Anda di atas.</p>
        @endforelse
    </div>
</div>

<!-- KOMODITAS UNGGULAN / FAVORIT -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">⭐ Komoditas Pantauan Utama</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        @foreach($favorit as $f)
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xl">{{ $f->ikon }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $f->persentase_perubahan >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $f->persentase_perubahan >= 0 ? '+' : '' }}{{ $f->persentase_perubahan }}%
                </span>
            </div>
            <p class="font-bold text-sm text-stone-800">{{ $f->nama }}</p>
            <p class="font-extrabold text-base text-emerald-700 mt-1">Rp {{ number_format($f->harga_pasar, 0, ',', '.') }}<span class="text-xs font-normal text-stone-500">/kg</span></p>
        </div>
        @endforeach
    </div>
</div>

@endsection