@extends('layouts.app')
@section('content')
<h2 class="text-2xl font-extrabold text-stone-800 mb-6">Profil & Status Akun</h2>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Kartu Info Pengguna -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-100 h-fit">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-extrabold text-2xl">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h3 class="font-extrabold text-lg text-stone-800">{{ $user->name }}</h3>
                <p class="text-xs text-stone-500">{{ $user->email }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-100 space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-xs text-stone-500">Status Akun</span>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                    {{ $user->peran_sektor == 'konsumen' ? 'bg-stone-200 text-stone-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ str_replace('_', ' ', $user->peran_sektor) }}
                </span>
            </div>
            @if($user->komoditas_utama)
            <div class="flex justify-between items-center pt-2 border-t border-stone-200/60">
                <span class="text-xs text-stone-500">Komoditas / Usaha</span>
                <span class="text-xs font-bold text-stone-800">{{ $user->komoditas_utama }}</span>
            </div>
            @endif
            @if($user->lokasi)
            <div class="flex justify-between items-center pt-2 border-t border-stone-200/60">
                <span class="text-xs text-stone-500">Wilayah</span>
                <span class="text-xs font-bold text-stone-800">{{ $user->lokasi }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Form Upgrade Peran -->
    <div class="lg:col-span-2 bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-stone-100">
        <div class="mb-6">
            <h3 class="font-extrabold text-lg text-stone-800">Upgrade / Ubah Peran Akun</h3>
            <p class="text-sm text-stone-500 mt-1">Tingkatkan akun konsumen Anda menjadi produsen atau pelaku usaha pangan untuk membuka fitur pencatatan produksi dan penjualan panen.</p>
        </div>

        <form action="{{ route('profil.upgrade') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-2.5">Pilih Jenis Akun</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="peran_sektor" value="konsumen" class="peer sr-only" {{ $user->peran_sektor == 'konsumen' ? 'checked' : '' }}>
                        <div class="p-4 rounded-2xl border-2 border-stone-200 peer-checked:border-stone-700 peer-checked:bg-stone-50 transition">
                            <div class="font-bold text-sm text-stone-800">🛒 Akun Konsumen (Biasa)</div>
                            <p class="text-xs text-stone-500 mt-1">Pantau harga pasar & pasang alert komoditas.</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="peran_sektor" value="petani" class="peer sr-only" {{ $user->peran_sektor == 'petani' ? 'checked' : '' }}>
                        <div class="p-4 rounded-2xl border-2 border-stone-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50/60 transition">
                            <div class="font-bold text-sm text-stone-800">🚜 Petani (Tanaman)</div>
                            <p class="text-xs text-stone-500 mt-1">Catat panen lahan, biaya pupuk, & jual hasil tani.</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="peran_sektor" value="peternak" class="peer sr-only" {{ $user->peran_sektor == 'peternak' ? 'checked' : '' }}>
                        <div class="p-4 rounded-2xl border-2 border-stone-200 peer-checked:border-amber-600 peer-checked:bg-amber-50/60 transition">
                            <div class="font-bold text-sm text-stone-800">🐾 Peternak</div>
                            <p class="text-xs text-stone-500 mt-1">Kelola produksi daging, telur, susu, & pakan.</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="peran_sektor" value="nelayan" class="peer sr-only" {{ $user->peran_sektor == 'nelayan' ? 'checked' : '' }}>
                        <div class="p-4 rounded-2xl border-2 border-stone-200 peer-checked:border-cyan-600 peer-checked:bg-cyan-50/60 transition">
                            <div class="font-bold text-sm text-stone-800">🐟 Nelayan & Pembudidaya</div>
                            <p class="text-xs text-stone-500 mt-1">Catat hasil tangkapan laut/tambak & distribusi.</p>
                        </div>
                    </label>

                    <label class="cursor-pointer sm:col-span-2">
                        <input type="radio" name="peran_sektor" value="pelaku_pangan" class="peer sr-only" {{ $user->peran_sektor == 'pelaku_pangan' ? 'checked' : '' }}>
                        <div class="p-4 rounded-2xl border-2 border-stone-200 peer-checked:border-purple-600 peer-checked:bg-purple-50/60 transition">
                            <div class="font-bold text-sm text-stone-800">🏪 Pelaku Pangan Lainnya (Pengepul / UMKM Olahan / Distributor)</div>
                            <p class="text-xs text-stone-500 mt-1">Kelola stok pasokan, pengolahan hasil pangan, dan distribusi pasar.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1.5">Komoditas / Bidang Utama</label>
                    <input type="text" name="komoditas_utama" value="{{ old('komoditas_utama', $user->komoditas_utama) }}" placeholder="Contoh: Cabai Merah, Ayam, Tongkol, Beras"
                        class="w-full px-4 py-3 rounded-xl border border-stone-200 text-sm focus:outline-none focus:border-emerald-600">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1.5">Daerah / Kabupaten</label>
                    <input type="text" name="lokasi" value="{{ old('lokasi', $user->lokasi) }}" placeholder="Contoh: Pesisir Selatan, Sumatera Barat"
                        class="w-full px-4 py-3 rounded-xl border border-stone-200 text-sm focus:outline-none focus:border-emerald-600">
                </div>
            </div>

            <button type="submit" class="px-6 py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm shadow-sm transition cursor-pointer">
                Simpan Perubahan Status Akun
            </button>
        </form>
    </div>
</div>
@endsection