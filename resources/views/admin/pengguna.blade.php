@extends('layouts.admin')
@section('content')

<h2 class="text-2xl font-extrabold text-stone-800 mb-1">Kelola Akun Pengguna</h2>
<p class="text-xs sm:text-sm text-stone-500 mb-6">Verifikasi peran sektor pelaku pangan dan kelola hak akses akun</p>

<div class="bg-white rounded-3xl border border-stone-200/70 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase text-[11px]">
                <tr>
                    <th class="p-4">Nama & Email</th>
                    <th class="p-4">Komoditas & Wilayah</th>
                    <th class="p-4">Ubah Peran Akun</th>
                    <th class="p-4 text-right">Hapus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($users as $u)
                <tr>
                    <td class="p-4">
                        <p class="font-bold text-stone-800">{{ $u->name }}</p>
                        <p class="text-xs text-stone-400">{{ $u->email }}</p>
                    </td>
                    <td class="p-4 text-xs text-stone-600">
                        <p class="font-semibold">{{ $u->komoditas_utama ?: '-' }}</p>
                        <p class="text-stone-400">{{ $u->lokasi ?: '-' }}</p>
                    </td>
                    <td class="p-4">
                        <form action="{{ route('admin.pengguna.update', $u->id) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="peran_sektor" class="px-3 py-1.5 rounded-lg border border-stone-300 bg-white text-xs font-bold">
                                @foreach(['konsumen', 'petani', 'peternak', 'nelayan', 'pelaku_pangan', 'admin'] as $role)
                                    <option value="{{ $role }}" {{ $u->peran_sektor == $role ? 'selected' : '' }}>
                                        {{ strtoupper(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-700 text-white font-bold text-xs cursor-pointer">
                                Update
                            </button>
                        </form>
                    </td>
                    <td class="p-4 text-right">
                        @if($u->id !== auth()->id())
                        <form action="{{ route('admin.pengguna.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $u->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs cursor-pointer">
                                Hapus
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection