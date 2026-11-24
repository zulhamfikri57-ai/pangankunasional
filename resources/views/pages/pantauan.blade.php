@extends('layouts.app')
@section('content')

    <div class="mb-6">
        <h2 class="fluid-title font-extrabold text-stone-800">🔔 Pantauan & Alert Harga Komoditas</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Atur batas harga ideal untuk mendapatkan tanda otomatis saat
            waktu terbaik menjual panen atau membeli stok</p>
    </div>

    <!-- FORM TAMBAH ALERT HARGA BARU -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs mb-6">
        <h3 class="font-extrabold text-base text-stone-800 mb-4">➕ Buat Aturan Alert Harga Baru</h3>

        <form action="{{ route('pantauan.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            @csrf

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Komoditas</label>
                <select name="komoditas_id" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    @foreach($semuaKomoditas as $km)
                        <option value="{{ $km->id }}">
                            {{ $km->ikon ?? '🌱' }} {{ $km->nama }} (Saat ini: Rp
                            {{ number_format($km->harga_sekarang, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Kondisi Pemicu</label>
                <select name="kondisi" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                    <option value="naik_di_atas">📈 Harga Naik di Atas (Waktu Jual)</option>
                    <option value="turun_di_bawah">📉 Harga Turun di Bawah (Waktu Beli)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Target Harga (Rp / kg)</label>
                <input type="text" name="target_harga" data-rupiah placeholder="Contoh: 25.000" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <button type="submit"
                class="w-full py-3 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
                Aktifkan Alert →
            </button>
        </form>
    </div>

    <!-- DAFTAR ALERT AKTIF MILIK PENGGUNA -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-100 shadow-xs">
        <h3 class="font-extrabold text-base text-stone-800 mb-4">Daftar Aturan Alert Anda ({{ $alerts->count() }})</h3>

        <div class="divide-y divide-stone-100">
            @forelse($alerts as $al)
                @php
                    $hargaKini = $al->komoditas->harga_sekarang ?? 0;
                    $terpicu = $al->is_aktif && (
                        ($al->kondisi === 'naik_di_atas' && $hargaKini >= $al->target_harga) ||
                        ($al->kondisi === 'turun_di_bawah' && $hargaKini <= $al->target_harga)
                    );
                @endphp
                <div
                    class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ !$al->is_aktif ? 'opacity-60' : '' }}">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base">{{ $al->komoditas->ikon ?? '🔔' }}</span>
                            <span class="font-extrabold text-sm text-stone-800">{{ $al->komoditas->nama ?? 'Komoditas' }}</span>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $al->kondisi === 'naik_di_atas' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                                {{ $al->kondisi === 'naik_di_atas' ? 'Naik ≥' : 'Turun ≤' }} Rp
                                {{ number_format($al->target_harga, 0, ',', '.') }}
                            </span>

                            @if($terpicu)
                                <span
                                    class="px-2.5 py-0.5 rounded-full bg-amber-400 text-stone-950 text-[10px] font-extrabold animate-pulse">
                                    ⚡ TARGET TERCAPAI!
                                </span>
                            @elseif(!$al->is_aktif)
                                <span class="px-2.5 py-0.5 rounded-full bg-stone-200 text-stone-600 text-[10px] font-bold">
                                    Dijeda
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-stone-500 mt-1">
                            Harga Pasar Saat Ini: <strong class="text-stone-700">Rp {{ number_format($hargaKini, 0, ',', '.') }}
                                / {{ $al->komoditas->satuan ?? 'kg' }}</strong>
                        </p>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <form action="{{ route('pantauan.toggle', $al->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer {{ $al->is_aktif ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800' }}">
                                {{ $al->is_aktif ? '⏸️ Jeda' : '▶️ Aktifkan' }}
                            </button>
                        </form>

                        <button type="button" onclick='bukaModalEditAlert(@json($al))'
                            class="px-2.5 py-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 text-[11px] font-bold transition cursor-pointer">
                            ✏️ Edit
                        </button>

                        <form action="{{ route('pantauan.destroy', $al->id) }}" method="POST"
                            onsubmit="return confirm('Hapus alert harga ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold transition cursor-pointer">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-xs text-stone-500 py-6 text-center">Belum ada aturan alert harga yang Anda buat.</p>
            @endforelse
        </div>
    </div>

    <!-- MODAL EDIT ALERT HARGA -->
    <div id="modalEditAlert" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-extrabold text-base text-stone-800">✏️ Edit Target Alert Harga</h3>
                <button type="button" onclick="tutupModalAlert()"
                    class="text-stone-400 hover:text-stone-700 font-bold">✕</button>
            </div>

            <form id="formEditAlert" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Kondisi Pemicu</label>
                    <select id="editAlertKondisi" name="kondisi" required
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-sm font-semibold">
                        <option value="naik_di_atas">📈 Harga Naik di Atas (Waktu Jual)</option>
                        <option value="turun_di_bawah">📉 Harga Turun di Bawah (Waktu Beli)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-600 mb-1">Target Harga (Rp / kg)</label>
                    <input type="number" id="editAlertTarget" name="target_harga" required
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm font-bold">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="tutupModalAlert()"
                        class="px-4 py-2.5 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer">Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModalEditAlert(al) {
            document.getElementById('formEditAlert').action = `/pantauan/alert/${al.id}`;
            document.getElementById('editAlertKondisi').value = al.kondisi;
            document.getElementById('editAlertTarget').value = Math.round(parseFloat(al.target_harga || 0));

            const modal = document.getElementById('modalEditAlert');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function tutupModalAlert() {
            const modal = document.getElementById('modalEditAlert');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>

@endsection