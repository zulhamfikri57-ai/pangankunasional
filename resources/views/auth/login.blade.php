@extends('layouts.app')
@section('content')
    <div class="max-w-md mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-xs my-6">
        <h2 class="font-extrabold text-xl text-stone-800">🔑 Masuk ke PanganKu Nasional</h2>
        <p class="text-xs text-stone-500 mt-1">Gunakan Email atau Nomor HP yang sudah Anda daftarkan.</p>

        @if($errors->any())
            <div class="mt-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4 mt-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Email atau Nomor HP</label>
                <input type="text" name="kontak" value="{{ old('kontak') }}" placeholder="nama@gmail.com atau 081234567890"
                    required class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1">Kata Sandi</label>
                <div class="relative">
                    <input type="password" id="loginPassword" name="password" required
                        class="w-full px-4 py-3 pr-12 rounded-xl border border-stone-300 text-sm">
                    <button type="button" onclick="togglePassword('loginPassword', this)"
                        class="absolute inset-y-0 right-0 px-3.5 text-stone-500 hover:text-stone-800 text-xs font-bold cursor-pointer">
                        👁️ Lihat
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
                Masuk →
            </button>
        </form>
        <div class="flex items-center justify-between mb-1">
            <a href="{{ route('password.request') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                Lupa kata sandi?
            </a>
        </div>

        <p class="text-xs text-center text-stone-500 mt-5">
            Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-emerald-700 hover:underline">Daftar
                dengan Email / No. HP</a>
        </p>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈 Tutup';
            } else {
                input.type = 'password';
                btn.textContent = '👁️ Lihat';
            }
        }
    </script>
@endsection