@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-xs my-6 text-center">
    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl mx-auto mb-3">🔐</div>
    <h2 class="font-extrabold text-xl text-stone-800">Verifikasi Email / Nomor HP</h2>
    <p class="text-xs text-stone-500 mt-1">
        Masukkan 6 digit kode OTP yang telah dikirim ke
        <strong class="text-stone-800">{{ str_ends_with($user->email, '@noemail.panganku.local') ? $user->no_hp : $user->email }}</strong>
    </p>

    @if(config('app.debug') && $user->kode_otp)
        <div class="mt-3 p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-900">
            Mode Pengujian Lokal: Kode OTP Anda adalah <strong>{{ $user->kode_otp }}</strong>
        </div>
    @endif

    @if($errors->any())
        <div class="mt-3 p-3 rounded-2xl bg-rose-50 text-xs text-rose-700 font-bold">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('verifikasi.otp.proses') }}" method="POST" class="space-y-4 mt-5">
        @csrf
        <input type="text" name="kode_otp" maxlength="6" placeholder="000000" required
            class="w-full px-4 py-3.5 rounded-2xl border border-stone-300 text-center font-extrabold text-2xl tracking-[0.4em]">

        <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm cursor-pointer">
            Verifikasi Akun Sekarang ✓
        </button>
    </form>

    <form action="{{ route('verifikasi.otp.resend') }}" method="POST" class="mt-4">
        @csrf
        <button type="submit" class="text-xs font-bold text-emerald-700 hover:underline cursor-pointer">
            Tidak menerima kode? Kirim ulang OTP
        </button>
    </form>
</div>
@endsection