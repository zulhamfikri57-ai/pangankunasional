@extends('layouts.admin')
@section('content')

<h2 class="text-2xl font-extrabold text-stone-800 mb-1">Manajemen Harga & Komoditas Nasional</h2>
<p class="text-xs sm:text-sm text-stone-500 mb-6">Perubahan harga di sini akan langsung memperbarui grafik harga dan analisis risiko pada akun seluruh pengguna</p>

<!-- Form Tambah Komoditas Baru -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/70 shadow-xs mb-6">
    <h3 class="font-bold text-sm sm:text-base text-stone-800 mb-4">+ Tambah Komoditas Baru</h3>
    <form action="{{ route('admin.komoditas.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="nama" placeholder="Nama Komoditas" required class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
        <select name="sektor" class="px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs sm:text-sm">
            <option value="darat">Darat (Tanaman)</option>
            <option value="ternak">Ternak/Daging</option>
            <option value="laut">Laut & Ikan</option>
        </select>
        <input type="text" name="kategori_harga" placeholder="Kategori (Sayuran/Bumbu)" required class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
        <input type="number" name="harga_pasar" placeholder="Harga Pasar (Rp)" required class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
        <input type="number" name="harga_petani" placeholder="Harga Produsen (Rp)" required class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
        <div class="flex gap-2">
            <input type="number" step="0.1" name="persentase_perubahan" placeholder="Tren %" value="0" required class="w-24 px-3 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            <input type="text" name="ikon" placeholder="🌾" value="🌾" class="w-14 px-2 py-2.5 rounded-xl border border-stone-300 text-center text-sm">
            <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs cursor-pointer">
                Tambah
            </button>
        </div>
    </form>
</div>

<!-- Tabel Update Harga Cepat -->
<div class="bg-white rounded-3xl border border-stone-200/70 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase text-[11px]">
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
                <tr>
                    <td class="p-4 font-bold text-stone-800 whitespace-nowrap">{{ $item->ikon }} {{ $item->nama }}</td>
                    <td class="p-4 uppercase text-xs font-semibold text-stone-500">{{ $item->sektor }}</td>
                    <td class="p-4" colspan="5">
                        <form action="{{ route('admin.komoditas.update', $item->id) }}" method="POST" class="flex flex-wrap items-center justify-between gap-2">
                            @csrf
                            @method('PUT')
                            <input type="number" name="harga_pasar" value="{{ $item->harga_pasar }}" class="w-28 px-3 py-1.5 rounded-lg border border-stone-300 font-bold text-xs">
                            <input type="number" name="harga_petani" value="{{ $item->harga_petani }}" class="w-28 px-3 py-1.5 rounded-lg border border-stone-300 font-bold text-emerald-700 text-xs">
                            <input type="number" step="0.1" name="persentase_perubahan" value="{{ $item->persentase_perubahan }}" class="w-20 px-2.5 py-1.5 rounded-lg border border-stone-300 text-xs">
                            <label class="inline-flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="is_favorit" {{ $item->is_favorit ? 'checked' : '' }}> Utama
                            </label>
                            <div class="flex items-center gap-1.5 ml-auto">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs cursor-pointer">
                                    Simpan
                                </button>
                        </form>
                                <form action="{{ route('admin.komoditas.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus komoditas {{ $item->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs cursor-pointer">🗑️</button>
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