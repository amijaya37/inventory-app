@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">Master Kategori</h1>
            <p class="text-xs text-slate-400">Kelola klasifikasi kategori barang IT.</p>
        </div>

        @can('categories.create')
            <a href="{{ route('categories.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kategori
            </a>
        @endcan
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('categories.index') }}" class="rounded-xl bg-[#121826]/60 border border-[#1e293b] p-5 shadow-lg card-glow">
        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ $search }}" class="input-professional w-full" placeholder="Cari kode atau nama kategori...">
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                <select name="status" id="status" class="input-professional w-full">
                    <option value="">Semua</option>
                    <option value="1" @selected($status === '1')>Aktif</option>
                    <option value="0" @selected($status === '0')>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-end gap-2.5">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/15 transition-all">Filter</button>
                <a href="{{ route('categories.index') }}" class="rounded-lg bg-[#090d16] border border-[#1e293b] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-900 transition-all">Reset</a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-[#1e293b] bg-[#121826]/40 shadow-xl card-glow">
        <table class="min-w-full divide-y divide-[#1e293b]">
            <thead class="bg-slate-950/60">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Kode</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Nama</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Deskripsi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Jumlah Barang</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Status</th>
                    <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-[#1e293b]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e293b]/40">
                @forelse ($categories as $category)
                    <tr class="hover:bg-slate-900/30 transition-colors">
                        <td class="px-5 py-4 text-sm font-semibold text-white">{{ $category->code }}</td>
                        <td class="px-5 py-4 text-sm text-slate-300">{{ $category->name }}</td>
                        <td class="px-5 py-4 text-sm text-slate-400">{{ $category->description ?: '-' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-300">{{ $category->items_count ?? '0' }}</td>
                        <td class="px-5 py-4 text-sm">
                            @if ($category->is_active)
                                <span class="inline-flex rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-400 border border-emerald-500/20">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-500/10 px-2.5 py-0.5 text-xs font-semibold text-slate-400 border border-slate-500/20">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm">
                            <div class="flex justify-end gap-3 font-semibold">
                                @can('categories.update')
                                    <a href="{{ route('categories.edit', $category) }}" class="text-indigo-400 hover:text-indigo-300 transition-colors">Edit</a>
                                @endcan

                                @can('categories.delete')
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Yakin ingin menghapus/nonaktifkan kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 transition-colors cursor-pointer">Hapus</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">
                            Data kategori belum tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $categories->links() }}
    </div>
</div>
@endsection

