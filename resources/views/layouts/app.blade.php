<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PanganKu Nasional — Ekosistem Pangan Terpadu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F6F7F4;
            color: #1C2621;
        }

        .fluid-title {
            font-size: clamp(1.25rem, 2.5vw, 1.75rem);
            line-height: 1.25;
        }

        .fluid-metric {
            font-size: clamp(1.35rem, 3vw, 1.85rem);
            line-height: 1.2;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col lg:flex-row pb-20 lg:pb-0">

    <!-- HEADER MOBILE (Muncul di layar < 1024px) -->
    <header
        class="lg:hidden sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200/80 px-4 py-3 flex items-center justify-between">
        <a href="{{ route('beranda') }}" class="flex items-center gap-2.5">
            <div
                class="w-9 h-9 rounded-xl bg-emerald-700 text-white flex items-center justify-center text-lg shadow-xs">
                🌱
            </div>
            <div>
                <h1 class="font-extrabold text-sm text-stone-800 leading-none">PanganKu</h1>
                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Nasional</span>
            </div>
        </a>

        <div class="flex items-center gap-2">
            @auth
                @if(auth()->user()->peran_sektor === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="px-2.5 py-1.5 rounded-xl bg-amber-400 text-emerald-950 text-xs font-extrabold">
                        🛡️ Admin
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-xl bg-emerald-700 text-white text-xs font-bold">
                    Masuk
                </a>
            @endauth

            <button type="button" onclick="toggleMobileSidebar()" aria-label="Buka Menu"
                class="p-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-sm transition cursor-pointer">
                ☰ Menu
            </button>
        </div>
    </header>

    <!-- OVERLAY GELAP MOBILE -->
    <div id="sidebarOverlay" onclick="toggleMobileSidebar()"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs hidden lg:hidden"></div>

    <!-- SIDEBAR NAVIGASI UTAMA -->
    <aside id="mainSidebar"
        class="fixed inset-y-0 left-0 z-50 w-68 lg:w-64 lg:sticky lg:top-0 lg:h-screen bg-white border-r border-stone-200/80 flex flex-col justify-between transform -translate-x-full lg:translate-x-0 transition-transform duration-200 overflow-y-auto">

        <div>
            <!-- Logo Brand -->
            <div class="p-5 border-b border-stone-100 flex items-center justify-between">
                <a href="{{ route('beranda') }}" class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-emerald-700 text-white flex items-center justify-center text-xl shadow-xs">
                        🌱
                    </div>
                    <div>
                        <h1 class="font-extrabold text-base text-stone-800 leading-tight">PanganKu</h1>
                        <span class="text-[10px] font-bold text-emerald-700 tracking-widest uppercase">Nasional</span>
                    </div>
                </a>
                <button type="button" onclick="toggleMobileSidebar()"
                    class="lg:hidden w-8 h-8 rounded-lg bg-stone-100 text-stone-600 font-bold">
                    ✕
                </button>
            </div>

            <!-- Menu Navigasi -->
            <nav class="p-3.5 space-y-1">
                <p class="px-3 pt-1 pb-1.5 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Publik &
                    Pasar</p>

                <a href="{{ route('beranda') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('beranda') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🏠</span> Beranda
                </a>

                <a href="{{ route('harga') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('harga') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>📈</span> Pantauan Harga
                </a>

                <a href="{{ route('jual') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('jual') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🏪</span> Pasar & Jual Hasil
                </a>

                <a href="{{ route('panduan') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('panduan') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🤖</span> Panduan & Pakar AI
                </a>

                <p class="px-3 pt-4 pb-1.5 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                    Kelola Usaha @guest <span class="text-amber-600">(Login)</span> @endguest
                </p>
                <a href="{{ route('siklus.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('siklus.*') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🗓️</span> Siklus & Jadwal Panen
                </a>
                <a href="{{ route('produksi') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('produksi') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>📦</span> Catat Produksi
                </a>

                <a href="{{ route('keuangan') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('keuangan') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>💰</span> Keuangan Usaha
                </a>

                <a href="{{ route('laporan') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('laporan') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>📊</span> Laporan Laba-Rugi
                </a>

                <a href="{{ route('pantauan') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('pantauan') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>🔔</span> Alert Harga Pribadi
                </a>

                <a href="{{ route('profil') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('profil') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-stone-600 hover:bg-stone-50' }}">
                    <span>👤</span> Profil & Peran Akun
                </a>

                @auth
                    @if(auth()->user()->peran_sektor === 'admin')
                        <div class="pt-3 mt-3 border-t border-stone-200">
                            <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-emerald-950 font-extrabold text-xs shadow-xs transition">
                                <span>🛡️</span> Kembali ke Panel Admin
                            </a>
                        </div>
                    @endif
                @endauth
            </nav>
        </div>

        <!-- Bagian Bawah Sidebar (Info Akun / Tombol Login) -->
        <div class="p-4 border-t border-stone-100">
            @auth
                <div class="px-3.5 py-2.5 mb-2.5 rounded-2xl bg-stone-50 border border-stone-200/60">
                    <p class="text-xs font-extrabold text-stone-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-emerald-700 font-bold uppercase mt-0.5">
                        {{ str_replace('_', ' ', auth()->user()->peran_sektor) }}
                    </p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition cursor-pointer">
                        🚪 Keluar Akun
                    </button>
                </form>
            @else
                <div class="space-y-2">
                    <a href="{{ route('login') }}"
                        class="block w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-center font-bold text-xs transition">
                        🔑 Masuk ke Akun
                    </a>
                    <a href="{{ route('register') }}"
                        class="block w-full py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-center font-bold text-xs transition">
                        ✨ Daftar Gratis
                    </a>
                </div>
            @endauth
        </div>
    </aside>

    <!-- AREA KONTEN UTAMA -->
    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto w-full">
        @if(session('success'))
            <div
                class="mb-5 p-4 rounded-2xl bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-xs flex items-center justify-between">
                <span>✅ {{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-white/80 hover:text-white font-bold ml-4">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 rounded-2xl bg-rose-600 text-white text-xs sm:text-sm shadow-xs">
                <p class="font-bold mb-1">⚠️ Periksa kembali isian Anda:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- NAVIGASI BAWAH KHUSUS LAYAR MOBILE (< 1024px) -->
    <nav
        class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-stone-200 grid grid-cols-5 py-2 px-1 text-center">
        <a href="{{ route('beranda') }}"
            class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('beranda') ? 'text-emerald-700 font-extrabold' : 'text-stone-500 font-medium' }}">
            <span class="text-lg">🏠</span>
            <span class="text-[10px]">Beranda</span>
        </a>
        <a href="{{ route('harga') }}"
            class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('harga') ? 'text-emerald-700 font-extrabold' : 'text-stone-500 font-medium' }}">
            <span class="text-lg">📈</span>
            <span class="text-[10px]">Harga</span>
        </a>
        <a href="{{ route('produksi') }}"
            class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('produksi') ? 'text-emerald-700 font-extrabold' : 'text-stone-500 font-medium' }}">
            <span class="text-lg">📦</span>
            <span class="text-[10px]">Produksi</span>
        </a>
        <a href="{{ route('jual') }}"
            class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('jual') ? 'text-emerald-700 font-extrabold' : 'text-stone-500 font-medium' }}">
            <span class="text-lg">🏪</span>
            <span class="text-[10px]">Pasar</span>
        </a>
        <a href="{{ route('panduan') }}"
            class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panduan') ? 'text-emerald-700 font-extrabold' : 'text-stone-500 font-medium' }}">
            <span class="text-lg">🤖</span>
            <span class="text-[10px]">Pakar AI</span>
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