@extends('layouts.app')
@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .custom-pin {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            border: 2.5px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
            font-size: 16px;
        }
    </style>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
        <div>
            <h2 class="fluid-title font-extrabold text-stone-800">🏪 Pasar & Jual Beli Pangan</h2>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Peta sebaran pasar tradisional, TPI, dan titik jual langsung
                dari petani, peternak, serta nelayan</p>
        </div>
        <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit">
            {{ $konfig['ikon'] }} {{ $konfig['label_peran'] }}
        </span>
    </div>

    <!-- PETA INTERAKTIF PASAR & TITIK JUAL BELI PANGAN -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/80 shadow-xs mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="font-extrabold text-base text-stone-800">🗺️ Peta Titik Pasar & Lokasi Jual Beli Pangan</h3>
                <p class="text-xs text-stone-500">Klik pin <strong>🏛️ Pasar/TPI</strong> untuk melihat info pasar atau klik
                    pin <strong>🌾 Produk</strong> untuk membeli langsung</p>
            </div>

            <!-- FILTER LAYER PETA -->
            <div class="flex flex-wrap items-center gap-1.5 bg-stone-100 p-1 rounded-2xl w-fit">
                <button type="button" id="btnFilterSemua" onclick="filterPetaPasar('semua')"
                    class="px-3 py-1.5 rounded-xl bg-stone-800 text-white text-xs font-bold transition cursor-pointer">
                    Semua ({{ count($titikPasar) + $pasarListings->whereNotNull('latitude')->count() }})
                </button>
                <button type="button" id="btnFilterPasar" onclick="filterPetaPasar('pasar')"
                    class="px-3 py-1.5 rounded-xl text-stone-700 hover:bg-white text-xs font-bold transition cursor-pointer">
                    🏛️ Pasar & TPI ({{ count($titikPasar) }})
                </button>
                <button type="button" id="btnFilterProduk" onclick="filterPetaPasar('produk')"
                    class="px-3 py-1.5 rounded-xl text-stone-700 hover:bg-white text-xs font-bold transition cursor-pointer">
                    🌾 Penjual Panen ({{ $pasarListings->whereNotNull('latitude')->count() }})
                </button>
            </div>
        </div>

        <!-- LEGENDA WARNA PIN -->
        <div class="flex flex-wrap items-center gap-4 text-[11px] font-bold text-stone-600 mb-3">
            <span class="flex items-center gap-1.5">
                <span class="w-4 h-4 rounded-full bg-amber-500 inline-block border border-white shadow-xs"></span>
                Pasar Tradisional / TPI / Sentra Pangan
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-4 h-4 rounded-full bg-emerald-600 inline-block border border-white shadow-xs"></span>
                Titik Jual Hasil Panen / Ternak / Nelayan
            </span>
        </div>

        <div id="mapPasarUtama" class="w-full h-80 sm:h-96 rounded-2xl border border-stone-200 z-10"></div>
    </div>

    @auth
        <!-- FORM PUBLIKASI HASIL PANEN / PRODUK -->
        <div id="formPublikasiJual" class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <h3 class="font-extrabold text-base text-stone-800">📢 Publikasikan Hasil Panen / Stok ke Pasar</h3>
                <span class="text-xs font-bold text-emerald-700">Stok Aktif Anda: {{ $listingAktif->count() }} Produk (Potensi
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }})</span>
            </div>

            <form action="{{ route('jual.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- PILIHAN SUMBER SIKLUS (OPSIONAL - AUTOFILL DATA PANEN) -->
                <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80">
                    <label class="block text-xs font-extrabold text-emerald-950 mb-1">
                        🌱 Sumber Siklus Produksi (Opsional — Otomatis Isi Komoditas & Stok)
                    </label>
                    <select id="selectSiklusJual" name="siklus_produksi_id" onchange="terapkanDataSiklus()"
                        class="w-full px-4 py-2.5 rounded-xl border border-emerald-300 bg-white text-sm font-semibold text-stone-800">
                        <option value="">-- Tanpa Siklus Spesifik (Input Manual / Tangkapan Harian / Olahan) --</option>
                        @foreach($siklusAktif as $sk)
                            @php
                                $stokSiklus = $sk->status === 'selesai'
                                    ? ($sk->hasil_aktual_kg ?? $sk->estimasi_hasil_kg)
                                    : $sk->estimasi_hasil_kg;
                            @endphp
                            <option value="{{ $sk->id }}" data-komoditas="{{ $sk->komoditas_id }}" data-stok="{{ $stokSiklus }}"
                                data-status="{{ $sk->status }}"
                                data-estimasi="{{ $sk->estimasi_tanggal_panen ? $sk->estimasi_tanggal_panen->format('Y-m-d') : '' }}"
                                data-alamat="{{ $sk->alamat_lahan ?? '' }}" data-lat="{{ $sk->latitude ?? '' }}"
                                data-lng="{{ $sk->longitude ?? '' }}">
                                {{ $sk->status === 'selesai' ? '✅ [Siap Panen]' : '⏳ [Berjalan]' }} {{ $sk->nama_siklus }} —
                                {{ $sk->komoditas->nama ?? '' }} ({{ number_format($stokSiklus, 0, ',', '.') }} kg)
                            </option>
                        @endforeach
                    </select>
                    <p id="infoAutofillSiklus" class="text-[11px] text-emerald-800 font-semibold mt-1.5 hidden"></p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Komoditas</label>
                        <select id="inputKomoditasJual" name="komoditas_id" required
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                            @foreach($semuaKomoditas as $km)
                                <option value="{{ $km->id }}" {{ (string) request('komoditas_id') === (string) $km->id ? 'selected' : '' }}>
                                    {{ $km->ikon ?? '🌱' }} {{ $km->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Stok Tersedia (kg / unit)</label>
                        <input type="number" step="0.1" id="inputStokJual" name="stok_kg" value="{{ request('stok_kg') }}"
                            placeholder="Contoh: 150" required
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Harga Jual per kg (Rp)</label>
                        <input type="text" name="harga_jual_per_kg" data-rupiah placeholder="Contoh: 28.000" required
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Jenis Penjualan</label>
                        <select id="jenisPenjualan" name="jenis_penjualan" onchange="toggleEstimasiSiap()"
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                            <option value="ready_stock">✅ Ready Stock (Siap Angkut)</option>
                            <option value="preorder">⏳ Pre-Order (Akan Panen)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-600 mb-1">Nama Pasar / Lokasi Titik Jual</label>
                        <input type="text" id="inputLokasiJual" name="lokasi_jual"
                            placeholder="Contoh: Pasar Raya Padang / Lahan Lakitan" required
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
                    </div>

                    <div id="wrapEstimasiSiap" class="hidden">
                        <label class="block text-xs font-bold text-stone-600 mb-1">Estimasi Tanggal Siap Panen</label>
                        <input type="date" id="inputEstimasiSiap" name="estimasi_siap"
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
                    </div>
                </div>

                <!-- KOORDINAT LOKASI JUAL -->
                <div
                    class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-xs text-stone-600">
                        <p class="font-extrabold text-stone-800">📍 Koordinat Titik Jual di Peta</p>
                        <p class="text-[11px] text-stone-500">Klik tanda <strong>🏛️ Pasar</strong> di peta atas lalu tekan
                            <em>"Jual di Pasar Ini"</em>, atau klik langsung titik lokasi Anda di peta.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" id="jualLat" name="latitude" placeholder="Latitude" readonly
                            class="w-28 px-3 py-2 rounded-lg bg-white border border-stone-200 text-xs">
                        <input type="text" id="jualLng" name="longitude" placeholder="Longitude" readonly
                            class="w-28 px-3 py-2 rounded-lg bg-white border border-stone-200 text-xs">
                        <button type="button" onclick="gunakanGpsJual()"
                            class="px-3 py-2 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-900 text-xs font-bold cursor-pointer">
                            🎯 GPS Saya
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="px-6 py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
                    Publikasikan ke Pasar PanganKu →
                </button>
            </form>
        </div>
    @endauth

    <!-- KATALOG PRODUK YANG SEDANG DIJUAL DI PASAR -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
        <h3 class="font-extrabold text-base text-stone-800 mb-4">🛒 Katalog Hasil Pangan Tersedia
            ({{ $pasarListings->count() }})</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($pasarListings as $item)
                <div class="p-4 rounded-2xl border border-stone-200/80 bg-stone-50/50 flex flex-col justify-between gap-3">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $item->jenis_penjualan === 'ready_stock' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                                {{ $item->jenis_penjualan === 'ready_stock' ? '✅ Ready Stock' : '⏳ Pre-Order' }}
                            </span>
                            <span class="text-[11px] font-bold text-stone-500">
                                Stok: {{ number_format($item->stok_kg, 0, ',', '.') }} kg
                            </span>
                        </div>

                        <h4 class="font-extrabold text-base text-stone-800 mt-2">
                            {{ $item->komoditas->ikon ?? '🌾' }} {{ $item->komoditas->nama ?? 'Komoditas' }}
                        </h4>
                        <p class="font-extrabold text-lg text-emerald-700 mt-0.5">
                            Rp {{ number_format($item->harga_jual_per_kg, 0, ',', '.') }} <span
                                class="text-xs font-normal text-stone-500">/ kg</span>
                        </p>

                        <p class="text-xs text-stone-600 mt-2">
                            👤 Penjual: <strong>{{ $item->user->name ?? 'Mitra Pangan' }}</strong><br>
                            📍 Lokasi: {{ $item->lokasi_jual }}
                            @if($item->siklus)
                                <br>🌱 Sumber: <span class="font-semibold text-emerald-800">{{ $item->siklus->nama_siklus }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="pt-2 border-t border-stone-200/70 flex items-center justify-between gap-2">
                        @if($item->latitude && $item->longitude)
                            <button type="button" onclick="fokusKePeta({{ $item->latitude }}, {{ $item->longitude }})"
                                class="px-3 py-1.5 rounded-xl bg-stone-200/80 hover:bg-stone-300 text-stone-800 text-xs font-bold cursor-pointer">
                                🗺️ Lihat di Peta
                            </button>
                        @else
                            <span class="text-[11px] text-stone-400">Tanpa pin peta</span>
                        @endif

                        @if(auth()->id() === $item->user_id)
                            <div class="flex items-center gap-1.5">
                                <form action="{{ route('jual.update', $item->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="aksi" value="terjual">
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer">
                                        ✓ Tandai Terjual
                                    </button>
                                </form>
                                <form action="{{ route('jual.destroy', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus listing ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold cursor-pointer">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        @else
                            <a href="https://wa.me/?text={{ urlencode('Halo ' . ($item->user->name ?? 'Mitra') . ', saya tertarik membeli ' . ($item->komoditas->nama ?? 'komoditas') . ' sebanyak ' . $item->stok_kg . ' kg di PanganKu Nasional.') }}"
                                target="_blank"
                                class="px-3 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">
                                💬 Hubungi Penjual
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-full text-xs text-stone-500 py-6 text-center">Belum ada hasil panen yang dipublikasikan di
                    pasar saat ini.</p>
            @endforelse
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const titikPasarData = @json($titikPasar);
        const produkListingsData = @json($pasarListings);

        const mapPasar = L.map('mapPasarUtama').setView([-1.05, 100.42], 9);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapPasar);

        const layerPasar = L.layerGroup().addTo(mapPasar);
        const layerProduk = L.layerGroup().addTo(mapPasar);
        let markerPilihanUser = null;

        // 1. Render Pin Pasar Tradisional / TPI / Sentra Jual Beli
        titikPasarData.forEach((pasar) => {
            const iconPasar = L.divIcon({
                className: '',
                html: `<div class="custom-pin bg-amber-500 text-white w-9 h-9">🏛️</div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            const marker = L.marker([pasar.lat, pasar.lng], { icon: iconPasar }).addTo(layerPasar);
            marker.bindPopup(`
                <div style="min-width: 200px; font-family: sans-serif;">
                    <span style="display:inline-block; background:#fef3c7; color:#92400e; font-size:10px; font-weight:800; padding:2px 8px; border-radius:99px; text-transform:uppercase;">
                        ${pasar.jenis}
                    </span>
                    <h4 style="margin:6px 0 4px; font-weight:800; font-size:13px; color:#1c1917;">${pasar.nama}</h4>
                    <p style="margin:0 0 4px; font-size:11px; color:#57534e;">🕒 ${pasar.jam_operasional}</p>
                    <p style="margin:0 0 8px; font-size:11px; color:#57534e;">🧺 Komoditas: <strong>${pasar.komoditas_utama}</strong></p>
                    <button type="button" onclick="pilihPasarUntukJual('${pasar.nama.replace(/'/g, "\\'")}', ${pasar.lat}, ${pasar.lng})"
                        style="width:100%; background:#047857; color:#fff; border:none; padding:6px 10px; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer;">
                        📍 Jual Hasil di Pasar Ini
                    </button>
                </div>
            `);
        });

        // 2. Render Pin Penjual / Hasil Panen Aktif
        produkListingsData.forEach((item) => {
            if (!item.latitude || !item.longitude) return;

            const ikonKomoditas = item.komoditas?.ikon || '🌾';
            const namaKomoditas = item.komoditas?.nama || 'Komoditas';
            const harga = Number(item.harga_jual_per_kg).toLocaleString('id-ID');

            const iconProduk = L.divIcon({
                className: '',
                html: `<div class="custom-pin bg-emerald-600 text-white w-9 h-9">${ikonKomoditas}</div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            const marker = L.marker([item.latitude, item.longitude], { icon: iconProduk }).addTo(layerProduk);
            marker.bindPopup(`
                <div style="min-width: 190px; font-family: sans-serif;">
                    <span style="display:inline-block; background:#d1fae5; color:#065f46; font-size:10px; font-weight:800; padding:2px 8px; border-radius:99px;">
                        ${item.jenis_penjualan === 'ready_stock' ? 'READY STOCK' : 'PRE-ORDER'}
                    </span>
                    <h4 style="margin:6px 0 2px; font-weight:800; font-size:13px; color:#1c1917;">${ikonKomoditas} ${namaKomoditas}</h4>
                    <p style="margin:0 0 4px; font-weight:800; font-size:13px; color:#047857;">Rp ${harga} / kg</p>
                    <p style="margin:0; font-size:11px; color:#57534e;">Stok: ${item.stok_kg} kg • ${item.lokasi_jual}</p>
                </div>
            `);
        });

        // Klik bebas di peta untuk menandai titik jual penjual
        mapPasar.on('click', function (e) {
            const inputLat = document.getElementById('jualLat');
            const inputLng = document.getElementById('jualLng');
            if (!inputLat || !inputLng) return;

            inputLat.value = e.latlng.lat.toFixed(6);
            inputLng.value = e.latlng.lng.toFixed(6);

            if (markerPilihanUser) mapPasar.removeLayer(markerPilihanUser);
            markerPilihanUser = L.marker([e.latlng.lat, e.latlng.lng]).addTo(mapPasar)
                .bindPopup('📍 Titik lokasi penjualan Anda').openPopup();
        });

        function pilihPasarUntukJual(namaPasar, lat, lng) {
            const inputLokasi = document.getElementById('inputLokasiJual');
            const inputLat = document.getElementById('jualLat');
            const inputLng = document.getElementById('jualLng');

            if (!inputLokasi) {
                alert('Silakan masuk (login) terlebih dahulu untuk mempublikasikan hasil panen di pasar ini.');
                return;
            }

            inputLokasi.value = namaPasar;
            inputLat.value = lat.toFixed(6);
            inputLng.value = lng.toFixed(6);

            document.getElementById('formPublikasiJual').scrollIntoView({ behavior: 'smooth' });
        }

        function filterPetaPasar(mode) {
            const btns = ['btnFilterSemua', 'btnFilterPasar', 'btnFilterProduk'];
            btns.forEach(id => {
                const el = document.getElementById(id);
                el.classList.remove('bg-stone-800', 'text-white');
                el.classList.add('text-stone-700');
            });

            if (mode === 'semua') {
                mapPasar.addLayer(layerPasar);
                mapPasar.addLayer(layerProduk);
                document.getElementById('btnFilterSemua').classList.add('bg-stone-800', 'text-white');
            } else if (mode === 'pasar') {
                mapPasar.addLayer(layerPasar);
                mapPasar.removeLayer(layerProduk);
                document.getElementById('btnFilterPasar').classList.add('bg-stone-800', 'text-white');
            } else {
                mapPasar.removeLayer(layerPasar);
                mapPasar.addLayer(layerProduk);
                document.getElementById('btnFilterProduk').classList.add('bg-stone-800', 'text-white');
            }
        }

        function fokusKePeta(lat, lng) {
            mapPasar.setView([lat, lng], 15);
            document.getElementById('mapPasarUtama').scrollIntoView({ behavior: 'smooth' });
        }

        function gunakanGpsJual() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition((pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                document.getElementById('jualLat').value = lat.toFixed(6);
                document.getElementById('jualLng').value = lng.toFixed(6);
                mapPasar.setView([lat, lng], 15);

                if (markerPilihanUser) mapPasar.removeLayer(markerPilihanUser);
                markerPilihanUser = L.marker([lat, lng]).addTo(mapPasar)
                    .bindPopup('📍 Lokasi GPS Anda saat ini').openPopup();
            });
        }

        function toggleEstimasiSiap() {
            const jenis = document.getElementById('jenisPenjualan').value;
            const wrap = document.getElementById('wrapEstimasiSiap');
            if (jenis === 'preorder') {
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }
        }

        function terapkanDataSiklus() {
            const select = document.getElementById('selectSiklusJual');
            const opt = select.options[select.selectedIndex];
            const info = document.getElementById('infoAutofillSiklus');

            if (!select.value) {
                info.classList.add('hidden');
                return;
            }

            const komoditasId = opt.dataset.komoditas;
            const stok = opt.dataset.stok;
            const status = opt.dataset.status;
            const estimasi = opt.dataset.estimasi;
            const alamat = opt.dataset.alamat;
            const lat = opt.dataset.lat;
            const lng = opt.dataset.lng;

            if (komoditasId) document.getElementById('inputKomoditasJual').value = komoditasId;
            if (stok) document.getElementById('inputStokJual').value = stok;

            const jenisSelect = document.getElementById('jenisPenjualan');
            if (status === 'selesai') {
                jenisSelect.value = 'ready_stock';
            } else {
                jenisSelect.value = 'preorder';
                if (estimasi) document.getElementById('inputEstimasiSiap').value = estimasi;
            }
            toggleEstimasiSiap();

            if (alamat && !document.getElementById('inputLokasiJual').value) {
                document.getElementById('inputLokasiJual').value = alamat;
            }
            if (lat && lng) {
                document.getElementById('jualLat').value = Number(lat).toFixed(6);
                document.getElementById('jualLng').value = Number(lng).toFixed(6);
            }

            info.textContent = `✓ Data komoditas & stok (${stok} kg) otomatis diisi dari siklus terpilih. Anda tetap dapat menyesuaikan angkanya sebelum publikasi.`;
            info.classList.remove('hidden');
        }
    </script>

@endsection