@extends('layouts.app')
@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
        <div>
            <h2 class="fluid-title font-extrabold text-stone-800">💰 Pembukuan Keuangan Usaha Pangan</h2>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Catat, filter, dan kelola modal maupun omzet panen yang
                terhubung ke setiap siklus produksi</p>
        </div>
        <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit">
            {{ $konfig['ikon'] }} {{ $konfig['label_peran'] }}
        </span>
    </div>

    <!-- RINGKASAN SALDO (MENGIKUTI FILTER AKTIF) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-3xl border border-stone-100 shadow-xs">
            <span class="text-xs font-bold text-stone-500">Total Pemasukan {{ $filterSiklus ? '(Terfilter)' : '' }}</span>
            <p class="font-extrabold text-xl text-emerald-700 mt-1">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-stone-100 shadow-xs">
            <span class="text-xs font-bold text-stone-500">Total Pengeluaran / Modal
                {{ $filterSiklus ? '(Terfilter)' : '' }}</span>
            <p class="font-extrabold text-xl text-rose-600 mt-1">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-stone-100 shadow-xs">
            <span class="text-xs font-bold text-stone-500">Keuntungan / Saldo Bersih</span>
            <p class="font-extrabold text-xl {{ $saldoBersih >= 0 ? 'text-stone-800' : 'text-rose-700' }} mt-1">
                Rp {{ number_format($saldoBersih, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- FORM TAMBAH TRANSAKSI BARU -->
    <div id="formTambahKeuangan" class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
        <h3 class="font-extrabold text-base text-stone-800 mb-4">📝 Catat Transaksi Baru</h3>

        <form action="{{ route('keuangan.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Tautkan ke Siklus Produksi</label>
                    <select id="addSiklus" name="siklus_produksi_id"
                        class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                        <option value="">-- Umum / Tanpa Siklus Spesifik --</option>
                        @foreach($siklusList as $sk)
                            <option value="{{ $sk->id }}" {{ (string) $filterSiklus === (string) $sk->id ? 'selected' : '' }}>
                                {{ $sk->komoditas->ikon ?? '🌱' }} {{ $sk->nama_siklus }} ({{ ucfirst($sk->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Jenis Transaksi</label>
                    <select id="addJenis" name="jenis" required
                        class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                        <option value="pengeluaran">📤 Pengeluaran (Modal / Biaya)</option>
                        <option value="pemasukan">📥 Pemasukan (Penjualan Panen)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Transaksi</label>
                    <input type="date" id="addTanggal" name="tanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Kategori Biaya / Pendapatan</label>
                    <input type="text" id="addKategori" name="kategori" list="daftarKategori"
                        placeholder="Contoh: Pupuk NPK, Bibit, Pakan, Jual Panen" required
                        class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
                    <datalist id="daftarKategori">
                        @foreach($konfig['kategori_biaya'] as $kb)
                            <option value="{{ $kb }}">
                        @endforeach
                            @foreach($konfig['kategori_masuk'] as $km)
                                <option value="{{ $km }}">
                            @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Nominal (Rp)</label>
                    <input type="text" id="addJumlah" name="jumlah" data-rupiah placeholder="Contoh: 500.000" required
                        class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Keterangan Tambahan</label>
                    <input type="text" id="addKeterangan" name="keterangan" placeholder="Catatan opsional..."
                        class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
                </div>
            </div>

            <button type="submit"
                class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
                Simpan Catatan Keuangan →
            </button>
        </form>
    </div>

    <!-- RIWAYAT TRANSAKSI + FILTER SIKLUS & JENIS -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-4 mb-4 border-b border-stone-100">
            <h3 class="font-extrabold text-base text-stone-800">Riwayat Transaksi ({{ $transaksis->count() }})</h3>

            <form method="GET" action="{{ route('keuangan') }}" class="flex flex-wrap items-center gap-2">
                <select name="siklus_id" onchange="this.form.submit()"
                    class="px-3.5 py-2 rounded-xl border border-stone-300 bg-stone-50 text-xs font-bold text-stone-700">
                    <option value="">🔍 Semua Siklus</option>
                    <option value="umum" {{ $filterSiklus === 'umum' ? 'selected' : '' }}>📌 Umum (Tanpa Siklus)</option>
                    @foreach($siklusList as $sk)
                        <option value="{{ $sk->id }}" {{ (string) $filterSiklus === (string) $sk->id ? 'selected' : '' }}>
                            🌱 {{ $sk->nama_siklus }}
                        </option>
                    @endforeach
                </select>

                <select name="jenis" onchange="this.form.submit()"
                    class="px-3.5 py-2 rounded-xl border border-stone-300 bg-stone-50 text-xs font-bold text-stone-700">
                    <option value="">Semua Jenis</option>
                    <option value="pemasukan" {{ $filterJenis === 'pemasukan' ? 'selected' : '' }}>📥 Pemasukan</option>
                    <option value="pengeluaran" {{ $filterJenis === 'pengeluaran' ? 'selected' : '' }}>📤 Pengeluaran</option>
                </select>

                @if($filterSiklus || $filterJenis)
                    <a href="{{ route('keuangan') }}" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-700 text-xs font-bold">
                        ✕ Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="divide-y divide-stone-100">
            @forelse($transaksis as $t)
                <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $t->jenis === 'pemasukan' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $t->jenis }}
                            </span>
                            <span class="font-bold text-sm text-stone-800">{{ $t->kategori }}</span>
                            @if($t->siklus)
                                <span class="px-2.5 py-0.5 rounded-lg bg-stone-100 text-stone-700 text-[11px] font-bold">
                                    🌱 {{ $t->siklus->nama_siklus }}
                                </span>
                            @endif
                            @if($t->sumber_transaksi === 'pasar_panen')
                                <span class="px-2.5 py-0.5 rounded-lg bg-amber-100 text-amber-900 text-[10px] font-extrabold">
                                    🔒 Otomatis dari Pasar Panen
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-stone-500 mt-1">
                            {{ \Carbon\Carbon::parse($t->tanggal)->format('d M Y') }}
                            {{ $t->keterangan ? '• ' . $t->keterangan : '' }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3">
                        <p
                            class="font-extrabold text-sm {{ $t->jenis === 'pemasukan' ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $t->jenis === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($t->jumlah, 0, ',', '.') }}
                        </p>

                        @if($t->sumber_transaksi === 'pasar_panen')
                            <a href="{{ route('jual') }}" title="Kelola melalui menu Pasar Panen"
                                class="px-2.5 py-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-600 text-[11px] font-bold transition">
                                🏪 Lihat Listing
                            </a>
                        @else
                            <div class="flex items-center gap-1.5">
                                <button type="button" title="Catat Ulang Transaksi Rutin" onclick='catatUlangKeuangan(@json($t))'
                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[11px] font-bold transition cursor-pointer">
                                    ↻ Ulangi
                                </button>

                                <button type="button" title="Edit Transaksi" onclick='bukaModalEditKeuangan(@json($t))'
                                    class="px-2.5 py-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 text-[11px] font-bold transition cursor-pointer">
                                    ✏️ Edit
                                </button>

                                <form action="{{ route('keuangan.destroy', $t->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus catatan transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Transaksi"
                                        class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold transition cursor-pointer">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-xs text-stone-500 py-6 text-center">Belum ada catatan transaksi keuangan yang sesuai filter.</p>
            @endforelse
        </div>
    </div>

    <!-- MODAL EDIT TRANSAKSI KEUANGAN -->
    <div id="modalEditKeuangan" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-extrabold text-base text-stone-800">✏️ Edit Catatan Keuangan</h3>
                <button type="button" onclick="tutupModalKeuangan()"
                    class="text-stone-400 hover:text-stone-700 font-bold">✕</button>
            </div>

            <form id="formEditKeuangan" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Tautkan ke Siklus Produksi</label>
                    <select id="editKeuSiklus" name="siklus_produksi_id"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                        <option value="">-- Umum / Tanpa Siklus Spesifik --</option>
                        @foreach($siklusList as $sk)
                            <option value="{{ $sk->id }}">
                                {{ $sk->komoditas->ikon ?? '🌱' }} {{ $sk->nama_siklus }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Jenis Transaksi</label>
                        <select id="editKeuJenis" name="jenis" required
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                            <option value="pengeluaran">📤 Pengeluaran (Modal)</option>
                            <option value="pemasukan">📥 Pemasukan (Omzet)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal</label>
                        <input type="date" id="editKeuTanggal" name="tanggal" required
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Kategori</label>
                        <input type="text" id="editKeuKategori" name="kategori" list="daftarKategori" required
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Nominal (Rp)</label>
                        <input type="text" id="editKeuJumlah" name="jumlah" data-rupiah required
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Keterangan</label>
                    <input type="text" id="editKeuKeterangan" name="keterangan"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="tutupModalKeuangan()"
                        class="px-4 py-2.5 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer">Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModalEditKeuangan(t) {
            document.getElementById('formEditKeuangan').action = `/keuangan/${t.id}`;
            document.getElementById('editKeuSiklus').value = t.siklus_produksi_id || '';
            document.getElementById('editKeuJenis').value = t.jenis || 'pengeluaran';
            document.getElementById('editKeuTanggal').value = (t.tanggal || '').substring(0, 10);
            document.getElementById('editKeuKategori').value = t.kategori || '';
            document.getElementById('editKeuJumlah').value = formatRibuanStr(Math.round(parseFloat(t.jumlah || t.nominal || 0)));
            document.getElementById('editKeuKeterangan').value = t.keterangan || '';

            const modal = document.getElementById('modalEditKeuangan');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function tutupModalKeuangan() {
            const modal = document.getElementById('modalEditKeuangan');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function catatUlangKeuangan(t) {
            document.getElementById('addSiklus').value = t.siklus_produksi_id || '';
            document.getElementById('addJenis').value = t.jenis || 'pengeluaran';
            document.getElementById('addKategori').value = t.kategori || '';
            document.getElementById('addJumlah').value = formatRibuanStr(Math.round(parseFloat(t.jumlah || t.nominal || 0)));
            document.getElementById('addKeterangan').value = t.keterangan || '';
            document.getElementById('addTanggal').value = new Date().toISOString().split('T')[0];

            document.getElementById('formTambahKeuangan').scrollIntoView({ behavior: 'smooth' });
        }
    </script>

@endsection