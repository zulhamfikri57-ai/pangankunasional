@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">📊 Laporan Laba-Rugi & Analitik Komparasi Panen</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Visualisasi performa modal, omzet, komposisi biaya, dan realisasi tonase antar siklus</p>
    </div>
    <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-900 text-white text-xs font-bold w-fit cursor-pointer">
        🖨️ Cetak / Simpan PDF
    </button>
</div>

<!-- RINGKASAN KUMULATIF SEMUA SIKLUS -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded-3xl border border-stone-100 shadow-xs">
        <span class="text-xs font-bold text-stone-500">Akumulasi Modal Siklus</span>
        <p class="font-extrabold text-xl text-rose-600 mt-1">Rp {{ number_format($totalModalSemua, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white p-5 rounded-3xl border border-stone-100 shadow-xs">
        <span class="text-xs font-bold text-stone-500">Akumulasi Omzet Panen</span>
        <p class="font-extrabold text-xl text-emerald-700 mt-1">Rp {{ number_format($totalOmzetSemua, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white p-5 rounded-3xl border border-stone-100 shadow-xs">
        <span class="text-xs font-bold text-stone-500">Total Laba Bersih Usaha</span>
        <p class="font-extrabold text-xl {{ $totalLabaSemua >= 0 ? 'text-emerald-800' : 'text-rose-700' }} mt-1">
            Rp {{ number_format($totalLabaSemua, 0, ',', '.') }}
        </p>
    </div>
</div>

@if($siklusLaporan->count() > 0)
<!-- BAGIAN GRAFIK INTERAKTIF (MODUL 3) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <!-- Grafik 1: Modal vs Omzet vs Laba per Siklus -->
    <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
        <div class="mb-4">
            <h3 class="font-extrabold text-sm sm:text-base text-stone-800">📈 Perbandingan Modal, Omzet & Laba Bersih per Siklus</h3>
            <p class="text-xs text-stone-500">Evaluasi pertumbuhan keuntungan antar periode tanam/ternak/tangkap</p>
        </div>
        <div class="relative h-64 sm:h-72">
            <canvas id="chartFinansialSiklus"></canvas>
        </div>
    </div>

    <!-- Grafik 2: Komposisi Pengeluaran Modal -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
        <div class="mb-4">
            <h3 class="font-extrabold text-sm sm:text-base text-stone-800">🍩 Struktur Biaya Modal</h3>
            <p class="text-xs text-stone-500">Proporsi pengeluaran terbesar usaha Anda</p>
        </div>
        @if($chartKategoriLabels->count() > 0)
            <div class="relative h-64 sm:h-72 flex items-center justify-center">
                <canvas id="chartKomposisiModal"></canvas>
            </div>
        @else
            <div class="h-64 flex items-center justify-center text-center px-4">
                <p class="text-xs text-stone-400">Belum ada data pengeluaran modal untuk dipetakan.</p>
            </div>
        @endif
    </div>
</div>

<!-- Grafik 3: Akurasi Target Estimasi vs Hasil Aktual -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
    <div class="mb-4">
        <h3 class="font-extrabold text-sm sm:text-base text-stone-800">🌾 Realisasi Produktivitas: Target Estimasi vs Hasil Aktual (kg)</h3>
        <p class="text-xs text-stone-500">Perbandingan proyeksi awal sistem terhadap bobot panen aktual di lapangan</p>
    </div>
    <div class="relative h-60 sm:h-68">
        <canvas id="chartProduktivitasPanen"></canvas>
    </div>
</div>
@endif

<!-- TABEL KOMPARASI ANTAR SIKLUS PANEN -->
<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6 overflow-x-auto">
    <h3 class="font-extrabold text-base text-stone-800 mb-4">⚖️ Tabel Rincian Komparasi Antar Siklus Produksi</h3>

    @if($siklusLaporan->count() > 0)
    <table class="w-full text-left border-collapse min-w-[760px]">
        <thead>
            <tr class="border-b border-stone-200 text-[11px] font-extrabold text-stone-500 uppercase">
                <th class="py-3 px-3">Siklus & Komoditas</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-3">Hasil Panen (Est / Aktual)</th>
                <th class="py-3 px-3">Total Modal</th>
                <th class="py-3 px-3">HPP / kg</th>
                <th class="py-3 px-3">Omzet Jual</th>
                <th class="py-3 px-3">Laba Bersih (ROI)</th>
                <th class="py-3 px-3">Tren vs Siklus Sebelumnya</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 text-xs">
            @php $labaSebelumnya = null; @endphp
            @foreach($siklusLaporan as $sk)
            @php
                $selisihLaba = $labaSebelumnya !== null ? ($sk->laba_bersih - $labaSebelumnya) : null;
            @endphp
            <tr class="hover:bg-stone-50/80">
                <td class="py-3.5 px-3">
                    <p class="font-extrabold text-stone-800">{{ $sk->komoditas->ikon ?? '🌱' }} {{ $sk->nama_siklus }}</p>
                    <p class="text-[11px] text-stone-500">Mulai: {{ $sk->tanggal_mulai->format('d M Y') }}</p>
                </td>
                <td class="py-3.5 px-3">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $sk->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                        {{ $sk->status }}
                    </span>
                </td>
                <td class="py-3.5 px-3">
                    <span class="text-stone-500">{{ number_format($sk->estimasi_hasil_kg, 0, ',', '.') }} kg</span> /
                    <strong class="text-stone-800">{{ $sk->hasil_aktual_kg ? number_format($sk->hasil_aktual_kg, 0, ',', '.') . ' kg' : 'Berjalan' }}</strong>
                </td>
                <td class="py-3.5 px-3 font-bold text-rose-600">
                    Rp {{ number_format($sk->total_modal, 0, ',', '.') }}
                </td>
                <td class="py-3.5 px-3 font-semibold text-stone-700">
                    Rp {{ number_format($sk->hpp_per_kg, 0, ',', '.') }}/kg
                </td>
                <td class="py-3.5 px-3 font-bold text-emerald-700">
                    Rp {{ number_format($sk->total_omzet, 0, ',', '.') }}
                </td>
                <td class="py-3.5 px-3">
                    <p class="font-extrabold {{ $sk->laba_bersih >= 0 ? 'text-emerald-800' : 'text-rose-700' }}">
                        Rp {{ number_format($sk->laba_bersih, 0, ',', '.') }}
                    </p>
                    <span class="text-[10px] font-bold text-stone-500">ROI: {{ $sk->roi_persen }}%</span>
                </td>
                <td class="py-3.5 px-3">
                    @if($selisihLaba === null)
                        <span class="text-stone-400 italic">Siklus Awal (Baseline)</span>
                    @elseif($selisihLaba > 0)
                        <span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-bold">
                            ▲ +Rp {{ number_format($selisihLaba, 0, ',', '.') }}
                        </span>
                    @elseif($selisihLaba < 0)
                        <span class="px-2 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold">
                            ▼ -Rp {{ number_format(abs($selisihLaba), 0, ',', '.') }}
                        </span>
                    @else
                        <span class="text-stone-500 font-semibold">Stabil (Rp 0)</span>
                    @endif
                </td>
            </tr>
            @php $labaSebelumnya = $sk->laba_bersih; @endphp
            @endforeach
        </tbody>
    </table>
    @else
    <div class="text-center py-8">
        <p class="text-sm font-bold text-stone-600">Belum ada siklus produksi untuk dibandingkan.</p>
        <p class="text-xs text-stone-500 mt-1">Buat rencana siklus di menu <strong>Siklus & Jadwal Panen</strong> lalu catat modal/omzet di <strong>Pembukuan Keuangan</strong>.</p>
    </div>
    @endif
</div>

@if($siklusLaporan->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labelsSiklus = @json($chartLabelsSiklus);
    const dataModal = @json($chartModalSiklus);
    const dataOmzet = @json($chartOmzetSiklus);
    const dataLaba = @json($chartLabaSiklus);
    const dataEstimasiKg = @json($chartEstimasiKg);
    const dataAktualKg = @json($chartAktualKg);
    const labelsKategori = @json($chartKategoriLabels);
    const totalsKategori = @json($chartKategoriTotals);

    const formatRupiah = (val) => 'Rp ' + Number(val).toLocaleString('id-ID');

    // 1. Bar Chart Finansial per Siklus
    new Chart(document.getElementById('chartFinansialSiklus'), {
        type: 'bar',
        data: {
            labels: labelsSiklus,
            datasets: [
                {
                    label: 'Modal (Rp)',
                    data: dataModal,
                    backgroundColor: '#f43f5e',
                    borderRadius: 8
                },
                {
                    label: 'Omzet Jual (Rp)',
                    data: dataOmzet,
                    backgroundColor: '#10b981',
                    borderRadius: 8
                },
                {
                    label: 'Laba Bersih (Rp)',
                    data: dataLaba,
                    backgroundColor: '#0284c7',
                    borderRadius: 8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${formatRupiah(ctx.raw)}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (val) => 'Rp ' + (val / 1000).toLocaleString('id-ID') + 'k'
                    }
                }
            }
        }
    });

    // 2. Doughnut Chart Komposisi Biaya Modal
    const canvasModal = document.getElementById('chartKomposisiModal');
    if (canvasModal && labelsKategori.length > 0) {
        new Chart(canvasModal, {
            type: 'doughnut',
            data: {
                labels: labelsKategori,
                datasets: [{
                    data: totalsKategori,
                    backgroundColor: ['#059669', '#f59e0b', '#0284c7', '#e11d48', '#8b5cf6', '#64748b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${formatRupiah(ctx.raw)}`
                        }
                    }
                }
            }
        });
    }

    // 3. Bar Chart Target Estimasi vs Hasil Panen Aktual
    new Chart(document.getElementById('chartProduktivitasPanen'), {
        type: 'bar',
        data: {
            labels: labelsSiklus,
            datasets: [
                {
                    label: 'Target Estimasi Awal (kg)',
                    data: dataEstimasiKg,
                    backgroundColor: '#d97706',
                    borderRadius: 8
                },
                {
                    label: 'Hasil Panen Aktual (kg)',
                    data: dataAktualKg,
                    backgroundColor: '#047857',
                    borderRadius: 8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('id-ID')} kg`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (val) => Number(val).toLocaleString('id-ID') + ' kg'
                    }
                }
            }
        }
    });
</script>
@endif

@endsection