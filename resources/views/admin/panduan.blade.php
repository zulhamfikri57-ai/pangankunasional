@extends('layouts.admin')
@section('content')

<h2 class="text-2xl font-extrabold text-stone-800 mb-1">Kelola Materi Panduan Teknis</h2>
<p class="text-xs sm:text-sm text-stone-500 mb-6">Tambah atau perbarui panduan budidaya, rantai dingin, pengolahan, dan tips pangan</p>

<div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200/70 shadow-xs mb-6">
    <h3 class="font-bold text-sm sm:text-base text-stone-800 mb-4">+ Tambah Panduan Baru</h3>
    <form action="{{ route('admin.panduan.store') }}" method="POST" class="space-y-3">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <select name="sektor" class="px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs sm:text-sm font-semibold">
                <option value="petani">Untuk: Petani</option>
                <option value="peternak">Untuk: Peternak</option>
                <option value="nelayan">Untuk: Nelayan</option>
                <option value="pelaku_pangan">Untuk: Pelaku Pangan</option>
                <option value="konsumen">Untuk: Konsumen</option>
            </select>
            <input type="text" name="komoditas_fokus" placeholder="Topik (misal: Pascapanen)" required class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            <input type="text" name="judul" placeholder="Judul Panduan" required class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
            <input type="text" name="ikon" placeholder="Ikon Emoji (📖)" value="📖" class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm">
        </div>
        <textarea name="deskripsi" rows="2" placeholder="Isi penjelasan panduan teknis..." required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm"></textarea>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs cursor-pointer">
            Terbitkan Panduan
        </button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($panduans as $p)
    <div class="bg-white p-5 rounded-3xl border border-stone-200/70 shadow-xs flex items-start justify-between gap-3">
        <div class="flex items-start gap-3">
            <span class="text-2xl">{{ $p->ikon }}</span>
            <div>
                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">{{ $p->sektor }} • {{ $p->komoditas_fokus }}</span>
                <h4 class="font-bold text-sm text-stone-800 mt-1">{{ $p->judul }}</h4>
                <p class="text-xs text-stone-600 mt-1">{{ $p->deskripsi }}</p>
            </div>
        </div>
        <form action="{{ route('admin.panduan.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus panduan ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 font-bold text-xs cursor-pointer">🗑️</button>
        </form>
    </div>
    @endforeach
</div>

@endsection