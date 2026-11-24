@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">🌾 Catatan Produksi & Hasil Panen</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Rekam realisasi panen harian/mingguan per sektor dan salurkan langsung ke pasar</p>
    </div>

    <!-- TAB SEKTOR -->
    <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-2xl border border-stone-200/80 w-fit">
        <a href="{{ route('produksi', ['sektor' => 'darat']) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $sektor === 'darat' ? 'bg-emerald-700 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
            🌱 Pertanian
        </a>
        <a href="{{ route('produksi', ['sektor' => 'ternak']) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $sektor === 'ternak' ? 'bg-emerald-700 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
            🐾 Peternakan
        </a>
        <a href="{{ route('produksi', ['sektor' => 'laut']) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $sektor === 'laut' ? 'bg-emerald-700 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
            🐟 Perikanan
        </a>
    </div>
</div>

<!-- RINGKASAN SEKTOR -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-5 rounded-3xl border border-stone-100 shadow-xs">
        <span class="text-xs font-bold text-stone-500">Total Volume Produksi ({{ ucfirst($sektor) }})</span>
        <p class="font-extrabold text-xl text-emerald-700 mt-1">{{ number_format($totalBerat, 1, ',', '.') }} kg / unit</p>
    </div>
    <div class="bg-white p-5 rounded-3xl border border-stone-100 shadow-xs">
        <span class="text-xs font-bold text-stone-500">Jumlah Catatan Panen</span>
        <p class="font-extrabold text-xl text-stone-800 mt-1">{{ $jumlahCatatan }} Catatan</p>
    </div>
</div>

<!-- FORM CATAT PRODUKSI BARU -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">📝 Tambah Catatan Hasil Produksi</h3>

    <form action="{{ route('produksi.store') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="sektor" value="{{ $sektor }}">

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Komoditas</label>
                <select name="komoditas_id" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    @foreach($komoditas as $km)
                        <option value="{{ $km->id }}">{{ $km->ikon ?? '🌱' }} {{ $km->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Jumlah Hasil</label>
                <input type="number" step="0.1" name="jumlah" placeholder="Contoh: 120" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Satuan</label>
                <select name="satuan" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    <option value="kg">Kilogram (kg)</option>
                    <option value="ton">Ton</option>
                    <option value="ekor">Ekor</option>
                    <option value="butir">Butir / Krat</option>
                    <option value="liter">Liter</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Panen</label>
                <input type="date" name="tanggal_panen" value="{{ date('Y-m-d') }}" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="catatan" placeholder="Catatan mutu / sortasi (misal: Grade A segar, panen pagi)..."
                class="flex-1 px-4 py-3 rounded-xl border border-stone-300 text-sm">
            <button type="submit" class="px-6 py-3 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
                Simpan Produksi →
            </button>
        </div>
    </form>
</div>

<!-- RIWAYAT PRODUKSI -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">Riwayat Hasil Produksi ({{ $riwayat->count() }})</h3>

    <div class="divide-y divide-stone-100">
        @forelse($riwayat as $item)
        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-base">{{ $item->komoditas->ikon ?? '🌾' }}</span>
                    <span class="font-extrabold text-sm text-stone-800">{{ $item->komoditas->nama ?? 'Komoditas' }}</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold">
                        {{ number_format($item->jumlah, 1, ',', '.') }} {{ $item->satuan }}
                    </span>
                </div>
                <p class="text-xs text-stone-500 mt-1">
                    📅 {{ \Carbon\Carbon::parse($item->tanggal_panen)->format('d M Y') }}
                    {{ $item->catatan ? '• ' . $item->catatan : '' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('jual', ['komoditas_id' => $item->komoditas_id, 'stok_kg' => $item->jumlah]) }}"
                    class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[11px] font-bold transition">
                    🏪 Jual ke Pasar
                </a>

                <button type="button" onclick='bukaModalEditProduksi(@json($item))'
                    class="px-2.5 py-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 text-[11px] font-bold transition cursor-pointer">
                    ✏️ Edit
                </button>

                <form action="{{ route('produksi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus catatan produksi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold transition cursor-pointer">
                        🗑️
                    </button>
                </form>
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 py-6 text-center">Belum ada catatan hasil produksi pada sektor ini.</p>
        @endforelse
    </div>
</div>

<!-- MODAL EDIT CATATAN PRODUKSI -->
<div id="modalEditProduksi" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-base text-stone-800">✏️ Edit Catatan Produksi</h3>
            <button type="button" onclick="tutupModalProduksi()" class="text-stone-400 hover:text-stone-700 font-bold">✕</button>
        </div>

        <form id="formEditProduksi" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Komoditas</label>
                <select id="editProdKomoditas" name="komoditas_id" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    @foreach($komoditas as $km)
                        <option value="{{ $km->id }}">{{ $km->ikon ?? '🌱' }} {{ $km->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Jumlah Hasil</label>
                    <input type="number" step="0.1" id="editProdJumlah" name="jumlah" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Satuan</label>
                    <input type="text" id="editProdSatuan" name="satuan" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Panen</label>
                <input type="date" id="editProdTanggal" name="tanggal_panen" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Catatan Mutu</label>
                <input type="text" id="editProdCatatan" name="catatan" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalProduksi()" class="px-4 py-2.5 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalEditProduksi(item) {
        document.getElementById('formEditProduksi').action = `/produksi/${item.id}`;
        document.getElementById('editProdKomoditas').value = item.komoditas_id;
        document.getElementById('editProdJumlah').value = item.jumlah;
        document.getElementById('editProdSatuan').value = item.satuan || 'kg';
        document.getElementById('editProdTanggal').value = (item.tanggal_panen || '').substring(0, 10);
        document.getElementById('editProdCatatan').value = item.catatan || '';

        const modal = document.getElementById('modalEditProduksi');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalProduksi() {
        const modal = document.getElementById('modalEditProduksi');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

@endsection