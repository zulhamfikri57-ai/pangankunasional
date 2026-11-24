@extends('layouts.app')
@section('content')
    <h2 class="text-2xl font-extrabold text-stone-800 mb-6">Profil & Status Akun</h2>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kartu Info Pengguna -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-100 h-fit">
            <div class="flex items-center gap-4 mb-5">
                <div
                    class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-extrabold text-2xl">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-extrabold text-lg text-stone-800">{{ $user->name }}</h3>
                    <p class="text-xs text-stone-500">
                        {{ str_ends_with($user->email, '@noemail.panganku.local') ? ($user->no_hp ?? '-') : $user->email }}
                    </p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-stone-50 border border-stone-100 space-y-2.5">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-stone-500">Status Peran</span>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold uppercase
                        {{ $user->peran_sektor == 'konsumen' ? 'bg-stone-200 text-stone-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ str_replace('_', ' ', $user->peran_sektor) }}
                    </span>
                </div>

                <div class="flex justify-between items-center pt-2 border-t border-stone-200/60">
                    <span class="text-xs text-stone-500">Verifikasi Kontak</span>
                    @if($user->is_kontak_verified)
                        <span class="text-xs font-bold text-emerald-700">✓ Terverifikasi</span>
                    @else
                        <a href="{{ route('verifikasi.otp') }}" class="text-xs font-bold text-amber-700 underline">Belum OTP</a>
                    @endif
                </div>

                @if($user->no_hp)
                    <div class="flex justify-between items-center pt-2 border-t border-stone-200/60">
                        <span class="text-xs text-stone-500">Nomor HP / WA</span>
                        <span class="text-xs font-bold text-stone-800">{{ $user->no_hp }}</span>
                    </div>
                @endif

                @if($user->alamat_usaha_riil || $user->lokasi)
                    <div class="flex justify-between items-start gap-3 pt-2 border-t border-stone-200/60">
                        <span class="text-xs text-stone-500 shrink-0">Wilayah / Usaha</span>
                        <span class="text-xs font-bold text-stone-800 text-right">{{ $user->alamat_usaha_riil ?? $user->lokasi }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Panel Status Verifikasi & Upgrade Peran Resmi -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-stone-100">
            <h3 class="font-extrabold text-lg text-stone-800">🛡️ Status & Verifikasi Peran Usaha Pangan</h3>
            <p class="text-xs text-stone-500 mt-1">
                Peran aktif saat ini:
                <span class="font-extrabold uppercase text-emerald-700">{{ str_replace('_', ' ', $user->peran_sektor) }}</span>
            </p>

            @if($user->peran_sektor === 'konsumen')
                <div class="mt-5">
                    @if($user->status_verifikasi_peran === 'pending')
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-950 space-y-1">
                            <p class="font-extrabold text-sm">⏳ Pengajuan Peran "{{ ucfirst($user->peran_diajukan) }}" Sedang Diverifikasi</p>
                            <p>Dokumen identitas dan lokasi usaha Anda sedang diperiksa oleh Administrator sebelum fitur produsen diaktifkan.</p>
                        </div>
                    @elseif($user->status_verifikasi_peran === 'rejected')
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-2 mb-4">
                            <p class="font-extrabold text-sm">❌ Pengajuan Peran Sebelumnya Ditolak</p>
                            <p>Alasan Admin: <strong>{{ $user->catatan_admin_verifikasi ?? 'Dokumen tidak valid.' }}</strong></p>
                        </div>
                        <a href="{{ route('peran.upgrade') }}"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition">
                            🔄 Unggah Ulang Dokumen Verifikasi Peran →
                        </a>
                    @else
                        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 text-xs text-stone-600 mb-4 space-y-1">
                            <p class="font-bold text-stone-800">Ingin menjual hasil panen, ternak, atau tangkapan laut?</p>
                            <p>Ajukan verifikasi identitas (NIK KTP 16 digit, alamat usaha riil, dan dokumen bukti) untuk membuka akses penuh modul siklus produksi, pembukuan, dan marketplace.</p>
                        </div>
                        <a href="{{ route('peran.upgrade') }}"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition">
                            🛡️ Ajukan Verifikasi Jadi Petani / Peternak / Nelayan →
                        </a>
                    @endif
                </div>
            @elseif($user->peran_sektor === 'admin')
                <div class="mt-5 p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs text-amber-950 space-y-3">
                    <p class="font-bold">Akun ini memiliki hak akses penuh sebagai Administrator PanganKu Nasional untuk memverifikasi pengajuan peran produsen dan mengelola data komoditas.</p>
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-stone-800 hover:bg-stone-900 text-white text-xs font-bold transition">
                        🏛️ Buka Panel Moderasi Administrator →
                    </a>
                </div>
            @else
                <div class="mt-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 space-y-1">
                    <p class="font-extrabold text-sm">✅ Akun Produsen Terverifikasi Resmi</p>
                    <p>Anda telah terverifikasi sebagai <strong>{{ ucfirst(str_replace('_', ' ', $user->peran_sektor)) }}</strong> dan dapat mengakses pencatatan siklus usaha, produksi, pembukuan keuangan, serta penawaran di Pasar Panen.</p>
                    @if($user->nik_identitas)
                        <p class="pt-1 text-[11px] text-emerald-800">
                            NIK Terdaftar: <strong>{{ substr($user->nik_identitas, 0, 6) }}******{{ substr($user->nik_identitas, -4) }}</strong>
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection