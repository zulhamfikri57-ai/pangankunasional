@extends('layouts.app')
@section('content')
<div class="hero-gradient text-white rounded-3xl p-7 mb-6">
    <h2 class="text-2xl font-extrabold">Pantauan Harga & Alert</h2>
    <p class="text-sm text-white/90 mt-1">Pantau harga komoditas favorit dan atur notifikasi batas harga</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <h3 class="font-bold text-lg text-stone-800 mb-3">Komoditas Favorit</h3>
        <div class="space-y-3">
            @foreach($favorit as $fav)
            <div class="bg-white p-4 rounded-2xl border border-stone-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $fav->ikon }}</span>
                    <div>
                        <p class="font-bold text-stone-800">{{ $fav->nama }}</p>
                        <p class="text-xs text-stone-500">{{ $fav->kategori_harga }}</p>
                    </div>
                </div>
                <p class="font-extrabold text-stone-800">Rp {{ number_format($fav->harga_pasar, 0, ',', '.') }} / kg</p>
            </div>
            @endforeach
        </div>
    </div>

    <div>
        <h3 class="font-bold text-lg text-stone-800 mb-3">Alert Harga Aktif</h3>
        <div class="space-y-3 mb-4">
            @foreach($alerts as $a)
            <div class="bg-white p-4 rounded-2xl border border-stone-100 flex items-center justify-between">
                <div>
                    <p class="font-bold text-stone-800">Harga {{ $a->komoditas->nama }}</p>
                    <p class="text-xs text-stone-500">{{ $a->kondisi == 'naik_di_atas' ? 'Naik di atas' : 'Turun di bawah' }} Rp {{ number_format($a->target_harga, 0, ',', '.') }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Aktif</span>
            </div>
            @endforeach
        </div>

        <form action="{{ route('pantauan.store') }}" method="POST" class="bg-white p-5 rounded-2xl border border-stone-100 space-y-3">
            @csrf
            <h4 class="font-bold text-sm text-stone-800">+ Tambah Alert Baru</h4>
            <select name="komoditas_id" class="w-full px-3 py-2.5 rounded-xl border border-stone-300 text-sm">
                @foreach($semuaKomoditas as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                @endforeach
            </select>
            <div class="grid grid-cols-2 gap-2">
                <select name="kondisi" class="px-3 py-2.5 rounded-xl border border-stone-300 text-sm">
                    <option value="naik_di_atas">Naik di atas</option>
                    <option value="turun_di_bawah">Turun di bawah</option>
                </select>
                <input type="number" name="target_harga" placeholder="Target Rp" required class="px-3 py-2.5 rounded-xl border border-stone-300 text-sm">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-700 text-white font-bold text-sm">Simpan Alert</button>
        </form>
    </div>
</div>
@endsection