@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">Barang Masuk</h1>
            <p class="text-xs text-slate-400">Draft barang masuk tidak menambah stok. Stok bertambah setelah posting.</p>
        </div>
        @can('goods-in.create')
            <a href="{{ route('goods-receipts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Barang Masuk
            </a>
        @endcan
    </div>

    <!-- Filter Form -->
    <form method="GET" class="grid gap-5 rounded-xl border border-[#1e293b] bg-[#121826]/60 p-5 shadow-lg card-glow md:grid-cols-3">
        <div class="flex flex-col">
            <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Pencarian</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor, PO, invoice..." class="input-professional w-full">
        </div>

        <div class="flex flex-col">
            <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
            <select id="status" name="status" class="input-professional w-full">
                <option value="">Semua status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="posted" @selected(request('status') === 'posted')>Posted</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
        </div>

        <div class="flex items-end gap-2.5">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/15 transition-all cursor-pointer">Filter</button>
            <a href="{{ route('goods-receipts.index') }}" class="rounded-lg bg-[#090d16] border border-[#1e293b] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-900 transition-all">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-[#1e293b] bg-[#121826]/40 shadow-xl card-glow">
        <table class="min-w-full divide-y divide-[#1e293b] text-sm">
            <thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Nomor</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Tanggal</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Supplier</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Gudang</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Item</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Total</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Status</th>
                    <th class="px-5 py-3.5 text-right font-bold border-b border-[#1e293b]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e293b]/40">
                @forelse ($receipts as $receipt)
                    <tr class="hover:bg-slate-900/30 transition-colors">
                        <td class="px-5 py-4 font-semibold text-white">{{ $receipt->receipt_no }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $receipt->receipt_date?->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $receipt->supplier?->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $receipt->warehouseLocation?->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $receipt->items_count }} items</td>
                        <td class="px-5 py-4 text-slate-300 font-semibold">Rp {{ number_format((float) $receipt->total_amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-4">
                            @php
                                $statusStr = $receipt->status->value;
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider
                                @if($statusStr === 'posted') badge-posted
                                @elseif($statusStr === 'draft') badge-draft
                                @else badge-cancelled @endif">
                                {{ $statusStr }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a class="text-indigo-400 hover:text-indigo-300 font-semibold transition-colors" href="{{ route('goods-receipts.show', $receipt) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">Belum ada transaksi barang masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $receipts->links() }}
    </div>
</div>
@endsection

