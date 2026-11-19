@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">Panduan, Prediksi & Konsultasi</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Disesuaikan khusus untuk peran {{ $konfig['label_peran'] }}</p>
    </div>
    <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit">
        {{ $konfig['ikon'] }} {{ $konfig['label_peran'] }}
    </span>
</div>

<!-- BAGIAN 1: PANTAUAN REKOMENDASI & PREDIKSI RISIKO KE DEPAN -->
<div class="mb-8">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h3 class="font-extrabold text-sm sm:text-base text-stone-800 flex items-center gap-2">
            <span>🔮</span> Pantauan Rekomendasi & Prediksi Risiko ke Depan
        </h3>
        <span class="text-xs font-semibold text-stone-500">Sektor: {{ $konfig['label_peran'] }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($prediksiRisiko as $pred)
        <div class="p-4 sm:p-5 rounded-3xl border shadow-sm transition
            {{ $pred['level'] == 'tinggi' ? 'bg-rose-50/80 border-rose-200' : ($pred['level'] == 'sedang' ? 'bg-amber-50/80 border-amber-200' : 'bg-emerald-50/70 border-emerald-200') }}">
            
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl flex items-center justify-center text-xl sm:text-2xl flex-shrink-0
                    {{ $pred['level'] == 'tinggi' ? 'bg-rose-100' : ($pred['level'] == 'sedang' ? 'bg-amber-100' : 'bg-emerald-100') }}">
                    {{ $pred['ikon'] }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase
                            {{ $pred['level'] == 'tinggi' ? 'bg-rose-600 text-white' : ($pred['level'] == 'sedang' ? 'bg-amber-500 text-white' : 'bg-emerald-700 text-white') }}">
                            Risiko {{ $pred['level'] }}
                        </span>
                    </div>
                    <h4 class="font-extrabold text-stone-800 text-sm">{{ $pred['judul'] }}</h4>
                    <p class="text-xs text-stone-600 mt-1.5 leading-relaxed">{{ $pred['analisis'] }}</p>
                    
                    <div class="mt-3 p-3 rounded-xl bg-white/85 border border-stone-200/60">
                        <p class="text-[11px] font-bold text-emerald-900">💡 Rekomendasi Tindakan:</p>
                        <p class="text-xs text-stone-700 mt-0.5">{{ $pred['rekomendasi'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- BAGIAN 2: TANYA PAKAR PANGAN AI (SESUAI SEKTOR PENGGUNA) -->
<div class="bg-white rounded-3xl p-5 sm:p-7 shadow-sm border border-stone-100 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
        <div>
            <h3 class="font-extrabold text-base sm:text-lg text-stone-800 flex items-center gap-2">
                <span>🤖</span> Tanya Pakar AI — {{ $konfig['label_peran'] }}
            </h3>
            <p class="text-xs text-stone-500 mt-0.5">{{ $konteksPanduan['sub_pakar'] }}</p>
        </div>
        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold w-fit">
            Fokus: {{ $user->komoditas_utama ?: $konfig['label_peran'] }}
        </span>
    </div>

    <!-- Pilihan Pertanyaan Cepat Khusus Jenis Akun -->
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($konteksPanduan['prompts'] as $promptText)
        <button type="button" onclick="isiPertanyaan('{{ addslashes($promptText) }}')"
            class="px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-emerald-50 hover:text-emerald-800 text-stone-700 text-xs font-semibold transition cursor-pointer text-left">
            {{ $promptText }}
        </button>
        @endforeach
    </div>

    <!-- Form Pertanyaan -->
    <form id="formTanyaPakar" class="space-y-3">
        @csrf
        <textarea id="inputPertanyaan" rows="3" required
            placeholder="{{ $konteksPanduan['placeholder'] }}"
            class="w-full p-4 rounded-2xl border border-stone-200 text-sm focus:outline-none focus:border-emerald-600 bg-stone-50/50"></textarea>
        
        <div class="flex justify-end">
            <button type="submit" id="btnKirimTanya"
                class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm shadow-sm transition cursor-pointer flex items-center justify-center gap-2">
                <span>✨ Analisis & Beri Solusi</span>
            </button>
        </div>
    </form>

    <!-- Area Hasil Jawaban AI -->
    <div id="boxJawabanAI" class="hidden mt-5 p-4 sm:p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-lg">💡</span>
            <h4 class="font-extrabold text-sm text-emerald-900">Rekomendasi Pakar ({{ $konfig['label_peran'] }}):</h4>
        </div>
        <div id="isiJawabanAI" class="text-xs sm:text-sm text-stone-800 leading-relaxed whitespace-pre-line"></div>
    </div>
</div>

<!-- BAGIAN 3: PANDUAN TEKNIS SESUAI JENIS AKUN -->
<div>
    <h3 class="font-extrabold text-sm sm:text-base text-stone-800 mb-3">📖 {{ $konteksPanduan['judul_panduan'] }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
        @foreach ($panduans as $p)
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-stone-100 shadow-sm flex items-start gap-3.5 sm:gap-4">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-xl sm:text-2xl flex-shrink-0">
                {{ $p->ikon }}
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">{{ $p->komoditas_fokus }}</span>
                <h4 class="font-bold text-stone-800 text-sm sm:text-base">{{ $p->judul }}</h4>
                <p class="text-xs text-stone-600 mt-1 leading-relaxed">{{ $p->deskripsi }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    function isiPertanyaan(teks) {
        document.getElementById('inputPertanyaan').value = teks;
        document.getElementById('inputPertanyaan').focus();
    }

    document.getElementById('formTanyaPakar').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const pertanyaan = document.getElementById('inputPertanyaan').value.trim();
        if (!pertanyaan) return;

        const btn = document.getElementById('btnKirimTanya');
        const box = document.getElementById('boxJawabanAI');
        const isi = document.getElementById('isiJawabanAI');

        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Sedang menganalisis masalah...</span>';
        box.classList.remove('hidden');
        isi.textContent = 'Menghubungkan ke Pakar AI sektor {{ $konfig["label_peran"] }}...';

        try {
            const response = await fetch("{{ route('panduan.tanya') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ pertanyaan: pertanyaan })
            });

            const data = await response.json();
            isi.textContent = data.jawaban || 'Tidak ada respons dari server.';
        } catch (error) {
            isi.textContent = 'Gagal memproses permintaan. Pastikan koneksi internet aktif.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>✨ Analisis & Beri Solusi</span>';
        }
    });
</script>

@endsection