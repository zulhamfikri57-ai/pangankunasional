@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">🗓️ Manajemen Siklus & Jadwal Perawatan</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Kelola rencana tanam/ternak/tangkap, checklist perawatan, hingga realisasi panen</p>
    </div>
    <button type="button" onclick="document.getElementById('formSiklusBaru').classList.toggle('hidden')"
        class="px-4 py-2.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold w-fit transition cursor-pointer">
        + Mulai Siklus Baru
    </button>
</div>

<!-- FORM TAMBAH SIKLUS BARU -->
<div id="formSiklusBaru" class="{{ $siklusList->count() > 0 ? 'hidden' : '' }} bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/80 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">🌱 Buat Rencana Siklus Produksi Baru</h3>

    <form action="{{ route('siklus.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Komoditas</label>
                <select name="komoditas_id" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    @foreach($komoditasList as $km)
                        <option value="{{ $km->id }}">{{ $km->ikon ?? '🌱' }} {{ $km->nama }} ({{ ucfirst($km->sektor) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Nama Siklus / Blok Lahan</label>
                <input type="text" name="nama_siklus" placeholder="Contoh: Kebun Cabai Blok A - Musim 1" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ date('Y-m-d') }}" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Luas Lahan / Kapasitas</label>
                <input type="number" step="0.01" name="luas_atau_kapasitas" placeholder="Contoh: 1000" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Satuan Kapasitas</label>
                <select name="satuan_kapasitas" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    <option value="m2">Meter Persegi (m²)</option>
                    <option value="hektar">Hektar (ha)</option>
                    <option value="ekor">Ekor (Ternak/Ikan)</option>
                    <option value="kolam">Unit Kolam / Keramba</option>
                    <option value="kapal">Trip Kapal</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Alamat / Lokasi Lahan</label>
                <input type="text" name="alamat_lahan" placeholder="Contoh: Nagari Lakitan, Pesisir Selatan"
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>
        </div>

        <button type="submit" class="px-6 py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Simpan Siklus & Generate Jadwal Otomatis →
        </button>
    </form>
</div>

<!-- DAFTAR SIKLUS PRODUKSI -->
<div class="space-y-6">
    @forelse($siklusList as $sk)
    @php
        $totalJadwal = $sk->jadwals->count();
        $selesaiJadwal = $sk->jadwals->where('status', 'selesai')->count();
        $persenProgres = $totalJadwal > 0 ? round(($selesaiJadwal / $totalJadwal) * 100) : 0;
    @endphp
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/80 shadow-xs">
        <!-- HEADER KARTU SIKLUS -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-stone-100">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase {{ $sk->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($sk->status === 'gagal' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-900') }}">
                        {{ $sk->status === 'berjalan' ? '⏳ Sedang Berjalan' : ($sk->status === 'selesai' ? '✅ Panen Selesai' : '⚠️ Gagal Panen') }}
                    </span>
                    <span class="text-xs font-bold text-stone-500">
                        {{ $sk->komoditas->ikon ?? '🌱' }} {{ $sk->komoditas->nama ?? 'Komoditas' }} • Kapasitas: {{ number_format($sk->luas_atau_kapasitas, 0, ',', '.') }} {{ $sk->satuan_kapasitas }}
                    </span>
                </div>
                <h3 class="font-extrabold text-lg text-stone-800 mt-1.5">{{ $sk->nama_siklus }}</h3>
                <p class="text-xs text-stone-500 mt-0.5">
                    Mulai: <strong>{{ \Carbon\Carbon::parse($sk->tanggal_mulai)->format('d M Y') }}</strong> •
                    Estimasi Panen: <strong>{{ \Carbon\Carbon::parse($sk->estimasi_tanggal_panen)->format('d M Y') }}</strong>
                    @if($sk->alamat_lahan) • 📍 {{ $sk->alamat_lahan }} @endif
                </p>
            </div>

            <!-- TOMBOL AKSI SIKLUS -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="bukaModalPanen({{ $sk->id }}, '{{ addslashes($sk->nama_siklus) }}', {{ $sk->estimasi_hasil_kg }})"
                    class="px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition cursor-pointer">
                    ✅ Catat Hasil Panen
                </button>

                <button type="button" onclick='bukaModalEdit(@json($sk))'
                    class="px-3.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition cursor-pointer">
                    ✏️ Edit Siklus
                </button>

                <form action="{{ route('siklus.destroy', $sk->id) }}" method="POST" onsubmit="return confirm('Hapus siklus ini beserta seluruh jadwal perawatannya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition cursor-pointer">
                        🗑️
                    </button>
                </form>
            </div>
        </div>

        <!-- RINGKASAN TARGET VS AKTUAL & PROGRESS BAR -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 my-4">
            <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200/60">
                <span class="text-[11px] font-bold text-stone-500">Target Estimasi Panen</span>
                <p class="font-extrabold text-base text-stone-800 mt-0.5">{{ number_format($sk->estimasi_hasil_kg, 0, ',', '.') }} kg</p>
            </div>

            <div class="p-3.5 rounded-2xl {{ $sk->hasil_aktual_kg ? 'bg-emerald-50 border-emerald-200' : 'bg-stone-50 border-stone-200/60' }} border">
                <span class="text-[11px] font-bold text-stone-500">Realisasi Panen Aktual</span>
                <p class="font-extrabold text-base {{ $sk->hasil_aktual_kg ? 'text-emerald-800' : 'text-stone-400' }} mt-0.5">
                    {{ $sk->hasil_aktual_kg ? number_format($sk->hasil_aktual_kg, 0, ',', '.') . ' kg' : 'Belum dipanen' }}
                </p>
            </div>

            <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200/60">
                <div class="flex items-center justify-between text-[11px] font-bold text-stone-600 mb-1.5">
                    <span>Progres Perawatan</span>
                    <span>{{ $selesaiJadwal }}/{{ $totalJadwal }} ({{ $persenProgres }}%)</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-stone-200 overflow-hidden">
                    <div class="h-full bg-emerald-600 rounded-full transition-all" style="width: {{ $persenProgres }}%"></div>
                </div>
            </div>
        </div>

        <!-- CHECKLIST JADWAL PERAWATAN + TAMBAH JADWAL MANUAL -->
        <div class="mt-4">
            <div class="flex items-center justify-between mb-2.5">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-stone-500">📋 Checklist Jadwal Perawatan & Pemupukan</h4>
                <button type="button" onclick="document.getElementById('formJadwalManual-{{ $sk->id }}').classList.toggle('hidden')"
                    class="text-xs font-bold text-emerald-700 hover:underline cursor-pointer">
                    + Tambah Jadwal Manual
                </button>
            </div>

            <!-- Form Tambah Jadwal Manual -->
            <form id="formJadwalManual-{{ $sk->id }}" action="{{ route('jadwal.store', $sk->id) }}" method="POST"
                class="hidden mb-3 p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200 grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                @csrf
                <input type="text" name="judul_kegiatan" placeholder="Nama kegiatan (misal: Semprot Fungisida)" required
                    class="px-3 py-2 rounded-xl bg-white border border-stone-300 text-xs">
                <select name="jenis_kegiatan" class="px-3 py-2 rounded-xl bg-white border border-stone-300 text-xs font-semibold">
                    <option value="pemupukan">Pemupukan / Nutrisi</option>
                    <option value="pengendalian_hama">Pengendalian Hama / Obat</option>
                    <option value="perawatan">Perawatan Rutin</option>
                    <option value="panen">Persiapan Panen</option>
                </select>
                <input type="date" name="tanggal_jadwal" value="{{ date('Y-m-d') }}" required
                    class="px-3 py-2 rounded-xl bg-white border border-stone-300 text-xs">
                <div class="flex gap-2">
                    <input type="text" name="dosis_atau_catatan" placeholder="Dosis/catatan..."
                        class="w-full px-3 py-2 rounded-xl bg-white border border-stone-300 text-xs">
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-700 text-white text-xs font-bold shrink-0 cursor-pointer">
                        Simpan
                    </button>
                </div>
            </form>

            <!-- Daftar Item Jadwal -->
            <div class="divide-y divide-stone-100 border border-stone-100 rounded-2xl overflow-hidden">
                @forelse($sk->jadwals->sortBy('tanggal_jadwal') as $jd)
                <div class="p-3 bg-white hover:bg-stone-50/70 flex items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <form action="{{ route('jadwal.toggle', $jd->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="Klik untuk ubah status selesai/menunggu"
                                class="mt-0.5 w-5 h-5 rounded-md border flex items-center justify-center text-xs font-bold transition cursor-pointer {{ $jd->status === 'selesai' ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-stone-300 bg-white hover:border-emerald-600' }}">
                                {{ $jd->status === 'selesai' ? '✓' : '' }}
                            </button>
                        </form>
                        <div>
                            <p class="text-xs font-bold {{ $jd->status === 'selesai' ? 'line-through text-stone-400' : 'text-stone-800' }}">
                                {{ $jd->judul_kegiatan }}
                            </p>
                            <p class="text-[11px] text-stone-500">
                                📅 {{ \Carbon\Carbon::parse($jd->tanggal_jadwal)->format('d M Y') }}
                                {{ $jd->dosis_atau_catatan ? '• ' . $jd->dosis_atau_catatan : '' }}
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('jadwal.destroy', $jd->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-stone-400 hover:text-rose-600 text-xs font-bold px-2 cursor-pointer">✕</button>
                    </form>
                </div>
                @empty
                <p class="p-4 text-xs text-stone-400 text-center">Belum ada jadwal perawatan tercatat pada siklus ini.</p>
                @endforelse
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white p-8 rounded-3xl border border-stone-100 text-center">
        <p class="text-sm font-bold text-stone-700">Belum ada siklus produksi aktif.</p>
        <p class="text-xs text-stone-500 mt-1">Gunakan formulir di atas untuk mulai merencanakan siklus tanam, ternak, atau melaut Anda.</p>
    </div>
    @endforelse
</div>

<!-- MODAL 1: CATAT HASIL PANEN / TUTUP SIKLUS -->
<div id="modalPanen" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-base text-stone-800">✅ Catat Realisasi Panen & Tutup Siklus</h3>
            <button type="button" onclick="tutupModal('modalPanen')" class="text-stone-400 hover:text-stone-700 font-bold">✕</button>
        </div>

        <form id="formPanen" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <p id="labelNamaSiklusPanen" class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-2 rounded-xl"></p>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Status Akhir Siklus</label>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm font-semibold">
                    <option value="selesai">✅ Panen Berhasil (Selesai)</option>
                    <option value="gagal">⚠️ Gagal Panen (Evaluasi Rugi)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Panen Aktual</label>
                <input type="date" name="tanggal_panen_aktual" value="{{ date('Y-m-d') }}" required
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Total Bobot Panen Aktual (kg)</label>
                <input type="number" step="0.1" id="inputHasilAktual" name="hasil_aktual_kg" required
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm font-bold">
                <p class="text-[11px] text-stone-500 mt-1">Isi 0 jika gagal panen total. Angka ini otomatis memperbarui grafik HPP & Laporan.</p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModal('modalPanen')" class="px-4 py-2.5 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer">Simpan Realisasi Panen</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: EDIT PARAMETER SIKLUS -->
<div id="modalEditSiklus" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-xl my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-base text-stone-800">✏️ Edit Parameter Siklus</h3>
            <button type="button" onclick="tutupModal('modalEditSiklus')" class="text-stone-400 hover:text-stone-700 font-bold">✕</button>
        </div>

        <form id="formEditSiklus" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Nama Siklus / Blok</label>
                    <input type="text" id="editNamaSiklus" name="nama_siklus" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Status Siklus</label>
                    <select id="editStatusSiklus" name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm font-semibold">
                        <option value="berjalan">Sedang Berjalan</option>
                        <option value="selesai">Selesai Panen</option>
                        <option value="gagal">Gagal Panen</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Mulai</label>
                    <input type="date" id="editTanggalMulai" name="tanggal_mulai" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Estimasi Tanggal Panen</label>
                    <input type="date" id="editEstimasiPanen" name="estimasi_tanggal_panen" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Target Estimasi (kg)</label>
                    <input type="number" step="0.1" id="editEstimasiKg" name="estimasi_hasil_kg" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Luas / Kapasitas</label>
                    <input type="number" step="0.01" id="editLuas" name="luas_atau_kapasitas" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Satuan</label>
                    <input type="text" id="editSatuan" name="satuan_kapasitas" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Alamat Lahan</label>
                    <input type="text" id="editAlamat" name="alamat_lahan" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModal('modalEditSiklus')" class="px-4 py-2.5 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalPanen(id, namaSiklus, estimasiKg) {
        document.getElementById('formPanen').action = `/siklus/${id}/selesai`;
        document.getElementById('labelNamaSiklusPanen').textContent = `Siklus: ${namaSiklus}`;
        document.getElementById('inputHasilAktual').value = estimasiKg;
        const modal = document.getElementById('modalPanen');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function bukaModalEdit(sk) {
        document.getElementById('formEditSiklus').action = `/siklus/${sk.id}`;
        document.getElementById('editNamaSiklus').value = sk.nama_siklus || '';
        document.getElementById('editStatusSiklus').value = sk.status || 'berjalan';
        document.getElementById('editTanggalMulai').value = (sk.tanggal_mulai || '').substring(0, 10);
        document.getElementById('editEstimasiPanen').value = (sk.estimasi_tanggal_panen || '').substring(0, 10);
        document.getElementById('editEstimasiKg').value = sk.estimasi_hasil_kg || 0;
        document.getElementById('editLuas').value = sk.luas_atau_kapasitas || 0;
        document.getElementById('editSatuan').value = sk.satuan_kapasitas || 'm2';
        document.getElementById('editAlamat').value = sk.alamat_lahan || '';

        const modal = document.getElementById('modalEditSiklus');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

@endsection