<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PanganKu Nasional — Ekosistem Ketahanan & Niaga Pangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fluid-title {
            font-size: clamp(1.25rem, 2.5vw, 1.75rem);
        }
    </style>
</head>

<body class="bg-stone-50 text-stone-800 antialiased min-h-screen">

    <!-- BACKDROP MOBILE SIDEBAR -->
    <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-stone-900/40 z-40 hidden lg:hidden">
    </div>

    <!-- SIDEBAR KIRI -->
    <aside id="leftSidebar"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-stone-200 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">

        <!-- Brand Logo -->
        <div class="h-16 px-5 border-b border-stone-100 flex items-center justify-between shrink-0">
            <a href="{{ route('beranda') }}" class="flex items-center gap-2.5">
                <div
                    class="w-9 h-9 rounded-2xl bg-emerald-700 text-white flex items-center justify-center font-extrabold text-lg shadow-sm">
                    🌾
                </div>
                <div>
                    <span
                        class="font-extrabold text-base text-stone-900 tracking-tight block leading-none">PanganKu</span>
                    <span class="text-[10px] font-bold text-emerald-700 tracking-wider uppercase">Nasional</span>
                </div>
            </a>
            <button type="button" onclick="toggleSidebar()"
                class="lg:hidden p-1.5 rounded-xl text-stone-500 hover:bg-stone-100">
                ✕
            </button>
        </div>

        <!-- Daftar Menu Navigasi -->
        <div class="flex-1 overflow-y-auto px-3.5 py-4 space-y-5 text-xs font-bold">

            <!-- Kelompok 1: Pemantauan & Pasar Publik -->
            <div>
                <p class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Ekosistem Pangan
                </p>
                <nav class="space-y-1">
                    <a href="{{ route('beranda') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('beranda') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                        <span>🏠</span> <span>Beranda Utama</span>
                    </a>
                    <a href="{{ route('harga') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('harga') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                        <span>📈</span> <span>Harga & Stok Komoditas</span>
                    </a>
                    <a href="{{ route('spk.distribusi') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('spk.distribusi') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                        <span>🧭</span> <span>SPK Harga & Distribusi</span>
                    </a>
                    <a href="{{ route('jual') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('jual') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                        <span>🛒</span> <span>Pasar Panen & Niaga</span>
                    </a>
                    <a href="{{ route('panduan') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('panduan') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                        <span>📖</span> <span>Panduan & Konsultasi</span>
                    </a>
                </nav>
            </div>

            @auth
                <!-- Kelompok 2: Panel Khusus Administrator -->
                @if(auth()->user()->peran_sektor === 'admin')
                    <div>
                        <p class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Administrator
                        </p>
                        <nav class="space-y-1">
                            <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-stone-800 text-white shadow-sm' : 'bg-amber-50 text-amber-950 hover:bg-amber-100' }}">
                                <span>🏛️</span> <span>Dashboard & Moderasi</span>
                            </a>
                            <a href="{{ route('admin.komoditas') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.komoditas') ? 'bg-stone-800 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
                                <span>🌾</span> <span>Master Komoditas</span>
                            </a>
                            <a href="{{ route('admin.wilayah') }}"
                                class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm font-bold transition {{ request()->routeIs('admin.wilayah*') ? 'bg-stone-800 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
                                <span>🧭</span> Wilayah Distribusi SPK
                            </a>
                            <a href="{{ route('admin.pengguna') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pengguna') ? 'bg-stone-800 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
                                <span>👥</span> <span>Manajemen Pengguna</span>
                            </a>
                            <a href="{{ route('admin.panduan') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.panduan') ? 'bg-stone-800 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
                                <span>📚</span> <span>Kelola Panduan Teknis</span>
                            </a>
                        </nav>
                    </div>
                @endif

                <!-- Kelompok 3: Operasional Produsen -->
                @if(in_array(auth()->user()->peran_sektor, ['petani', 'peternak', 'nelayan', 'pelaku_pangan']))
                    <div>
                        <p class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Manajemen Usaha
                        </p>
                        <nav class="space-y-1">
                            <a href="{{ route('siklus.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('siklus.*') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                                <span>🌱</span> <span>Siklus & Jadwal Rawat</span>
                            </a>
                            <a href="{{ route('produksi') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('produksi') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                                <span>📦</span> <span>Catatan Produksi</span>
                            </a>
                            <a href="{{ route('keuangan') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('keuangan') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                                <span>💰</span> <span>Pembukuan Keuangan</span>
                            </a>
                            <a href="{{ route('laporan') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('laporan') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                                <span>📊</span> <span>Laporan & Analisis</span>
                            </a>
                        </nav>
                    </div>
                @endif

                <!-- Kelompok 4: Fitur Akun & Pantauan -->
                <div>
                    <p class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Akun &
                        Peringatan</p>
                    <nav class="space-y-1">
                        <a href="{{ route('pantauan') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('pantauan') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                            <span>🔔</span> <span>Pantauan & Alert Harga</span>
                        </a>
                        <a href="{{ route('profil') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('profil') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                            <span>👤</span> <span>Profil & Status Peran</span>
                        </a>

                        @if(auth()->user()->peran_sektor === 'konsumen')
                            <a href="{{ route('peran.upgrade') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('peran.upgrade') ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                                <span>🛡️</span>
                                <span>{{ auth()->user()->status_verifikasi_peran === 'pending' ? 'Verifikasi Diproses' : 'Daftar Jadi Produsen' }}</span>
                            </a>
                        @endif
                    </nav>
                </div>
            @endauth
        </div>

        <!-- Footer Sidebar -->
        <div class="p-3.5 border-t border-stone-100 bg-stone-50 shrink-0">
            @auth
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('profil') }}" class="min-w-0">
                        <p class="text-xs font-extrabold text-stone-800 truncate">{{ auth()->user()->name }}</p>
                        <span
                            class="inline-block mt-0.5 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800">
                            {{ str_replace('_', ' ', auth()->user()->peran_sektor) }}
                        </span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-200 text-rose-800 text-xs font-bold transition cursor-pointer">
                            Keluar
                        </button>
                    </form>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}"
                        class="py-2 text-center rounded-xl border border-stone-300 bg-white hover:bg-stone-100 text-xs font-bold text-stone-700 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                        class="py-2 text-center rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition">
                        Daftar
                    </a>
                </div>
            @endauth
        </div>
    </aside>

    <!-- AREA KONTEN UTAMA (KANAN SIDEBAR) -->
    <div class="lg:pl-64 flex flex-col min-h-screen w-full">

        <!-- TOP BAR -->
        <header
            class="h-16 bg-white border-b border-stone-200 sticky top-0 z-30 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()"
                    class="lg:hidden p-2 rounded-xl border border-stone-200 text-stone-700 hover:bg-stone-100">
                    ☰
                </button>
                <span class="text-xs sm:text-sm font-extrabold text-stone-700 hidden sm:inline">
                    Sistem Terpadu Ketahanan & Distribusi Pangan Nasional
                </span>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    @if(!auth()->user()->is_kontak_verified)
                        <a href="{{ route('verifikasi.otp') }}"
                            class="px-3 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-950 text-xs font-extrabold transition">
                            🔐 Verifikasi OTP
                        </a>
                    @elseif(auth()->user()->peran_sektor === 'konsumen')
                        <a href="{{ route('peran.upgrade') }}"
                            class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition">
                            @if(auth()->user()->status_verifikasi_peran === 'pending')
                                ⏳ Verifikasi Peran Diproses
                            @else
                                🛡️ Jadi Petani / Ternak / Nelayan
                            @endif
                        </a>
                    @endif
                @endauth
            </div>
        </header>

        <!-- BANNER PERINGATAN OTP -->
        @auth
            @if(!auth()->user()->is_kontak_verified && !request()->routeIs('verifikasi.otp'))
                <div class="bg-amber-50 border-b border-amber-200 px-4 py-2.5 text-center text-xs text-amber-950">
                    Akun Anda belum terverifikasi OTP.
                    <a href="{{ route('verifikasi.otp') }}" class="font-extrabold underline ml-1">Masukkan Kode Verifikasi
                        Sekarang →</a>
                </div>
            @endif
        @endauth

        <!-- KONTEN HALAMAN -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @if(session('success'))
                <div
                    class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-bold">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div
                    class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-bold">
                    ⛔ {{ session('error') }}
                </div>
            @endif

            @yield('content')
            @yield('konten')
        </main>

        <!-- FOOTER -->
        <footer class="bg-white border-t border-stone-200 py-5 px-4 sm:px-6 lg:px-8 mt-10">
            <div
                class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-stone-500">
                <p class="font-bold text-stone-700">🌾 PanganKu Nasional — Sistem Terpadu Ketahanan & Niaga Pangan</p>
                <p>Pertanian • Peternakan • Perikanan • Transparansi Harga & Pasokan</p>
            </div>
        </footer>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('leftSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }
    </script>
    <script>
        function formatRibuanStr(val) {
            const angka = String(val).replace(/\D/g, '');
            if (!angka) return '';
            return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[data-rupiah]').forEach(function (input) {
                // Ubah tipe ke text agar browser mengizinkan karakter titik (.)
                input.type = 'text';
                input.inputMode = 'numeric';

                if (input.value) {
                    input.value = formatRibuanStr(input.value);
                }

                input.addEventListener('input', function () {
                    this.value = formatRibuanStr(this.value);
                });

                // Bersihkan titik sebelum form dikirim ke controller Laravel
                if (input.form) {
                    input.form.addEventListener('submit', function () {
                        input.value = input.value.replace(/\./g, '');
                    });
                }
            });
        });
    </script>
</body>

</html>