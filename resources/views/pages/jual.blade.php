@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">🏪 Marketplace Pangan & Peta Lokasi Jual</h2>
        <p class="text-xs text-stone-500 mt-0.5">Temukan titik penjualan langsung produsen atau buka Pre-Order panen Anda</p>
    </div>
    <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit">
        {{ $konfig['ikon'] }} {{ $konfig['label_peran'] }}
    </span>
</div>

<!-- PETA SEBARAN TITIK PENJUALAN BAHAN PANGAN (PUBLIK) -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <div>
            <h3 class="font-extrabold text-base text-stone-800">🗺️ Peta Sebaran Lokasi Penjualan Bahan Pangan</h3>
            <p class="text-xs text-stone-500">Klik pin pada peta untuk melihat detail komoditas, harga, stok, dan status Pre-Order</p>
        </div>
        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold">
            {{ $pasarListings->count() }} Titik Penjualan Aktif
        </span>
    </div>
    <div id="mapMarketplace" class="w-full h-72 rounded-2xl border border-stone-200 z-10"></div>
</div>

@guest
<div class="mb-6 p-4 sm:p-5 rounded-3xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <p class="font-extrabold text-sm text-emerald-950">Ingin memasarkan bahan pangan atau membuka Pre-Order?</p>
        <p class="text-xs text-emerald-800 mt-0.5">Masuk ke akun Anda untuk menandai titik lokasi penjualan di peta dan mulai berjualan.</p>
    </div>
    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs text-center whitespace-nowrap">
        🔑 Masuk untuk Berjualan
    </a>
</div>
@endguest

@if($user && $user->peran_sektor !== 'konsumen')
<!-- FORM BUAT LISTING PENJUALAN / PRE-ORDER + PIN LOKASI JUAL -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">🛒 Publikasikan Penjualan / Pre-Order Baru</h3>
    <form action="{{ route('jual.store') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Pilih Komoditas -->
        <div>
            <label class="block text-xs font-bold text-stone-600 mb-2">1. Pilih Komoditas</label>
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

        <!-- Jenis Penjualan (Ready Stock / Pre-Order) & Tautkan Siklus -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">2. Jenis Penawaran</label>
                <select name="jenis_penjualan" id="jenisPenjualan" onchange="togglePreOrder()"
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    <option value="ready_stock">✅ Ready Stock (Siap Diambil/Kirim)</option>
                    <option value="preorder">⏳ Pre-Order (Pesan Sebelum Panen)</option>
                </select>
            </div>

            <div id="boxEstimasiSiap" class="hidden">
                <label class="block text-xs font-bold text-stone-600 mb-1">Perkiraan Tanggal Siap Panen</label>
                <input type="date" name="estimasi_siap" id="inputEstimasiSiap" value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            @if($siklusAktif->count() > 0)
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Tautkan ke Siklus (Opsional)</label>
                <select name="siklus_produksi_id" id="pilihSiklusJual" onchange="isiDariSiklus()"
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm">
                    <option value="">-- Tanpa Siklus --</option>
                    @foreach($siklusAktif as $sk)
                        <option value="{{ $sk->id }}"
                                data-estimasi="{{ $sk->estimasi_hasil_kg }}"
                                data-tanggal="{{ $sk->estimasi_tanggal_panen->format('Y-m-d') }}">
                            {{ $sk->nama_siklus }} (~{{ number_format($sk->estimasi_hasil_kg, 0) }} kg)
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <!-- Stok & Harga -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">3. Jumlah Stok Dijual (kg)</label>
                <input type="number" step="0.1" name="stok_kg" id="inputStokJual" placeholder="Contoh: 100" required
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">4. Harga Jual / kg (Rp)</label>
                <input type="number" name="harga_jual_per_kg" id="inputHargaJual"
                       value="{{ $komoditas->first()->harga_petani ?? 15000 }}" required
                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>
        </div>

        <!-- Pin Titik Lokasi Penjualan di Peta -->
        <div class="pt-1">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                <label class="text-xs font-bold text-stone-600">5. Tandai Titik Lokasi Penjualan / Pengambilan Barang di Peta</label>
                <button type="button" onclick="gunakanLokasiJual()" class="px-3 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-xs font-bold text-stone-700 cursor-pointer">
                    🎯 Gunakan Lokasi Saya Saat Ini
                </button>
            </div>
            <div id="mapPickerJual" class="w-full h-52 rounded-2xl border border-stone-300 z-10"></div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-2.5">
                <input type="text" name="lokasi_jual" placeholder="Alamat / Patokan Lokasi Jual (Contoh: Pasar Raya / Gudang Blok B)" required
                    class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs">
                <input type="text" name="latitude" id="jualLat" value="-0.9471000" readonly
                    class="px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-xs text-stone-600">
                <input type="text" name="longitude" id="jualLng" value="100.4172000" readonly
                    class="px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-xs text-stone-600">
            </div>
        </div>

        <button type="submit" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Publikasikan ke Marketplace →
        </button>
    </form>
