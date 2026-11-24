@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto my-8 bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-xs">
    <div class="mb-5">
        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-[11px] font-extrabold uppercase">
            Pemulihan Akun
        </span>
        <h2 class="text-xl font-extrabold text-stone-800 mt-2">🔑 Lupa Kata Sandi?</h2>
        <p class="text-xs text-stone-500 mt-1">
            Masukkan Email atau Nomor HP/WhatsApp yang terdaftar untuk menerima kode OTP reset kata sandi.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-semibold">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-stone-700 mb-1">Email atau Nomor HP Terdaftar</label>
            <input type="text" name="identitas" value="{{ old('identitas') }}"
                placeholder="Contoh: 081234567890 atau nama@email.com" required autofocus
                class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
        </div>

        <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Kirim Kode OTP Reset →
        </button>
    </form>

    <div class="mt-5 pt-4 border-t border-stone-100 text-center">
        <a href="{{ route('login') }}" class="text-xs font-bold text-stone-600 hover:text-emerald-700">
            ← Kembali ke Halaman Masuk
        </a>
    </div>
</div>
@endsection