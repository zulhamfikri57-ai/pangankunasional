@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <!-- Header & Filter Komoditas -->
    <div class="bg-white p-6 rounded-3xl border border-stone-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-emerald-100 text-emerald-800">
                Decision Support System (Metode SAW)
            </span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-800 mt-2">
                🧭 SPK Rekomendasi Harga & Wilayah Distribusi
            </h2>
            <p class="text-xs text-stone-500 mt-1">
                Analisis pembobotan kriteria margin harga (40%), defisit pasokan pasar (35%), dan efisiensi biaya logistik (25%).
            </p>
        </div>

        <form action="{{ route('spk.distribusi') }}" method="GET" class="flex items-center gap-2">
            <select name="komoditas_id" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-2xl border border-stone-300 bg-stone-50 text-xs font-extrabold text-stone-800">
                @foreach($daftarKomoditas as $item)
                    <option value="{{ $item->id }}" {{ $komoditasTerpilih?->id == $item->id ? 'selected' : '' }}>
                        {{ $item->nama }} ({{ ucfirst($item->sektor ?? $item->kategori ?? 'Pangan') }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($komoditasTerpilih)
    <!-- Ringkasan Parameter Komoditas -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-stone-200/80">
            <p class="text-xs text-stone-500 font-bold">Harga Dasar Tingkat Produsen</p>
            <p class="text-xl font-extrabold text-stone-800 mt-1">Rp {{ number_format($hargaProdusen, 0, ',', '.') }} <span class="text-xs font-normal">/kg</span></p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-stone-200/80">
            <p class="text-xs text-stone-500 font-bold">Harga Acuan Pasar Lokal</p>
            <p class="text-xl font-extrabold text-emerald-700 mt-1">Rp {{ number_format($komoditasTerpilih->harga_pasar, 0, ',', '.') }} <span class="text-xs font-normal">/kg</span></p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-stone-200/80">
            <p class="text-xs text-stone-500 font-bold">Total Stok Siap Distribusi</p>
            <p class="text-xl font-extrabold text-amber-700 mt-1">{{ number_format($totalStokTersedia, 0, ',', '.') }} <span class="text-xs font-normal">kg</span></p>
        </div>
    </div>

    <!-- Tabel Hasil Perankingan SAW -->
    <div class="bg-white rounded-3xl border border-stone-200/80 overflow-hidden shadow-xs">
        <div class="p-5 border-b border-stone-100">
            <h3 class="font-extrabold text-base text-stone-800">🏆 Peringkat Prioritas Tujuan Distribusi & Rekomendasi Harga Jual</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase font-extrabold">
                    <tr>
                        <th class="px-5 py-3.5">Peringkat</th>
                        <th class="px-5 py-3.5">Wilayah Tujuan</th>
                        <th class="px-5 py-3.5">Harga Pasar Tujuan</th>
                        <th class="px-5 py-3.5">Biaya Logistik</th>
                        <th class="px-5 py-3.5">Skor SAW</th>
                        <th class="px-5 py-3.5">Rekomendasi Harga Produsen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($hasilSpk as $index => $row)
                    <tr class="{{ $index === 0 ? 'bg-emerald-50/60' : 'hover:bg-stone-50' }}">
                        <td class="px-5 py-4 font-extrabold">
                            @if($index === 0)
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-700 text-white">#1 Prioritas Utama</span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg bg-stone-200 text-stone-700">#{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-bold text-stone-800">{{ $row['wilayah'] }}</td>
                        <td class="px-5 py-4">Rp {{ number_format($row['harga_pasar_tujuan'], 0, ',', '.') }}/kg</td>
                        <td class="px-5 py-4 text-rose-700">Rp {{ number_format($row['c3_logistik'], 0, ',', '.') }}/kg</td>
                        <td class="px-5 py-4 font-extrabold text-stone-900">{{ number_format($row['skor_saw'], 4) }}</td>
                        <td class="px-5 py-4">
                            <span class="font-extrabold text-emerald-800 text-sm">
                                Rp {{ number_format($row['rekomendasi_harga'], 0, ',', '.') }}/kg
                            </span>
                            <span class="block text-[11px] text-emerald-600">
                                (+Rp {{ number_format($row['estimasi_profit_bersih'], 0, ',', '.') }}/kg di atas harga dasar)
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection