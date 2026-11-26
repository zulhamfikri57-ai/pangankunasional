@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">🧭 Manajemen Wilayah Distribusi SPK (SAW)</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Atur parameter defisit pasar, faktor pengali harga, dan biaya logistik per kg untuk perhitungan rekomendasi distribusi</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            🏛️ Dashboard
        </a>
        <a href="{{ route('admin.komoditas') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            📈 Komoditas
        </a>
        <a href="{{ route('spk.distribusi') }}" class="px-3.5 py-2 rounded-2xl bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-xs font-bold text-emerald-800 transition">
            🧮 Uji Hasil SPK ↗
        </a>
    </div>
</div>

<!-- Form Tambah Wilayah Baru -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/80 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">+ Tambah Alternatif Wilayah Tujuan</h3>
    <form action="{{ route('admin.wilayah.store') }}" method="POST" class="space-y-3">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Nama Kota / Kabupaten / Pasar Tujuan</label>
                <input type="text" name="nama_wilayah" placeholder="Contoh: Kota Solok (Pasar Raya Solok)" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Provinsi</label>
                <input type="text" name="provinsi" value="Sumatera Barat" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Estimasi Jarak (km)</label>
                <input type="number" step="0.1" name="jarak_km" placeholder="Contoh: 65" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tingkat Defisit / Permintaan (1–100%)</label>
                <input type="number" min="1" max="100" name="tingkat_defisit" placeholder="Contoh: 78" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Biaya Logistik (Rp/kg)</label>
                <input type="text" name="biaya_logistik_kg" data-rupiah placeholder="Contoh: 1.800" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Faktor Harga Tujuan (misal 1.12 = +12%)</label>
                <input type="number" step="0.01" min="0.5" max="3" name="faktor_harga" value="1.10" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            </div>
            <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs sm:text-sm transition cursor-pointer">
                + Tambah Wilayah SPK
            </button>
        </div>
    </form>
</div>

<!-- Tabel Parameter Wilayah SPK -->
<div class="bg-white rounded-3xl border border-stone-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase text-[11px] font-extrabold">
                <tr>
                    <th class="p-4">Wilayah Tujuan</th>
                    <th class="p-4">Jarak (km)</th>
                    <th class="p-4">Defisit (%)</th>
                    <th class="p-4">Logistik (Rp/kg)</th>
                    <th class="p-4">Faktor Harga</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($wilayahList as $w)
                <tr class="hover:bg-stone-50/70 transition">
                    <td class="p-4">
                        <p class="font-bold text-stone-800">{{ $w->nama_wilayah }}</p>
                        <p class="text-[11px] text-stone-400">{{ $w->provinsi }}</p>
                    </td>
                    <td class="p-4">
                        <input type="number" step="0.1" form="form-wilayah-{{ $w->id }}" name="jarak_km"
                            value="{{ $w->jarak_km }}" class="w-20 px-2.5 py-1.5 rounded-xl border border-stone-300 text-xs">
                    </td>
                    <td class="p-4">
                        <input type="number" min="1" max="100" form="form-wilayah-{{ $w->id }}" name="tingkat_defisit"
                            value="{{ $w->tingkat_defisit }}" class="w-20 px-2.5 py-1.5 rounded-xl border border-stone-300 font-bold text-xs">
                    </td>
                    <td class="p-4">
                        <input type="text" form="form-wilayah-{{ $w->id }}" name="biaya_logistik_kg" data-rupiah
                            value="{{ (int) $w->biaya_logistik_kg }}" class="w-24 px-2.5 py-1.5 rounded-xl border border-stone-300 font-bold text-rose-700 text-xs">
                    </td>
                    <td class="p-4">
                        <input type="number" step="0.01" form="form-wilayah-{{ $w->id }}" name="faktor_harga"
                            value="{{ $w->faktor_harga }}" class="w-20 px-2.5 py-1.5 rounded-xl border border-stone-300 text-xs">
                    </td>
                    <td class="p-4">
                        <label class="inline-flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" form="form-wilayah-{{ $w->id }}" name="is_aktif"
                                class="rounded text-emerald-700" {{ $w->is_aktif ? 'checked' : '' }}> Aktif
                        </label>
                    </td>
                    <td class="p-4 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            <form id="form-wilayah-{{ $w->id }}" action="{{ route('admin.wilayah.update', $w->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition cursor-pointer">
                                    Simpan
                                </button>
                            </form>
                            <form action="{{ route('admin.wilayah.destroy', $w->id) }}" method="POST" onsubmit="return confirm('Hapus wilayah {{ $w->nama_wilayah }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition cursor-pointer">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection