@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto my-8 bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-xs">
    <div class="mb-5">
        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-extrabold uppercase">
            Verifikasi OTP
        </span>
        <h2 class="text-xl font-extrabold text-stone-800 mt-2">🔐 Buat Kata Sandi Baru</h2>
        <p class="text-xs text-stone-500 mt-1">
            Masukkan kode OTP 6 digit yang dikirim ke <strong>{{ $identitas }}</strong> beserta kata sandi baru Anda.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-bold">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-semibold">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-stone-700 mb-1">Kode OTP (6 Digit)</label>
            <input type="text" name="otp_code" maxlength="6" inputmode="numeric" placeholder="123456" required autofocus
                class="w-full px-4 py-3 rounded-xl border border-stone-300 text-center tracking-widest font-extrabold text-base">
        </div>

       <div>
            <label class="block text-xs font-bold text-stone-700 mb-1">Kata Sandi Baru (Minimal 6 karakter)</label>
            <div class="relative">
                <input type="password" id="resetPassBaru" name="password" placeholder="Masukkan kata sandi baru" required
                    class="w-full px-4 py-3 pr-12 rounded-xl border border-stone-300 text-sm">
                <button type="button" onclick="toggleSandiReset('resetPassBaru')"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-stone-500 hover:text-stone-800 cursor-pointer">
                    👁️
                </button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-700 mb-1">Konfirmasi Kata Sandi Baru</label>
            <div class="relative">
                <input type="password" id="resetPassKonfirmasi" name="password_confirmation" placeholder="Ulangi kata sandi baru" required
                    class="w-full px-4 py-3 pr-12 rounded-xl border border-stone-300 text-sm">
                <button type="button" onclick="toggleSandiReset('resetPassKonfirmasi')"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-stone-500 hover:text-stone-800 cursor-pointer">
                    👁️
                </button>
            </div>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Simpan Kata Sandi Baru ✓
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('password.request') }}" class="text-xs font-bold text-amber-700 hover:underline">
            Kirim ulang kode OTP
        </a>
    </div>
</div>
@endsection