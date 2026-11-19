<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — PanganKu Nasional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F7F5EE; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-3xl p-8 md:p-10 shadow-sm border border-stone-100">
        <div class="text-center mb-7">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-700 text-white flex items-center justify-center text-2xl mb-3">🌱</div>
            <h1 class="text-2xl font-extrabold text-emerald-900">Daftar PanganKu Nasional</h1>
            <p class="text-sm text-stone-500 mt-1">Buat akun untuk memantau harga & belanja pangan langsung dari produsen</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Fikri" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-200 text-sm focus:outline-none focus:border-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="fikri@panganku.id" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-200 text-sm focus:outline-none focus:border-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1.5">Kata Sandi</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-200 text-sm focus:outline-none focus:border-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1.5">Ulangi Kata Sandi</label>
                <input type="password" name="password_confirmation" placeholder="Ketik ulang kata sandi" required
                    class="w-full px-4 py-3 rounded-xl border border-stone-200 text-sm focus:outline-none focus:border-emerald-600">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm shadow-sm transition cursor-pointer mt-2">
                Daftar Sekarang →
            </button>
        </form>

        <p class="text-center text-xs text-stone-500 mt-6">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:underline">Masuk di sini</a>
        </p>
    </div>

</body>
</html>