</div>

<!-- DAFTAR PRODUK MILIK PENGGUNA -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <h3 class="font-extrabold text-stone-800 mb-4 text-sm sm:text-base">Daftar Penawaran Anda ({{ $listings->count() }})</h3>
    <div class="divide-y divide-stone-100">
        @forelse($listings as $l)
        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <span class="text-2xl flex-shrink-0">{{ $l->komoditas->ikon ?? '📦' }}</span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-bold text-sm text-stone-800">{{ $l->komoditas->nama ?? 'Komoditas' }}</p>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ ($l->jenis_penjualan ?? 'ready_stock') === 'preorder' ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ ($l->jenis_penjualan ?? 'ready_stock') === 'preorder' ? '⏳ Pre-Order' : '✅ Ready Stock' }}
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 mt-0.5">
                        Stok: <strong>{{ number_format($l->stok_kg, 0, ',', '.') }} kg</strong> • Lokasi: {{ $l->lokasi_jual ?? 'Belum diisi' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <p class="font-extrabold text-sm text-emerald-700 mr-2">Rp {{ number_format($l->harga_jual_per_kg, 0, ',', '.') }}/kg</p>
                @if($l->status === 'aktif')
                <form action="{{ route('jual.update', $l->id) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="aksi" value="terjual">
                    <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold cursor-pointer">✓ Terjual</button>
                </form>
                @endif
                <form action="{{ route('jual.destroy', $l->id) }}" method="POST" onsubmit="return confirm('Hapus penawaran ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold cursor-pointer">🗑️</button>
                </form>
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 py-4 text-center">Anda belum mempublikasikan penawaran jual.</p>
        @endforelse
    </div>
</div>
@endif

<!-- ETALASE MARKETPLACE PUBLIK -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
    <h3 class="font-extrabold text-stone-800 text-sm sm:text-base mb-4">🌾 Etalase Bahan Pangan Siap Beli & Pre-Order</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        @forelse($pasarListings as $itemPasar)
        <div class="p-4 rounded-2xl border border-stone-200/80 bg-stone-50/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-2xl">{{ $itemPasar->komoditas->ikon ?? '🌾' }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ ($itemPasar->jenis_penjualan ?? 'ready_stock') === 'preorder' ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ ($itemPasar->jenis_penjualan ?? 'ready_stock') === 'preorder' ? '⏳ Pre-Order' : '✅ Ready Stock' }}
                    </span>
                </div>
                <h4 class="font-extrabold text-sm text-stone-800">{{ $itemPasar->komoditas->nama ?? 'Komoditas' }}</h4>
                <p class="text-xs text-stone-600 mt-0.5">Penjual: <strong>{{ $itemPasar->user->name ?? 'Mitra' }}</strong></p>
                <p class="text-[11px] text-stone-500 mt-1">📍 {{ $itemPasar->lokasi_jual ?? ($itemPasar->user->lokasi ?? 'Lokasi belum dicantumkan') }}</p>
                @if(($itemPasar->jenis_penjualan ?? '') === 'preorder' && $itemPasar->estimasi_siap)
                    <p class="text-[11px] font-bold text-amber-800 mt-1">📅 Estimasi Siap: {{ \Carbon\Carbon::parse($itemPasar->estimasi_siap)->format('d M Y') }}</p>
                @endif
            </div>

            <div class="mt-3 pt-3 border-t border-stone-200/70 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-stone-400">Harga Jual</p>
                    <p class="font-extrabold text-sm text-emerald-700">Rp {{ number_format($itemPasar->harga_jual_per_kg, 0, ',', '.') }}/kg</p>
                </div>
                <span class="text-xs font-bold text-stone-700 bg-white px-2.5 py-1 rounded-lg border border-stone-200">
                    {{ number_format($itemPasar->stok_kg, 0, ',', '.') }} kg
                </span>
            </div>
        </div>
        @empty
        <p class="text-xs text-stone-500 col-span-full py-4 text-center">Belum ada stok aktif di marketplace saat ini.</p>
        @endforelse
    </div>
</div>

<script>
    // 1. Peta Sebaran Titik Penjualan (Publik)
    const mapMarket = L.map('mapMarketplace').setView([-0.9471, 100.4172], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapMarket);

    const dataPasar = @json($pasarListings);
    const bounds = [];

    dataPasar.forEach(item => {
        if (item.latitude && item.longitude) {
            const lat = parseFloat(item.latitude);
            const lng = parseFloat(item.longitude);
            bounds.push([lat, lng]);

            const badge = item.jenis_penjualan === 'preorder' ? '⏳ PRE-ORDER' : '✅ READY STOCK';
            const namaKomoditas = item.komoditas ? item.komoditas.nama : 'Komoditas';
            const namaPenjual = item.user ? item.user.name : 'Mitra';

            L.marker([lat, lng]).addTo(mapMarket).bindPopup(`
                <div style="font-family: sans-serif; min-width: 160px;">
                    <strong style="font-size: 13px;">${namaKomoditas}</strong><br>
                    <span style="font-size: 11px; color: #047857; font-weight: bold;">${badge}</span><br>
                    <span style="font-size: 12px;">Harga: <b>Rp ${Number(item.harga_jual_per_kg).toLocaleString('id-ID')}/kg</b></span><br>
                    <span style="font-size: 12px;">Stok: <b>${item.stok_kg} kg</b></span><br>
                    <span style="font-size: 11px; color: #555;">Penjual: ${namaPenjual}<br>📍 ${item.lokasi_jual || ''}</span>
                </div>
            `);
        }
    });

    if (bounds.length > 0) {
        mapMarket.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
    }

    // 2. Peta Picker Lokasi Jual (Untuk Produsen yang Login)
    const pickerEl = document.getElementById('mapPickerJual');
    if (pickerEl) {
        const mapPicker = L.map('mapPickerJual').setView([-0.9471, 100.4172], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapPicker);
        const markerJual = L.marker([-0.9471, 100.4172], { draggable: true }).addTo(mapPicker);

        function setKoordinatJual(lat, lng) {
            document.getElementById('jualLat').value = lat.toFixed(7);
            document.getElementById('jualLng').value = lng.toFixed(7);
        }

        mapPicker.on('click', e => {
            markerJual.setLatLng(e.latlng);
            setKoordinatJual(e.latlng.lat, e.latlng.lng);
        });

        markerJual.on('dragend', () => {
            const p = markerJual.getLatLng();
            setKoordinatJual(p.lat, p.lng);
        });

        window.gunakanLokasiJual = function() {
            if (!navigator.geolocation) return alert('GPS tidak didukung browser.');
            navigator.geolocation.getCurrentPosition(pos => {
                mapPicker.setView([pos.coords.latitude, pos.coords.longitude], 15);
                markerJual.setLatLng([pos.coords.latitude, pos.coords.longitude]);
                setKoordinatJual(pos.coords.latitude, pos.coords.longitude);
            });
        };
    }

    function togglePreOrder() {
        const jenis = document.getElementById('jenisPenjualan').value;
        document.getElementById('boxEstimasiSiap').classList.toggle('hidden', jenis !== 'preorder');
    }

    function isiDariSiklus() {
        const sel = document.getElementById('pilihSiklusJual');
        const opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('jenisPenjualan').value = 'preorder';
            togglePreOrder();
            document.getElementById('inputStokJual').value = opt.dataset.estimasi;
            document.getElementById('inputEstimasiSiap').value = opt.dataset.tanggal;
        }
    }

    function isiHargaRekomendasi(harga) {
        const input = document.getElementById('inputHargaJual');
        if (input && harga > 0) input.value = harga;
    }
</script>

@endsection