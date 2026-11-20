<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Pusat — PanganKu Nasional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F3F4F1; color: #18221D; }
    </style>
</head>
<body class="min-h-screen flex flex-col lg:flex-row">

    <!-- Header Mobile -->
    <header class="lg:hidden sticky top-0 z-40 bg-emerald-950 text-white px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-xl">🛡️</span>
            <span class="font-extrabold text-sm tracking-wide">ADMIN PANGANKU</span>
        </div>
        <button type="button" onclick="document.getElementById('adminSidebar').classList.toggle('-translate-x-full')"
                class="px-3 py-1.5 rounded-lg bg-white/15 text-xs font-bold">
            ☰ Menu
        </button>
    </header>

    <!-- Sidebar Admin -->
    <aside id="adminSidebar"
           class="fixed inset-y-0 left-0 z-50 w-64 lg:sticky lg:top-0 lg:h-screen bg-emerald-950 text-emerald-50 flex flex-col justify-between transform -translate-x-full lg:translate-x-0 transition-transform duration-200 overflow-y-auto">
        <div>
            <div class="p-6 border-b border-emerald-900 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 text-emerald-950 flex items-center justify-center font-extrabold text-xl">🛡️</div>
                    <div>
                        <h1 class="font-extrabold text-base text-white leading-tight">PanganKu</h1>
                        <span class="text-[10px] font-bold text-amber-400 tracking-widest uppercase">Pusat Kendali</span>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('adminSidebar').classList.add('-translate-x-full')" class="lg:hidden text-white/70">✕</button>
            </div>

            <nav class="p-4 space-y-1.5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-800 text-white' : 'text-emerald-200/80 hover:bg-emerald-900/60' }}">
                    <span>📊</span> Monitoring Nasional
                </a>
                <a href="{{ route('admin.komoditas') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.komoditas') ? 'bg-emerald-800 text-white' : 'text-emerald-200/80 hover:bg-emerald-900/60' }}">
                    <span>💹</span> Harga & Komoditas
                </a>
                <a href="{{ route('admin.pengguna') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.pengguna') ? 'bg-emerald-800 text-white' : 'text-emerald-200/80 hover:bg-emerald-900/60' }}">
                    <span>👥</span> Kelola Pengguna
                </a>
                <a href="{{ route('admin.panduan') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.panduan') ? 'bg-emerald-800 text-white' : 'text-emerald-200/80 hover:bg-emerald-900/60' }}">
                    <span>📚</span> Kelola Panduan
                </a>
                <div class="pt-3 mt-3 border-t border-emerald-900">
                    <a href="{{ route('beranda') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-xs text-amber-300 hover:bg-emerald-900/60 transition">
                        <span>↗️</span> Lihat Tampilan Pengguna
                    </a>
                </div>
            </nav>
        </div>

        <form action="{{ route('logout') }}" method="POST" class="p-4">
            @csrf
            <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-200 font-bold text-xs transition cursor-pointer">
                🚪 Keluar Admin
            </button>
        </form>
    </aside>

    <!-- Konten Utama Admin -->
    <main class="flex-1 min-w-0 p-5 sm:p-8 max-w-7xl mx-auto w-full">
        @if(session('success'))
            <div class="mb-5 p-4 rounded-2xl bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-sm">
                ✅ {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>