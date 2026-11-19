<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — PanganKu Nasional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F7F5EE; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl p-8 shadow-lg border border-stone-100">
        <div class="text-center mb-8">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-700 text-white flex items-center justify-center text-3xl mb-3">🌱</div>
            <h1 class="text-2xl font-extrabold text-emerald-900">PanganKu Nasional</h1>
            <p class="text-sm text-stone-500 mt-1">Masuk ke dasbor usaha tani, ternak & perikanan</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3.5 rounded-2xl bg-rose-50 text-rose-700 text-xs font-bold">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', 'sugianto@panganku.id') }}" required class="w-full px-4 py-3 rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-600">
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" value="password123" required class="w-full px-4 py-3 rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-600">
            </div>
            <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm transition">
                Masuk ke Dasbor
            </button>
        </form>

        <p class="text-center text-xs text-stone-500 mt-6">
            Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-emerald-700 hover:underline">Daftar Akun Baru</a>
        </p>
    </div>
</body>
</html>