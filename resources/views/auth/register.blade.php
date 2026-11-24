@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-xs my-6">
    <h2 class="font-extrabold text-xl text-stone-800">🌱 Daftar Akun PanganKu Nasional</h2>
    <p class="text-xs text-stone-500 mt-1">Gunakan alamat Email aktif atau Nomor HP/WhatsApp asli untuk menerima kode verifikasi.</p>

    @if($errors->any())
        <div class="mt-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-semibold">
            @foreach($errors->all() as $err)
                <p>• {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('register.store') }}" method="POST" class="space-y-4 mt-5">
        @csrf
        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Nama Lengkap Sesuai Identitas</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Isikan Nama Lengkap" required
                class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Email Aktif atau Nomor HP (WhatsApp)</label>
            <input type="text" name="kontak" value="{{ old('kontak') }}" placeholder="nama@gmail.com atau 081234567890" required
                class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm">
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Kata Sandi (Minimal 6 karakter)</label>
            <div class="relative">
                <input type="password" id="regPassword" name="password" required
                    class="w-full px-4 py-3 pr-16 rounded-xl border border-stone-300 text-sm">
                <button type="button" onclick="togglePassword('regPassword', this)"
                    class="absolute inset-y-0 right-0 px-3.5 text-stone-500 hover:text-stone-800 text-xs font-bold cursor-pointer">
                    👁️ Lihat
                </button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-600 mb-1">Ulangi Kata Sandi</label>
            <div class="relative">
                <input type="password" id="regPasswordConfirm" name="password_confirmation" required
                    class="w-full px-4 py-3 pr-16 rounded-xl border border-stone-300 text-sm">
                <button type="button" onclick="togglePassword('regPasswordConfirm', this)"
                    class="absolute inset-y-0 right-0 px-3.5 text-stone-500 hover:text-stone-800 text-xs font-bold cursor-pointer">
                    👁️ Lihat
                </button>
            </div>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition cursor-pointer">
            Kirim Kode Verifikasi & Daftar →
        </button>
    </form>

    <p class="text-xs text-center text-stone-500 mt-5">
        Sudah punya akun terverifikasi? <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:underline">Masuk di sini</a>
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