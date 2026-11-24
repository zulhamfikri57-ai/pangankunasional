<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Komoditas;
use App\Models\ListingPanen;
use App\Models\SiklusProduksi;

class HargaSyncService
{
    public function sinkronkanSemua(): array
    {
        $hargaBapanas = $this->fetchHargaBapanas();
        $komoditasList = Komoditas::all();
        $terupdate = 0;

        foreach ($komoditasList as $item) {
            $hargaLama = (float) $item->harga_sekarang;

            // 1. Cek kecocokan harga dari API Bapanas (Sumber 1)
            $acuanEksternal = $this->cocokkanHargaEksternal($item->nama, $hargaBapanas)
                ?? (float) ($item->harga_acuan_pemerintah ?: $hargaLama);

            // 2. Hitung rata-rata harga dari Marketplace internal 7 hari terakhir (Sumber 2)
            $listings7Hari = ListingPanen::where('komoditas_id', $item->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->get();

            $totalKg7Hari = $listings7Hari->sum('stok_kg');
            $rataMarketplace = $totalKg7Hari > 0
                ? round($listings7Hari->sum(fn($l) => $l->stok_kg * $l->harga_jual_per_kg) / $totalKg7Hari, 0)
                : null;

            // 3. Hitung Harga Hybrid Akhir
            if ($rataMarketplace && $acuanEksternal > 0) {
                $hargaBaru = round(($rataMarketplace * 0.6) + ($acuanEksternal * 0.4), 0);
                $sumberLabel = 'Hybrid (60% Mitra Lokal + 40% Bapanas)';
            } elseif ($rataMarketplace) {
                $hargaBaru = $rataMarketplace;
                $sumberLabel = 'Rata-rata Transaksi Mitra PanganKu';
            } else {
                $hargaBaru = $acuanEksternal;
                $sumberLabel = !empty($hargaBapanas) ? 'Panel Harga Bapanas' : 'Survei Pasar Lokal';
            }

            $persenPerubahan = $hargaLama > 0
                ? round((($hargaBaru - $hargaLama) / $hargaLama) * 100, 1)
                : 0;

            // 4. Hitung Ketersediaan Stok Real-Time
            $stokReady = (float) ListingPanen::where('komoditas_id', $item->id)
                ->where('status', 'aktif')
                ->where('jenis_penjualan', 'ready_stock')
                ->sum('stok_kg');

            $stokPreorderListing = (float) ListingPanen::where('komoditas_id', $item->id)
                ->where('status', 'aktif')
                ->where('jenis_penjualan', 'preorder')
                ->sum('stok_kg');

            $stokSiklusBerjalan = (float) SiklusProduksi::where('komoditas_id', $item->id)
                ->where('status', 'berjalan')
                ->sum('estimasi_hasil_kg');

            $totalAkanPanen = $stokPreorderListing + $stokSiklusBerjalan;

            // Tentukan Status Ketersediaan Otomatis
            $statusKetersediaan = match (true) {
                $stokReady >= 500 => 'surplus',
                $stokReady >= 50 => 'terbatas',
                default => 'menipis',
            };

            $item->update([
                'harga_sekarang' => $hargaBaru,
                'harga_acuan_pemerintah' => $acuanEksternal,
                'harga_rata_marketplace' => $rataMarketplace,
                'stok_ready_kg' => $stokReady,
                'stok_preorder_kg' => $totalAkanPanen,
                'status_ketersediaan' => $statusKetersediaan,
                'persentase_perubahan' => $persenPerubahan,
                'sumber_harga' => $sumberLabel,
                'terakhir_sync' => now(),
            ]);

            $terupdate++;
        }

        return [
            'jumlah_komoditas' => $terupdate,
            'status_api' => !empty($hargaBapanas) ? 'Terhubung ke API Bapanas' : 'Mode Fallback Lokal + Marketplace',
        ];
    }

    private function fetchHargaBapanas(): array
    {
        try {
            $response = Http::withOptions(['force_ip_resolve' => 'v4'])
                ->timeout(10)
                ->get('https://panelharga.badanpangan.go.id/api/front/harga-pangan-informasi', [
                    'province_id' => 13,
                    'level_harga_id' => 3,
                ]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            // Fallback otomatis jika API pemerintah sedang tidak dapat dijangkau
        }

        return [];
    }

    private function cocokkanHargaEksternal(string $namaKomoditas, array $dataApi): ?float
    {
        if (empty($dataApi)) {
            return null;
        }

        $target = strtolower(trim($namaKomoditas));

        foreach ($dataApi as $row) {
            $namaApi = strtolower($row['name'] ?? $row['komoditas'] ?? '');
            $nilaiHarga = (float) ($row['today'] ?? $row['harga'] ?? 0);

            if ($nilaiHarga > 0 && (str_contains($namaApi, $target) || str_contains($target, $namaApi))) {
                return $nilaiHarga;
            }
        }

        return null;
    }
}