@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-xs my-4">
    <h2 class="font-extrabold text-xl text-stone-800">🛡️ Verifikasi Identitas & Upgrade Peran Usaha Pangan</h2>
    <p class="text-xs text-stone-500 mt-1">
        Untuk menjadi <strong>Petani, Peternak, atau Nelayan</strong> terverifikasi dan dapat menjual hasil panen di Marketplace, lengkapi data identitas dan lokasi usaha asli Anda.
    </p>

    @if($user->status_verifikasi_peran === 'pending')
        <div class="mt-5 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
            <p class="font-extrabold text-sm">⏳ Pengajuan Peran "{{ ucfirst($user->peran_diajukan) }}" Sedang Diverifikasi</p>
            <p class="mt-1">NIK: <strong>{{ substr($user->nik_identitas, 0, 6) }}******{{ substr($user->nik_identitas, -4) }}</strong> • Lokasi: {{ $user->alamat_usaha_riil }}</p>
            <p class="mt-1 text-[11px]">Admin sedang memeriksa keaslian dokumen Anda sebelum hak akses produsen diaktifkan.</p>
        </div>
    @elseif($user->status_verifikasi_peran === 'rejected')
        <div class="mt-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
            <p class="font-extrabold">❌ Pengajuan Sebelumnya Ditolak</p>
            <p class="mt-1">Alasan: <strong>{{ $user->catatan_admin_verifikasi }}</strong>. Silakan unggah ulang dokumen yang jelas dan valid di bawah ini.</p>
        </div>
    @elseif($user->status_verifikasi_peran === 'approved')
        <div class="mt-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-bold">
            ✅ Akun Anda sudah terverifikasi resmi sebagai {{ ucfirst($user->peran_sektor) }}.
        </div>
    @endif

    @if($errors->any())
        <div class="mt-4 p-3 rounded-2xl bg-rose-50 text-xs text-rose-700 font-bold">
            @foreach($errors->all() as $err)
                <p>• {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <form id="formUpgradePeran" action="{{ route('peran.upgrade.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4 mt-5">
        @csrf

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Peran Usaha yang Diajukan</label>
            <select name="peran_diajukan" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                <option value="petani">🌾 Petani / Pekebun (Sektor Darat)</option>
                <option value="peternak">🐄 Peternak Unggas / Ruminansia</option>
                <option value="nelayan">🐟 Nelayan / Pembudidaya Ikan</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">NIK KTP Asli (16 Digit)</label>
                <input type="text" name="nik_identitas" maxlength="16" value="{{ old('nik_identitas', $user->nik_identitas) }}" placeholder="1301xxxxxxxxxxxx" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Nomor HP / WhatsApp Aktif</label>
                <input type="text" name="no_hp_aktif" value="{{ old('no_hp_aktif', $user->no_hp) }}" placeholder="081234567890" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>
        </div>

        <!-- PILIHAN WILAYAH BERJENJANG OTOMATIS -->
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-3">
            <p class="text-xs font-extrabold text-stone-700">📍 Lokasi Lahan / Kandang / Tambak / Pelabuhan</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-stone-500 mb-1">Provinsi</label>
                    <select id="selectProvinsi" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs font-semibold">
                        <option value="">Memuat daftar provinsi...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-stone-500 mb-1">Kabupaten / Kota</label>
                    <select id="selectKabupaten" required disabled class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs font-semibold disabled:bg-stone-100">
                        <option value="">Pilih provinsi dahulu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-stone-500 mb-1">Kecamatan</label>
                    <select id="selectKecamatan" required disabled class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs font-semibold disabled:bg-stone-100">
                        <option value="">Pilih kabupaten/kota dahulu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-stone-500 mb-1">Nagari / Kelurahan / Desa</label>
                    <select id="selectKelurahan" required disabled class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs font-semibold disabled:bg-stone-100">
                        <option value="">Pilih kecamatan dahulu</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-stone-500 mb-1">Detail Jorong / Jalan / Patokan Lokasi Usaha</label>
                <input type="text" id="detailJalan" placeholder="Contoh: Jorong Pasar Lama, dekat irigasi batang kapas" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs">
            </div>

            <!-- Input tersembunyi yang otomatis menggabungkan seluruh alamat lengkap -->
            <input type="hidden" name="alamat_usaha_riil" id="alamatUsahaGabungan" value="{{ old('alamat_usaha_riil', $user->alamat_usaha_riil) }}">
            <p id="previewAlamat" class="text-[11px] text-emerald-800 font-semibold hidden"></p>
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Unggah Dokumen Verifikasi Asli (Foto KTP / Surat Keterangan Usaha / Foto Lahan & Kapal)</label>
            <input type="file" name="dokumen_verifikasi" accept=".jpg,.jpeg,.png,.pdf" required
                class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-xs bg-stone-50">
            <p class="text-[11px] text-stone-400 mt-1">Format JPG, PNG, atau PDF maksimal 4 MB.</p>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Ajukan Verifikasi Peran Usaha →
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const baseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api';
    const selProv = document.getElementById('selectProvinsi');
    const selKab = document.getElementById('selectKabupaten');
    const selKec = document.getElementById('selectKecamatan');
    const selKel = document.getElementById('selectKelurahan');
    const detailJalan = document.getElementById('detailJalan');
    const inputGabungan = document.getElementById('alamatUsahaGabungan');
    const previewAlamat = document.getElementById('previewAlamat');

    function updateAlamatGabungan() {
        const prov = selProv.options[selProv.selectedIndex]?.dataset?.nama || '';
        const kab = selKab.options[selKab.selectedIndex]?.dataset?.nama || '';
        const kec = selKec.options[selKec.selectedIndex]?.dataset?.nama || '';
        const kel = selKel.options[selKel.selectedIndex]?.dataset?.nama || '';
        const jalan = detailJalan.value.trim();

        const bagian = [jalan, kel, kec ? `Kec. ${kec}` : '', kab, prov].filter(Boolean);
        const hasil = bagian.join(', ');
        inputGabungan.value = hasil;

        if (hasil) {
            previewAlamat.textContent = 'Alamat lengkap tercatat: ' + hasil;
            previewAlamat.classList.remove('hidden');
        }
    }

    // 1. Muat Provinsi
    fetch(`${baseUrl}/provinces.json`)
        .then(res => res.json())
        .then(data => {
            selProv.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.dataset.nama = item.name;
                opt.textContent = item.name;
                selProv.appendChild(opt);
            });
        })
        .catch(() => {
            selProv.innerHTML = '<option value="">Gagal memuat wilayah (periksa koneksi internet)</option>';
        });

    // 2. Provinsi -> Kabupaten/Kota
    selProv.addEventListener('change', () => {
        selKab.innerHTML = '<option value="">Memuat kabupaten/kota...</option>';
        selKab.disabled = true;
        selKec.innerHTML = '<option value="">Pilih kabupaten/kota dahulu</option>';
        selKec.disabled = true;
        selKel.innerHTML = '<option value="">Pilih kecamatan dahulu</option>';
        selKel.disabled = true;
        updateAlamatGabungan();

        if (!selProv.value) return;

        fetch(`${baseUrl}/regencies/${selProv.value}.json`)
            .then(res => res.json())
            .then(data => {
                selKab.innerHTML = '<option value="">-- Pilih Kabupaten / Kota --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.dataset.nama = item.name;
                    opt.textContent = item.name;
                    selKab.appendChild(opt);
                });
                selKab.disabled = false;
            });
    });

    // 3. Kabupaten/Kota -> Kecamatan
    selKab.addEventListener('change', () => {
        selKec.innerHTML = '<option value="">Memuat kecamatan...</option>';
        selKec.disabled = true;
        selKel.innerHTML = '<option value="">Pilih kecamatan dahulu</option>';
        selKel.disabled = true;
        updateAlamatGabungan();

        if (!selKab.value) return;

        fetch(`${baseUrl}/districts/${selKab.value}.json`)
            .then(res => res.json())
            .then(data => {
                selKec.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.dataset.nama = item.name;
                    opt.textContent = item.name;
                    selKec.appendChild(opt);
                });
                selKec.disabled = false;
            });
    });

    // 4. Kecamatan -> Nagari/Kelurahan/Desa
    selKec.addEventListener('change', () => {
        selKel.innerHTML = '<option value="">Memuat nagari/desa...</option>';
        selKel.disabled = true;
        updateAlamatGabungan();

        if (!selKec.value) return;

        fetch(`${baseUrl}/villages/${selKec.value}.json`)
            .then(res => res.json())
            .then(data => {
                selKel.innerHTML = '<option value="">-- Pilih Nagari / Kelurahan / Desa --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.dataset.nama = item.name;
                    opt.textContent = item.name;
                    selKel.appendChild(opt);
                });
                selKel.disabled = false;
            });
    });

    selKel.addEventListener('change', updateAlamatGabungan);
    detailJalan.addEventListener('input', updateAlamatGabungan);
    document.getElementById('formUpgradePeran').addEventListener('submit', updateAlamatGabungan);
});
</script>
@endsection