@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">{{ $konfig['aksi_jual'] }} & Pasar</h2>
        <p class="text-xs text-stone-500 mt-0.5">{{ $konfig['siklus'] }}</p>
    </div>
    <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit">
        {{ $konfig['ikon'] }} {{ $konfig['label_peran'] }}
    </span>
</div>

@if($user->peran_sektor !== 'konsumen')
<!-- Kartu Ringkasan Produsen / Pelaku Pangan -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4 mb-6">
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-2xl">💵</span>
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold">Estimasi Omzet</span>
        </div>
        <p class="fluid-metric font-extrabold text-emerald-700 mt-2 break-words">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
        <p class="text-xs text-stone-500 mt-1">Potensi pendapatan dari stok aktif Anda</p>
    </div>

    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-2xl">🏪</span>
            <span class="px-2.5 py-0.5 rounded-full bg-orange-50 text-orange-700 text-[11px] font-bold">Etalase Anda</span>
        </div>
        <p class="fluid-metric font-extrabold text-orange-600 mt-2">{{ $listingAktif->count() }}</p>
        <p class="text-xs text-stone-500 mt-1">Listing produk aktif di pasar</p>
    </div>
</div>

<!-- Form Buat Listing Baru -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-sm mb-6">
    <h3 class="font-bold text-base sm:text-lg text-stone-800 mb-4">🛒 Buat Listing Penjualan Baru</h3>
    <form action="{{ route('jual.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-2">Pilih Komoditas</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5">
                @foreach($komoditas as $item)
                <label class="cursor-pointer">
                    <input type="radio" name="komoditas_id" value="{{ $item->id }}" class="peer sr-only"
                           onchange="isiHargaRekomendasi({{ $item->harga_petani }})" {{ $loop->first ? 'checked' : '' }}>
                    <div class="p-3 rounded-2xl border-2 border-stone-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 text-center transition h-full flex flex-col items-center justify-center">
                        <span class="text-xl sm:text-2xl mb-1">{{ $item->ikon }}</span>
                        <span class="text-xs font-bold text-stone-800 leading-tight">{{ $item->nama }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Stok Dijual (kg)</label>
                <input type="number" step="0.1" name="stok_kg" placeholder="Contoh: 50" required
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:outline-none focus:border-emerald-600">
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Harga Jual / kg (Rp)</label>
                <input type="number" name="harga_jual_per_kg" id="inputHargaJual"
                       value="{{ $komoditas->first()->harga_petani ?? 15000 }}" required
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:outline-none focus:border-emerald-600">
            </div>
        </div>

        <button type="submit" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Publikasikan ke Pasar →
        </button>
    </form>
</div>

<!-- Daftar Listing Milik Pengguna -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-sm mb-6">
    <h3 class="font-bold text-stone-800 mb-4 text-sm sm:text-base">Daftar Produk Anda di Pasar</h3>
    <div class="divide-y divide-stone-100">
        @forelse($listings as $l)
        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <span class="text-2xl flex-shrink-0">{{ $l->komoditas->ikon ?? '📦' }}</span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-bold text-sm text-stone-800 truncate">{{ $l->komoditas->nama ?? 'Komoditas' }}</p>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $l->status == 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-700' }}">
                            {{ $l->status }}
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 mt-0.5">
                        Stok: <strong>{{ number_format($l->stok_kg, 0, ',', '.') }} kg</strong> • Total: Rp {{ number_format($l->stok_kg * $l->harga_jual_per_kg, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between sm:justify-end gap-2">
                <p class="font-extrabold text-sm text-emerald-700 mr-2">
                    Rp {{ number_format($l->harga_jual_per_kg, 0, ',', '.') }}/kg
                </p>

                @if($l->status === 'aktif')
                <form action="{{ route('jual.update', $l->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="aksi" value="terjual">
                    <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition cursor-pointer">
                        ✓ Terjual
                    </button>
                </form>
                @endif

                <button type="button"
                        onclick="bukaModalListing({{ $l->id }}, {{ $l->stok_kg }}, {{ $l->harga_jual_per_kg }}, '{{ $l->status }}')"
                        class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition cursor-pointer">
                    ✏️ Edit
                </button>

                <form action="{{ route('jual.destroy', $l->id) }}" method="POST"
                      onsubmit="return confirm('Hapus listing {{ addslashes($l->komoditas->nama ?? 'produk') }} ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition cursor-pointer">
                        🗑️
                    </button>
                </form>
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 py-4 text-center">Anda belum memiliki produk yang dipasarkan. Buat listing pertama Anda di atas.</p>
        @endforelse
    </div>
</div>
@endif

<!-- Etalase Pasar Pangan Nasional (Dapat Dilihat Semua Akun Termasuk Konsumen) -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-bold text-stone-800 text-sm sm:text-base">🌾 Etalase Pasar Langsung Produsen</h3>
            <p class="text-xs text-stone-500">Pasokan segar langsung dari petani, peternak, dan nelayan terdaftar</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        @forelse($pasarListings as $itemPasar)
        <div class="p-4 rounded-2xl border border-stone-200/80 bg-stone-50/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-2xl">{{ $itemPasar->komoditas->ikon ?? '🌾' }}</span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">
                        {{ str_replace('_', ' ', $itemPasar->user->peran_sektor ?? 'Produsen') }}
                    </span>
                </div>
                <h4 class="font-extrabold text-sm text-stone-800">{{ $itemPasar->komoditas->nama ?? 'Komoditas' }}</h4>
                <p class="text-xs text-stone-500 mt-0.5">
                    Oleh: <strong class="text-stone-700">{{ $itemPasar->user->name ?? 'Mitra PanganKu' }}</strong>
                    @if(!empty($itemPasar->user->lokasi))
                        • {{ $itemPasar->user->lokasi }}
                    @endif
                </p>
            </div>

            <div class="mt-3 pt-3 border-t border-stone-200/70 flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-stone-400">Harga Langsung</p>
                    <p class="font-extrabold text-sm text-emerald-700">Rp {{ number_format($itemPasar->harga_jual_per_kg, 0, ',', '.') }}/kg</p>
                </div>
                <span class="text-xs font-bold text-stone-600 bg-white px-2.5 py-1 rounded-lg border border-stone-200">
                    Stok: {{ number_format($itemPasar->stok_kg, 0, ',', '.') }} kg
                </span>
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 col-span-full py-4 text-center">Belum ada stok aktif yang ditawarkan di pasar saat ini.</p>
        @endforelse
    </div>
</div>

<!-- Modal Edit Listing -->
<div id="modalEditListing" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-xl border border-stone-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-lg text-stone-800">Edit Listing Penjualan</h3>
            <button type="button" onclick="tutupModalListing()" class="w-8 h-8 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-600 font-bold cursor-pointer">✕</button>
        </div>

        <form id="formEditListing" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Stok Tersedia (kg)</label>
                <input type="number" step="0.1" name="stok_kg" id="editStokKg" required class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Harga Jual / kg (Rp)</label>
                <input type="number" name="harga_jual_per_kg" id="editHargaJual" required min="100" class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Status Listing</label>
                <select name="status" id="editStatusListing" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    <option value="aktif">Aktif (Tampil di Pasar)</option>
                    <option value="terjual">Terjual</option>
                </select>
            </div>

            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="tutupModalListing()" class="flex-1 py-3 rounded-2xl bg-stone-100 text-stone-700 font-bold text-sm cursor-pointer">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-2xl bg-emerald-700 text-white font-bold text-sm cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function isiHargaRekomendasi(harga) {
        const input = document.getElementById('inputHargaJual');
        if (input && harga > 0) {
            input.value = harga;
        }
    }

    function bukaModalListing(id, stok, harga, status) {
        const modal = document.getElementById('modalEditListing');
        document.getElementById('formEditListing').action = `/jual-hasil/${id}`;
        document.getElementById('editStokKg').value = stok;
        document.getElementById('editHargaJual').value = harga;
        document.getElementById('editStatusListing').value = status;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalListing() {
        const modal = document.getElementById('modalEditListing');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

@endsection