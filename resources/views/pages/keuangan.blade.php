@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">Keuangan & Pembukuan</h2>
        <p class="text-xs text-stone-500 mt-0.5">{{ $konfig['siklus'] }}</p>
    </div>
    <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit">
        {{ $konfig['ikon'] }} {{ $konfig['label_peran'] }}
    </span>
</div>

<!-- Kartu Ringkasan Saldo / Laba -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4 mb-6">
    <div class="{{ $labaBersih >= 0 ? 'bg-emerald-700' : 'bg-rose-700' }} text-white rounded-3xl p-5 sm:p-6 shadow-sm">
        <span class="px-2.5 py-1 rounded-full bg-white/20 text-[11px] font-bold">
            {{ $labaBersih >= 0 ? '✓ SURPLUS / UNTUNG' : '⚠ DEFISIT / RUGI' }}
        </span>
        <p class="fluid-metric font-extrabold mt-3 break-words">Rp {{ number_format($labaBersih, 0, ',', '.') }}</p>
        <p class="text-xs text-white/85 mt-1">Selisih bersih pemasukan & biaya</p>
    </div>

    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-stone-100 shadow-sm flex flex-col justify-center">
        <p class="text-xs font-bold text-stone-500">Total Pemasukan / Anggaran</p>
        <p class="text-xl sm:text-2xl font-extrabold text-emerald-700 mt-1 break-words">Rp {{ number_format($pemasukan, 0, ',', '.') }}</p>
        <p class="text-[11px] text-stone-400 mt-1">Tercatat dari penjualan & dana masuk</p>
    </div>

    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-stone-100 shadow-sm flex flex-col justify-center">
        <p class="text-xs font-bold text-stone-500">Total Pengeluaran / Biaya</p>
        <p class="text-xl sm:text-2xl font-extrabold text-orange-600 mt-1 break-words">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</p>
        <p class="text-[11px] text-stone-400 mt-1">Akumulasi biaya operasional</p>
    </div>
</div>

<!-- Form Catat Transaksi Baru -->
<div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-100 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h3 class="font-bold text-base sm:text-lg text-stone-800">Catat Transaksi Keuangan</h3>
        <div class="flex gap-1.5 bg-stone-100 p-1 rounded-xl w-full sm:w-auto">
            <button type="button" id="tabKeluar" onclick="pilihJenis('pengeluaran')"
                    class="flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg text-xs font-bold bg-orange-600 text-white transition cursor-pointer">
                - Pengeluaran
            </button>
            <button type="button" id="tabMasuk" onclick="pilihJenis('pemasukan')"
                    class="flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg text-xs font-bold text-stone-600 transition cursor-pointer">
                + Pemasukan
            </button>
        </div>
    </div>

    <form action="{{ route('keuangan.store') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="jenis" id="inputJenis" value="pengeluaran">

        <div id="opsiPengeluaran" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3">
            @foreach($konfig['kategori_biaya'] as $kat)
            <label class="cursor-pointer">
                <input type="radio" name="kategori" value="{{ $kat }}" class="peer sr-only kat-keluar" {{ $loop->first ? 'checked' : '' }}>
                <div class="p-3 sm:p-3.5 rounded-2xl border border-stone-200 peer-checked:bg-orange-600 peer-checked:text-white peer-checked:border-orange-600 font-bold text-xs sm:text-sm text-center transition h-full flex items-center justify-center">
                    {{ $kat }}
                </div>
            </label>
            @endforeach
        </div>

        <div id="opsiPemasukan" class="hidden grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
            @foreach($konfig['kategori_masuk'] as $kat)
            <label class="cursor-pointer">
                <input type="radio" name="kategori" value="{{ $kat }}" class="peer sr-only kat-masuk" disabled>
                <div class="p-3 sm:p-3.5 rounded-2xl border border-stone-200 peer-checked:bg-emerald-700 peer-checked:text-white peer-checked:border-emerald-700 font-bold text-xs sm:text-sm text-center transition h-full flex items-center justify-center">
                    {{ $kat }}
                </div>
            </label>
            @endforeach
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Nominal (Rp)</label>
            <input type="number" name="nominal" placeholder="Masukkan nominal (contoh: 250000)" required
                   class="w-full px-4 py-3 sm:py-3.5 rounded-2xl border border-stone-300 text-sm focus:outline-none focus:border-emerald-600">
        </div>

        <button type="submit" id="btnSimpanKeuangan"
                class="w-full py-3.5 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm transition cursor-pointer">
            💾 Simpan Pengeluaran
        </button>
    </form>
</div>

