@extends('layouts.app')
@section('content')

<h2 class="fluid-title font-extrabold text-stone-800 mb-5">Laporan Keuangan</h2>

<div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-100 mb-6">
    <p class="text-xs sm:text-sm text-stone-500">Ringkasan Keuangan • {{ $konfig['siklus'] }}</p>
    <p class="fluid-metric font-extrabold {{ $labaBersih >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1 break-words">
        {{ $labaBersih >= 0 ? 'UNTUNG' : 'DEFISIT' }} Rp {{ number_format(abs($labaBersih), 0, ',', '.') }}
    </p>
    <p class="text-xs font-bold text-emerald-700 mt-1">↑ Tersinkron otomatis dengan catatan transaksi</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
    <!-- Grafik Elastis -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-100">
        <h3 class="font-bold text-stone-800 mb-4 text-sm sm:text-base">Pemasukan vs Pengeluaran</h3>
        <div class="relative w-full h-60 sm:h-72">
            <canvas id="chartKeuangan"></canvas>
        </div>
    </div>

    <!-- Rincian Biaya -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-100">
        <h3 class="font-bold text-stone-800 mb-4 text-sm sm:text-base">Rincian Biaya Operasional</h3>
        <div class="space-y-4">
            @forelse($rincianBiaya as $biaya)
            @php $persen = $pengeluaran > 0 ? round(($biaya->total / $pengeluaran) * 100) : 0; @endphp
            <div>
                <div class="flex justify-between items-center gap-2 text-xs sm:text-sm font-bold mb-1">
                    <span class="truncate">{{ $biaya->kategori }}</span>
                    <span class="whitespace-nowrap">{{ $persen }}% (Rp {{ number_format($biaya->total, 0, ',', '.') }})</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2.5">
                    <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $persen }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-stone-500 py-4 text-center">Belum ada rincian biaya yang tercatat.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    new Chart(document.getElementById('chartKeuangan'), {
        type: 'bar',
        data: {
            labels: ['Mei', 'Jun', 'Jul', 'Ags'],
            datasets: [
                { label: 'Pemasukan (Juta)', data: [14, 11, 18, 9.5], backgroundColor: '#2E7D32', borderRadius: 6 },
                { label: 'Pengeluaran (Juta)', data: [7.5, 4.2, 6.8, 3.1], backgroundColor: '#F97316', borderRadius: 6 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

@endsection