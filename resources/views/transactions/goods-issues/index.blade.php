@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">Barang Keluar</h1>
            <p class="text-xs text-slate-400">Draft dan posting pengeluaran barang dari gudang.</p>
        </div>
        
        @can('goods-out.create')
            <a href="{{ route('goods-issues.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Barang Keluar
            </a>
        @endcan
    </div>

    <!-- Filter Form -->
    <form method="GET" class="grid gap-5 rounded-xl border border-[#1e293b] bg-[#121826]/60 p-5 shadow-lg card-glow md:grid-cols-4">
        <div class="flex flex-col md:col-span-2">
            <label for="q" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Keyword</label>
            <input id="q" name="q" value="{{ request('q') }}" placeholder="Cari no BK/ST/penerima..." class="input-professional w-full">
        </div>

        <div class="flex flex-col">
            <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
            <select id="status" name="status" class="input-professional w-full">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status')==='draft')>Draft</option>
                <option value="posted" @selected(request('status')==='posted')>Posted</option>
            </select>
        </div>

        <div class="flex items-end gap-2.5">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/15 transition-all cursor-pointer">Filter</button>
            <a href="{{ route('goods-issues.index') }}" class="rounded-lg bg-[#090d16] border border-[#1e293b] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-900 transition-all">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-[#1e293b] bg-[#121826]/40 shadow-xl card-glow">
        <table class="min-w-full divide-y divide-[#1e293b] text-sm">
            <thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">No</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Tanggal</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Penerima</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Lokasi Asal</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Dokumen</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Status</th>
                    <th class="px-5 py-3.5 text-right font-bold border-b border-[#1e293b]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e293b]/40">
                @forelse($goodsIssues as $issue)
                    <tr class="hover:bg-slate-900/30 transition-colors">
                        <td class="px-5 py-4 font-mono text-xs text-white font-semibold">{{ $issue->issue_no }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $issue->issue_date?->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $issue->recipient_name }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $issue->sourceLocation?->name }}</td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-400">{{ $issue->document_no ?? '-' }}</td>
                        <td class="px-5 py-4">
                            @php
                                $statusStr = is_object($issue->status) ? $issue->status->value : $issue->status;
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider
                                @if($statusStr === 'posted') badge-posted
                                @elseif($statusStr === 'draft') badge-draft
                                @else badge-cancelled @endif">
                                {{ $statusStr }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('goods-issues.show',$issue) }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition-colors">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada transaksi barang keluar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $goodsIssues->links() }}
    </div>
</div>
@endsection