<!-- Riwayat Transaksi Terakhir + Tombol Edit & Hapus -->
<div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-100">
    <h3 class="font-bold text-stone-800 mb-4 text-sm sm:text-base">Riwayat Transaksi Terakhir</h3>
    <div class="divide-y divide-stone-100">
        @forelse($transaksi as $t)
        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $t->jenis == 'pemasukan' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                        {{ $t->jenis }}
                    </span>
                    <p class="font-bold text-sm text-stone-800 truncate">{{ $t->kategori }}</p>
                </div>
                <p class="text-xs text-stone-400 truncate mt-0.5">{{ $t->tanggal }} • {{ $t->musim_tanam }}</p>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-3">
                <span class="font-extrabold text-sm whitespace-nowrap {{ $t->jenis == 'pemasukan' ? 'text-emerald-700' : 'text-orange-600' }}">
                    {{ $t->jenis == 'pemasukan' ? '+' : '-' }} Rp {{ number_format($t->nominal, 0, ',', '.') }}
                </span>

                <div class="flex items-center gap-1.5">
                    <button type="button"
                            onclick="bukaModalEdit({{ $t->id }}, '{{ $t->jenis }}', '{{ addslashes($t->kategori) }}', {{ $t->nominal }}, '{{ $t->tanggal }}')"
                            class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition cursor-pointer">
                        ✏️ Edit
                    </button>

                    <form action="{{ route('keuangan.destroy', $t->id) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus transaksi {{ addslashes($t->kategori) }} sebesar Rp {{ number_format($t->nominal, 0, ',', '.') }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition cursor-pointer">
                            🗑️ Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 py-4 text-center">Belum ada transaksi tercatat.</p>
        @endforelse
    </div>
</div>

<!-- Modal Edit Transaksi -->
<div id="modalEditKeuangan" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-xl border border-stone-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-lg text-stone-800">Edit Transaksi Keuangan</h3>
            <button type="button" onclick="tutupModalEdit()" class="w-8 h-8 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-600 font-bold cursor-pointer">
                ✕
            </button>
        </div>

        <form id="formEditKeuangan" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Jenis Transaksi</label>
                <select name="jenis" id="editJenis" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    <option value="pengeluaran">Pengeluaran (-)</option>
                    <option value="pemasukan">Pemasukan (+)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Kategori</label>
                <input type="text" name="kategori" id="editKategori" required
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:outline-none focus:border-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Nominal (Rp)</label>
                <input type="number" name="nominal" id="editNominal" required min="100"
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:outline-none focus:border-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Transaksi</label>
                <input type="date" name="tanggal" id="editTanggal" required
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="tutupModalEdit()"
                        class="flex-1 py-3 rounded-2xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-sm transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 py-3 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function pilihJenis(jenis) {
        document.getElementById('inputJenis').value = jenis;
        const keluarDiv = document.getElementById('opsiPengeluaran');
        const masukDiv = document.getElementById('opsiPemasukan');
        const tabKeluar = document.getElementById('tabKeluar');
        const tabMasuk = document.getElementById('tabMasuk');
        const btn = document.getElementById('btnSimpanKeuangan');

        if (jenis === 'pengeluaran') {
            keluarDiv.classList.remove('hidden');
            keluarDiv.classList.add('grid');
            masukDiv.classList.add('hidden');
            masukDiv.classList.remove('grid');

            document.querySelectorAll('.kat-keluar').forEach((el, i) => { el.disabled = false; if(i===0) el.checked = true; });
            document.querySelectorAll('.kat-masuk').forEach(el => el.disabled = true);

            tabKeluar.className = 'flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg text-xs font-bold bg-orange-600 text-white transition cursor-pointer';
            tabMasuk.className = 'flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg text-xs font-bold text-stone-600 transition cursor-pointer';
            btn.className = 'w-full py-3.5 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm transition cursor-pointer';
            btn.textContent = '💾 Simpan Pengeluaran';
        } else {
            masukDiv.classList.remove('hidden');
            masukDiv.classList.add('grid');
            keluarDiv.classList.add('hidden');
            keluarDiv.classList.remove('grid');

            document.querySelectorAll('.kat-masuk').forEach((el, i) => { el.disabled = false; if(i===0) el.checked = true; });
            document.querySelectorAll('.kat-keluar').forEach(el => el.disabled = true);

            tabMasuk.className = 'flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-700 text-white transition cursor-pointer';
            tabKeluar.className = 'flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg text-xs font-bold text-stone-600 transition cursor-pointer';
            btn.className = 'w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer';
            btn.textContent = '💾 Simpan Pemasukan';
        }
    }

    function bukaModalEdit(id, jenis, kategori, nominal, tanggal) {
        const modal = document.getElementById('modalEditKeuangan');
        const form = document.getElementById('formEditKeuangan');

        form.action = `/keuangan/${id}`;
        document.getElementById('editJenis').value = jenis;
        document.getElementById('editKategori').value = kategori;
        document.getElementById('editNominal').value = nominal;
        document.getElementById('editTanggal').value = tanggal;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalEdit() {
        const modal = document.getElementById('modalEditKeuangan');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

@endsection