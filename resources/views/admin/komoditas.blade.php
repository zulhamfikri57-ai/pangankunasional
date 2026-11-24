@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">📈 Manajemen Harga & Komoditas Nasional</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Perubahan harga di sini akan langsung memperbarui grafik harga, SPK distribusi, dan analisis risiko seluruh pengguna</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            🏛️ Dashboard
        </a>
        <a href="{{ route('admin.pengguna') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            👥 Pengguna
        </a>
        <a href="{{ route('admin.panduan') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            📖 Panduan
        </a>
    </div>
</div>

<!-- Form Tambah Komoditas Baru (Grid Responsif Rapi) -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/80 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">+ Tambah Komoditas Baru</h3>
    <form action="{{ route('admin.komoditas.store') }}" method="POST" class="space-y-3">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Nama Komoditas</label>
                <input type="text" name="nama" placeholder="Contoh: Cabai Merah" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Sektor Pangan</label>
                <select name="sektor" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs sm:text-sm font-semibold">
                    <option value="darat">🌾 Darat (Tanaman)</option>
                    <option value="ternak">🐄 Ternak / Daging</option>
                    <option value="laut">🐟 Laut & Perikanan</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Kategori Harga</label>
                <input type="text" name="kategori_harga" placeholder="Contoh: Sayuran / Bumbu" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Ikon Emoji</label>
                <input type="text" name="ikon" placeholder="🌾" value="🌾"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-center text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Harga Pasar (Rp/kg)</label>
                <input type="text" name="harga_pasar" data-rupiah placeholder="Contoh: 32.000" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Harga Produsen (Rp/kg)</label>
                <input type="text" name="harga_petani" data-rupiah placeholder="Contoh: 26.000" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm font-bold text-emerald-700">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tren Perubahan (%)</label>
                <input type="number" step="0.1" name="persentase_perubahan" placeholder="0" value="0" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            </div>

            <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs sm:text-sm transition cursor-pointer">
                + Tambah Komoditas
            </button>
        </div>
    </form>
</div>

<!-- Tabel Update Harga Cepat -->
<div class="bg-white rounded-3xl border border-stone-200/80 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-stone-100 flex items-center justify-between">
        <h3 class="font-extrabold text-base text-stone-800">Daftar Komoditas Nasional ({{ $komoditas->count() }})</h3>
        <span class="text-[11px] font-semibold text-stone-400">Format nominal otomatis bertitik ribuan</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase text-[11px] font-extrabold">
                <tr>
                    <th class="p-4">Komoditas</th>
                    <th class="p-4">Sektor</th>
                    <th class="p-4">Harga Pasar (Rp/kg)</th>
                    <th class="p-4">Harga Produsen (Rp/kg)</th>
                    <th class="p-4">Tren (%)</th>
                    <th class="p-4">Favorit</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($komoditas as $item)
                <tr class="hover:bg-stone-50/70 transition">
                    <td class="p-4 font-bold text-stone-800 whitespace-nowrap">{{ $item->ikon }} {{ $item->nama }}</td>
                    <td class="p-4 uppercase text-xs font-extrabold text-stone-500">{{ $item->sektor }}</td>
                    <td class="p-4">
                        <input type="text" form="form-komoditas-{{ $item->id }}" name="harga_pasar" data-rupiah
                            value="{{ (int) $item->harga_pasar }}"
                            class="w-28 px-3 py-1.5 rounded-xl border border-stone-300 font-bold text-xs">
                    </td>
                    <td class="p-4">
                        <input type="text" form="form-komoditas-{{ $item->id }}" name="harga_petani" data-rupiah
                            value="{{ (int) $item->harga_petani }}"
                            class="w-28 px-3 py-1.5 rounded-xl border border-stone-300 font-bold text-emerald-700 text-xs">
                    </td>
                    <td class="p-4">
                        <input type="number" step="0.1" form="form-komoditas-{{ $item->id }}" name="persentase_perubahan"
                            value="{{ $item->persentase_perubahan }}"
                            class="w-20 px-2.5 py-1.5 rounded-xl border border-stone-300 text-xs">
                    </td>
                    <td class="p-4">
                        <label class="inline-flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" form="form-komoditas-{{ $item->id }}" name="is_favorit"
                                class="rounded text-emerald-700" {{ $item->is_favorit ? 'checked' : '' }}> Utama
                        </label>
                    </td>
                    <td class="p-4 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            <form id="form-komoditas-{{ $item->id }}" action="{{ route('admin.komoditas.update', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition cursor-pointer">
                                    Simpan
                                </button>
                            </form>
                            <form action="{{ route('admin.komoditas.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus komoditas {{ $item->nama }}?')">
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