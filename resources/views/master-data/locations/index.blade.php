@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">Master Lokasi</h1>
            <p class="text-xs text-slate-400">Daftar gudang penyimpanan, ruangan, dan cabang distribusi aset IT.</p>
        </div>

        @can('locations.create')
            <a href="#" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-1.5 opacity-60 cursor-not-allowed" onclick="alert('Form Create Lokasi ditunda untuk MVP Tahap 1 sesuai blueprint.')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Lokasi
            </a>
        @endcan
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('locations.index') }}" class="rounded-xl bg-[#121826]/60 border border-[#1e293b] p-5 shadow-lg card-glow">
        <div class="grid gap-5 md:grid-cols-3">
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ $search }}" class="input-professional w-full" placeholder="Cari kode, nama, atau tipe lokasi...">
            </div>

            <div class="flex items-end gap-2.5">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/15 transition-all cursor-pointer w-full">Filter</button>
                <a href="{{ route('locations.index') }}" class="rounded-lg bg-[#090d16] border border-[#1e293b] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-900 transition-all text-center w-full">Reset</a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-[#1e293b] bg-[#121826]/40 shadow-xl card-glow">
        <table class="min-w-full divide-y divide-[#1e293b]">
            <thead class="bg-slate-950/60">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Kode</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Nama Lokasi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Tipe Lokasi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Alamat</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Status</th>
                    <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e293b]/40">
                @forelse ($locations as $location)
                    <tr class="hover:bg-slate-900/30 transition-colors">
                        <td class="px-5 py-4 text-sm font-semibold font-mono text-indigo-300">{{ $location->code }}</td>
                        <td class="px-5 py-4 text-sm text-white font-semibold">{{ $location->name }}</td>
                        <td class="px-5 py-4 text-sm text-slate-300">
                            <span class="inline-flex rounded bg-indigo-500/10 px-2 py-0.5 text-xs font-semibold text-indigo-400 border border-indigo-500/20 capitalize">{{ $location->type }}</span>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-400 truncate max-w-xs">{{ $location->address ?: '-' }}</td>
                        <td class="px-5 py-4 text-sm">
                            @if ($location->is_active)
                                <span class="inline-flex rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-400 border border-emerald-500/20">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-500/10 px-2.5 py-0.5 text-xs font-semibold text-slate-400 border border-slate-500/20">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-semibold">
                            <a href="#" class="text-slate-500 cursor-not-allowed opacity-50" onclick="alert('Edit Lokasi ditunda untuk MVP Tahap 1.')">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">
                            Data lokasi belum tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $locations->links() }}
    </div>
</div>
@endsection
