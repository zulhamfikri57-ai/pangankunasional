@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">🗓️ Perencanaan Siklus & Jadwal Otomatis</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Hitung perkiraan hasil panen dan buat jadwal pemupukan/perawatan secara otomatis</p>
    </div>
    <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit uppercase">
        Sektor: {{ str_replace('_', ' ', $peran) }}
    </span>
</div>

<!-- FORM MULAI SIKLUS BARU + PREDIKSI OTOMATIS -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">🌱 Buat Rencana Siklus Baru</h3>

    <form action="{{ route('siklus.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Komoditas</label>
                <select name="komoditas_id" id="pilihKomoditas" onchange="hitungPreview()" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-stone-800 text-sm font-semibold">
                    @foreach($komoditas as $k)
                        <option value="{{ $k->id }}" data-nama="{{ strtolower($k->nama) }}" data-sektor="{{ $k->sektor }}">
                            {{ $k->ikon }} {{ $k->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Nama Lahan / Kandang / Siklus</label>
                <input type="text" name="nama_siklus" placeholder="Contoh: Kebun Cabai Blok A - Musim 1" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-800 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Mulai (Tanam / Tebar / Bibit)</label>
                <input type="date" name="tanggal_mulai" id="inputTanggalMulai" value="{{ date('Y-m-d') }}" onchange="hitungPreview()" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-800 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">
                    {{ $peran === 'peternak' ? 'Jumlah Populasi' : ($peran === 'nelayan' ? 'Luas Tambak / Kolam' : 'Luas Lahan') }}
                </label>
                <input type="number" step="0.01" name="luas_atau_kapasitas" id="inputKapasitas" value="1000" oninput="hitungPreview()" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-800 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Satuan Kapasitas</label>
                <select name="satuan_kapasitas" id="inputSatuan" onchange="hitungPreview()"
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-stone-800 text-sm font-semibold">
                    @if($peran === 'peternak')
                        <option value="ekor">Ekor</option>
                        <option value="kandang">Kandang</option>
                    @elseif($peran === 'nelayan')
                        <option value="m2">Meter Persegi (m²)</option>
                        <option value="kolam">Kolam / Keramba</option>
                    @else
                        <option value="m2">Meter Persegi (m²)</option>
                        <option value="hektar">Hektar (Ha)</option>
                        <option value="rante">Rante (400 m²)</option>
                    @endif
                </select>
            </div>

            <!-- Kotak Live Preview Hasil Panen -->
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col justify-center">
                <span class="text-[11px] font-bold text-emerald-800 uppercase">🔮 Estimasi Hasil & Panen</span>
                <p class="font-extrabold text-base text-emerald-900 mt-0.5" id="previewHasil">~1.000 kg</p>
                <p class="text-[11px] text-emerald-700" id="previewTanggal">Perkiraan Panen: -</p>
            </div>
        </div>

        <button type="submit" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            ✨ Simpan Siklus & Buat Jadwal Otomatis
        </button>
    </form>
</div>

<!-- JADWAL PERAWATAN / PEMUPUKAN MENDATANG -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-3">🔔 Checklist Jadwal Pemupukan, Pakan & Perawatan</h3>

    <div class="divide-y divide-stone-100">
        @forelse($jadwalMendatang as $j)
        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-900">
                        {{ $j->tanggal_jadwal->format('d M Y') }}
                    </span>
                    <span class="text-xs font-bold text-emerald-700">{{ $j->siklus->nama_siklus ?? '' }}</span>
                </div>
                <p class="font-extrabold text-sm text-stone-800 mt-1">{{ $j->judul_kegiatan }}</p>
                <p class="text-xs text-stone-500 mt-0.5">{{ $j->dosis_atau_catatan }}</p>
            </div>

            <form action="{{ route('jadwal.toggle', $j->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs transition cursor-pointer">
                    ✓ Tandai Selesai
                </button>
            </form>
        </div>
        @empty
        <p class="text-xs text-stone-500 py-4 text-center">Belum ada jadwal kegiatan mendatang. Buat rencana siklus di atas untuk menghasilkan jadwal otomatis.</p>
        @endforelse
    </div>
</div>

<!-- DAFTAR SIKLUS BERJALAN -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">🚜 Siklus Produksi Sedang Berjalan ({{ $siklusBerjalan->count() }})</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($siklusBerjalan as $s)
        @php
            $totalHari = max(1, $s->tanggal_mulai->diffInDays($s->estimasi_tanggal_panen));
            $hariBerjalan = min($totalHari, max(0, $s->tanggal_mulai->diffInDays(now(), false)));
            $persen = min(100, max(5, round(($hariBerjalan / $totalHari) * 100)));
        @endphp
        <div class="p-5 rounded-2xl border border-stone-200 bg-stone-50/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-extrabold text-emerald-700">{{ $s->komoditas->ikon ?? '🌱' }} {{ $s->komoditas->nama ?? '' }}</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                        Kapasitas: {{ number_format($s->luas_atau_kapasitas, 0, ',', '.') }} {{ $s->satuan_kapasitas }}
                    </span>
                </div>
                <h4 class="font-extrabold text-base text-stone-800 mt-1">{{ $s->nama_siklus }}</h4>
                <p class="text-xs text-stone-500 mt-0.5">
                    Mulai: <strong>{{ $s->tanggal_mulai->format('d M Y') }}</strong> • Estimasi Panen: <strong>{{ $s->estimasi_tanggal_panen->format('d M Y') }}</strong>
                </p>

                <!-- Progress Bar Waktu Panen -->
                <div class="mt-3">
                    <div class="flex justify-between text-[11px] font-bold text-stone-600 mb-1">
                        <span>Progres Menuju Panen</span>
                        <span>{{ $persen }}%</span>
                    </div>
                    <div class="w-full h-2.5 rounded-full bg-stone-200 overflow-hidden">
                        <div class="h-full bg-emerald-600 rounded-full" style="width: {{ $persen }}%"></div>
                    </div>
                </div>

                <div class="mt-3 p-3 rounded-xl bg-white border border-stone-200/70 flex items-center justify-between">
                    <span class="text-xs text-stone-500">Perkiraan Hasil Panen:</span>
                    <span class="font-extrabold text-sm text-emerald-700">{{ number_format($s->estimasi_hasil_kg, 0, ',', '.') }} kg</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-stone-200 flex gap-2">
                <button type="button" onclick="bukaModalPanen({{ $s->id }}, '{{ addslashes($s->nama_siklus) }}', {{ $s->estimasi_hasil_kg }})"
                    class="flex-1 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition cursor-pointer">
                    🌾 Catat Realisasi Panen
                </button>
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 col-span-full py-4 text-center">Belum ada siklus produksi aktif.</p>
        @endforelse
    </div>
</div>

<!-- RIWAYAT SIKLUS SELESAI -->
@if($siklusSelesai->count() > 0)
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">✅ Riwayat Panen Selesai ({{ $siklusSelesai->count() }})</h3>
    <div class="divide-y divide-stone-100">
        @foreach($siklusSelesai as $ss)
        <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <p class="font-bold text-sm text-stone-800">{{ $ss->komoditas->ikon ?? '🌾' }} {{ $ss->nama_siklus }}</p>
                <p class="text-xs text-stone-500">
                    Panen: {{ $ss->tanggal_panen_aktual ? $ss->tanggal_panen_aktual->format('d M Y') : '-' }} • Kapasitas: {{ number_format($ss->luas_atau_kapasitas, 0, ',', '.') }} {{ $ss->satuan_kapasitas }}
                </p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-xs text-stone-500">Estimasi: {{ number_format($ss->estimasi_hasil_kg, 0, ',', '.') }} kg</p>
                <p class="font-extrabold text-sm text-emerald-700">Aktual: {{ number_format($ss->hasil_aktual_kg, 0, ',', '.') }} kg</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- MODAL CATAT REALISASI PANEN -->
<div id="modalPanen" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-base text-stone-800" id="judulModalPanen">Catat Realisasi Panen</h3>
            <button type="button" onclick="tutupModalPanen()" class="w-8 h-8 rounded-lg bg-stone-100 text-stone-700 font-bold cursor-pointer">✕</button>
        </div>

        <form id="formSelesaiPanen" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tanggal Panen Aktual</label>
                <input type="date" name="tanggal_panen_aktual" value="{{ date('Y-m-d') }}" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-800 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Hasil Panen Aktual (kg)</label>
                <input type="number" step="0.1" name="hasil_aktual_kg" id="inputHasilAktual" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-800 text-sm">
            </div>

            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="tutupModalPanen()" class="flex-1 py-3 rounded-2xl bg-stone-100 text-stone-700 font-bold text-sm cursor-pointer">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-2xl bg-emerald-700 text-white font-bold text-sm cursor-pointer">Simpan Panen</button>
            </div>
        </form>
    </div>
</div>

<script>
    function hitungPreview() {
        const select = document.getElementById('pilihKomoditas');
        const opt = select.options[select.selectedIndex];
        const nama = opt ? opt.dataset.nama : '';
        const sektor = opt ? opt.dataset.sektor : 'darat';
        const kapasitas = parseFloat(document.getElementById('inputKapasitas').value) || 0;
        const satuan = document.getElementById('inputSatuan').value;
        const tglMulai = document.getElementById('inputTanggalMulai').value;

        let luasM2 = satuan === 'hektar' ? kapasitas * 10000 : (satuan === 'rante' ? kapasitas * 400 : kapasitas);
        let hari = 70;
        let hasil = luasM2 * 1.0;

        if (sektor === 'ternak') {
            const isAyam = nama.includes('ayam') || nama.includes('bebek');
            hari = isAyam ? 35 : 90;
            hasil = isAyam ? kapasitas * 1.85 : kapasitas * 28;
        } else if (sektor === 'laut') {
            hari = 75;
            hasil = satuan === 'kolam' ? kapasitas * 450 : luasM2 * 2.5;
        } else {
            if (nama.includes('padi') || nama.includes('beras')) { hari = 105; hasil = luasM2 * 0.65; }
            else if (nama.includes('jagung')) { hari = 95; hasil = luasM2 * 0.85; }
            else if (nama.includes('cabai')) { hari = 75; hasil = luasM2 * 1.2; }
            else if (nama.includes('bawang')) { hari = 65; hasil = luasM2 * 1.4; }
        }

        document.getElementById('previewHasil').textContent = '~' + Math.round(hasil).toLocaleString('id-ID') + ' kg';

        if (tglMulai) {
            const dt = new Date(tglMulai);
            dt.setDate(dt.getDate() + hari);
            document.getElementById('previewTanggal').textContent = 'Perkiraan Panen: ' + dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ` (+${hari} hari)`;
        }
    }

    function bukaModalPanen(id, nama, estimasi) {
        document.getElementById('judulModalPanen').textContent = 'Realisasi Panen: ' + nama;
        document.getElementById('formSelesaiPanen').action = `/siklus/${id}/selesai`;
        document.getElementById('inputHasilAktual').value = estimasi;
        const modal = document.getElementById('modalPanen');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalPanen() {
        const modal = document.getElementById('modalPanen');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    hitungPreview();
</script>

@endsection