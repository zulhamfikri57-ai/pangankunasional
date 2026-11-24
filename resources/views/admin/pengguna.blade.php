@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="fluid-title font-extrabold text-stone-800">👥 Manajemen Akun Pengguna</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Kelola hak akses akun, status verifikasi kontak, dan peran sektor pelaku pangan</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            🏛️ Dashboard
        </a>
        <a href="{{ route('admin.komoditas') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            📈 Komoditas
        </a>
        <a href="{{ route('admin.panduan') }}" class="px-3.5 py-2 rounded-2xl bg-white border border-stone-200 hover:bg-stone-50 text-xs font-bold text-stone-700 transition">
            📖 Panduan
        </a>
    </div>
</div>

<div class="bg-white rounded-3xl border border-stone-200/80 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-stone-100 flex items-center justify-between">
        <h3 class="font-extrabold text-base text-stone-800">Daftar Pengguna Terdaftar ({{ $users->count() }})</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase text-[11px] font-extrabold">
                <tr>
                    <th class="p-4">Nama & Kontak</th>
                    <th class="p-4">Komoditas & Wilayah</th>
                    <th class="p-4">Ubah Peran Akun</th>
                    <th class="p-4 text-right">Hapus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($users as $u)
                <tr class="hover:bg-stone-50/70 transition">
                    <td class="p-4">
                        <p class="font-bold text-stone-800">{{ $u->name }}</p>
                        <p class="text-xs text-stone-500">
                            {{ str_ends_with($u->email, '@noemail.panganku.local') ? ($u->no_hp ?: 'Daftar via HP') : $u->email }}
                            @if($u->no_hp && !str_ends_with($u->email, '@noemail.panganku.local'))
                                • {{ $u->no_hp }}
                            @endif
                        </p>
                    </td>
                    <td class="p-4 text-xs text-stone-600">
                        <p class="font-semibold">{{ $u->komoditas_utama ?: '-' }}</p>
                        <p class="text-stone-400">{{ $u->alamat_usaha_riil ?: ($u->lokasi ?: '-') }}</p>
                    </td>
                    <td class="p-4">
                        <form action="{{ route('admin.pengguna.update', $u->id) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="peran_sektor" class="px-3 py-1.5 rounded-xl border border-stone-300 bg-white text-xs font-bold">
                                @foreach(['konsumen', 'petani', 'peternak', 'nelayan', 'pelaku_pangan', 'admin'] as $role)
                                    <option value="{{ $role }}" {{ $u->peran_sektor == $role ? 'selected' : '' }}>
                                        {{ strtoupper(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition cursor-pointer">
                                Update
                            </button>
                        </form>
                    </td>
                    <td class="p-4 text-right">
                        @if($u->id !== auth()->id())
                        <form action="{{ route('admin.pengguna.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $u->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition cursor-pointer">
                                🗑️ Hapus
                            </button>
                        </form>
                        @else
                            <span class="px-2.5 py-1 rounded-lg bg-stone-100 text-stone-500 text-[11px] font-bold">Akun Anda</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection