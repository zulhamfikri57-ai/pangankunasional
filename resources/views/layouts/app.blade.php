<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>PanganKu Nasional — Sistem Manajemen Pangan & Agribisnis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F7F5EE;
            color: #1E2923;
            overflow-x: hidden;
        }
        .hero-gradient {
            background: linear-gradient(135deg, #5C8347 0%, #9FA353 50%, #E7B94B 100%);
        }
        /* Tipografi elastis menyesuaikan lebar layar */
        .fluid-title {
            font-size: clamp(1.35rem, 2.5vw, 1.875rem);
            line-height: 1.2;
        }
        .fluid-metric {
            font-size: clamp(1.5rem, 3.2vw, 2.25rem);
            line-height: 1.15;
        }
        /* Sembunyikan scrollbar pada tab horizontal mobile */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col lg:flex-row">

    <!-- Header Khusus Mobile & Tablet (< 1024px) -->
    <header class="lg:hidden sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200 px-4 py-3 flex items-center justify-between">
        <a href="{{ route('beranda') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-bold text-lg">🌱</div>
            <div>
                <h1 class="font-extrabold text-base text-emerald-900 leading-none">PanganKu</h1>
                <span class="text-[10px] font-bold text-amber-600 tracking-wider uppercase">Nasional</span>
            </div>
        </a>

        <div class="flex items-center gap-2">
            @if(auth()->user()->peran_sektor === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 rounded-xl bg-amber-400 text-emerald-950 text-xs font-extrabold">
                🛡️ Admin
            </a>
            @endif

            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> ONLINE
            </span>
            <button type="button" onclick="toggleMobileSidebar()" aria-label="Buka Menu"
                class="p-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-sm transition cursor-pointer">
                ☰ Menu
            </button>
        </div>
    </header>

    <!-- Overlay Gelap saat Drawer Mobile Terbuka -->
    <div id="sidebarOverlay" onclick="toggleMobileSidebar()"
         class="fixed inset-0 bg-black/40 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity"></div>

    <!-- Sidebar Navigasi (Drawer di Mobile/Tablet, Sticky di Desktop >= 1024px) -->
    <aside id="mainSidebar"
        class="fixed inset-y-0 left-0 z-50 w-72 lg:w-64 xl:w-72 lg:sticky lg:top-0 lg:h-screen bg-white border-r border-stone-200 flex flex-col justify-between transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-out overflow-y-auto">
        
        <div>
            <!-- Logo Header -->
            <div class="p-5 lg:p-6 border-b border-stone-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-bold text-xl">🌱</div>
                    <div>
                        <h1 class="font-extrabold text-lg text-emerald-900 leading-tight">PanganKu</h1>
                        <span class="text-xs font-semibold text-amber-600 tracking-wider uppercase">Nasional</span>
                    </div>
                </div>
                <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden w-8 h-8 rounded-lg bg-stone-100 text-stone-600 font-bold">
                    ✕
                </button>
            </div>

            <!-- Menu Navigasi -->
            <nav class="p-3.5 lg:p-4 space-y-1">
                <a href="{{ route('beranda') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('beranda') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🏠</span> Beranda
                </a>
                <a href="{{ route('produksi') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('produksi') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🚜</span> Hasil Produksi
                </a>
                <a href="{{ route('jual') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('jual') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🏷️</span> Jual Hasil Panen
                </a>
                <a href="{{ route('keuangan') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('keuangan') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>💰</span> Keuangan
                </a>
                <a href="{{ route('laporan') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('laporan') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>📊</span> Laporan Laba
                </a>
                <a href="{{ route('harga') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('harga') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>📈</span> Harga Komoditas
                </a>
                <a href="{{ route('pantauan') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('pantauan') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🔔</span> Pantauan & Alert
                </a>
                <a href="{{ route('panduan') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('panduan') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>📖</span> Panduan & Pakar AI
                </a>
                <a href="{{ route('profil') }}" class="flex items-center gap-3 px-4 py-2.5 lg:py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('profil') ? 'bg-emerald-50 text-emerald-800' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>👤</span> Profil & Upgrade Akun
                </a>

                @if(auth()->user()->peran_sektor === 'admin')
                <div class="pt-3 mt-3 border-t border-stone-200">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-emerald-950 font-extrabold text-xs shadow-xs transition">
                        <span>🛡️</span> Kembali ke Panel Admin
                    </a>
                </div>
                @endif
            </nav>
        </div>

        <!-- Status & Logout -->
        <div class="pt-2">
            <div class="p-3.5 mx-4 mb-3 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-emerald-900">Tersinkron DB</span>
                </div>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-600 text-white">ONLINE</span>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="px-4 pb-4">
                @csrf
                <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition cursor-pointer">
                    🚪 Keluar Akun
                </button>
            </form>
        </div>
    </aside>

    <!-- Konten Utama Elastis -->
    <main class="flex-1 min-w-0 px-4 py-5 sm:px-6 sm:py-7 lg:px-10 lg:py-8 pb-24 lg:pb-10 max-w-7xl mx-auto w-full">
        @if(session('success'))
            <div class="mb-5 p-3.5 sm:p-4 rounded-2xl bg-emerald-600 text-white font-semibold text-xs sm:text-sm shadow-sm flex items-center justify-between">
                <span>✅ {{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Navigation Bar Khusus Mobile (< 768px) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-stone-200 px-2 py-1.5 flex items-center justify-around">
        <a href="{{ route('beranda') }}" class="flex flex-col items-center py-1 px-2.5 rounded-xl {{ request()->routeIs('beranda') ? 'text-emerald-700 bg-emerald-50 font-bold' : 'text-stone-500' }}">
            <span class="text-lg leading-none">🏠</span>
            <span class="text-[10px] mt-1">Beranda</span>
        </a>
        <a href="{{ route('harga') }}" class="flex flex-col items-center py-1 px-2.5 rounded-xl {{ request()->routeIs('harga') ? 'text-emerald-700 bg-emerald-50 font-bold' : 'text-stone-500' }}">
            <span class="text-lg leading-none">📈</span>
            <span class="text-[10px] mt-1">Harga</span>
        </a>
        <a href="{{ route('produksi') }}" class="w-12 h-12 -mt-5 rounded-full hero-gradient text-white shadow-md flex items-center justify-center text-2xl font-bold border-4 border-[#F7F5EE]">
            +
        </a>
        <a href="{{ route('pantauan') }}" class="flex flex-col items-center py-1 px-2.5 rounded-xl {{ request()->routeIs('pantauan') ? 'text-emerald-700 bg-emerald-50 font-bold' : 'text-stone-500' }}">
            <span class="text-lg leading-none">🔔</span>
            <span class="text-[10px] mt-1">Pantauan</span>
        </a>
        <a href="{{ route('profil') }}" class="flex flex-col items-center py-1 px-2.5 rounded-xl {{ request()->routeIs('profil') ? 'text-emerald-700 bg-emerald-50 font-bold' : 'text-stone-500' }}">
            <span class="text-lg leading-none">👤</span>
            <span class="text-[10px] mt-1">Profil</span>
        </a>
    </nav>

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mainSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>