@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">Stock Gudang</h1>
            <p class="text-xs text-slate-400">Monitoring saldo stok per barang dan lokasi secara real-time setelah transaksi diposting.</p>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid gap-5 grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-xl border border-[#1e293b] bg-[#121826]/60 p-4 shadow-md card-glow">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Baris Stock</div>
            <div class="mt-1.5 text-2xl font-bold text-white">{{ number_format($summary['total_item_location']) }}</div>
        </div>
        <div class="rounded-xl border border-[#1e293b] bg-[#121826]/60 p-4 shadow-md card-glow">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">On Hand</div>
            <div class="mt-1.5 text-2xl font-bold text-white">{{ number_format($summary['total_qty_on_hand']) }}</div>
        </div>
        <div class="rounded-xl border border-[#1e293b] bg-[#121826]/60 p-4 shadow-md card-glow">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Reserved</div>
            <div class="mt-1.5 text-2xl font-bold text-white">{{ number_format($summary['total_qty_reserved']) }}</div>
        </div>
        <div class="rounded-xl border border-[#1e293b] bg-[#121826]/60 p-4 shadow-md card-glow">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Available</div>
            <div class="mt-1.5 text-2xl font-bold text-white">{{ number_format($summary['total_qty_available']) }}</div>
        </div>
        <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4 shadow-md card-glow">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-amber-400">Low Stock</div>
            <div class="mt-1.5 text-2xl font-bold text-amber-400">{{ number_format($summary['low_stock_count']) }}</div>
        </div>
        <div class="rounded-xl border border-rose-500/20 bg-rose-500/5 p-4 shadow-md card-glow">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-rose-400">Kosong</div>
            <div class="mt-1.5 text-2xl font-bold text-rose-400">{{ number_format($summary['empty_stock_count']) }}</div>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('stock.index') }}" class="grid gap-5 rounded-xl border border-[#1e293b] bg-[#121826]/60 p-5 shadow-lg card-glow md:grid-cols-5">
        <div class="flex flex-col">
            <label for="keyword" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Keyword</label>
            <input id="keyword" name="keyword" value="{{ request('keyword') }}" placeholder="SKU / nama barang..." class="input-professional w-full">
        </div>
        
        <div class="flex flex-col">
            <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Kategori</label>
            <select id="category_id" name="category_id" class="input-professional w-full">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col">
            <label for="location_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Lokasi</label>
            <select id="location_id" name="location_id" class="input-professional w-full">
                <option value="">Semua Lokasi</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->code }} - {{ $location->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col">
            <label for="stock_status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Status Stock</label>
            <select id="stock_status" name="stock_status" class="input-professional w-full">
                <option value="all" @selected(request('stock_status', 'all') === 'all')>Semua</option>
                <option value="low" @selected(request('stock_status') === 'low')>Low Stock</option>
                <option value="empty" @selected(request('stock_status') === 'empty')>Kosong</option>
            </select>
        </div>

        <div class="flex items-end gap-2.5">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/15 transition-all cursor-pointer">Filter</button>
            <a href="{{ route('stock.index') }}" class="rounded-lg bg-[#090d16] border border-[#1e293b] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-900 transition-all">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-[#1e293b] bg-[#121826]/40 shadow-xl card-glow">
        <table class="min-w-full divide-y divide-[#1e293b] text-sm">
            <head class="bg-slate-950/60 text-left text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">SKU</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Barang</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Kategori</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Lokasi</th>
                    <th class="px-5 py-3.5 text-right font-bold border-b border-[#1e293b]">On Hand</th>
                    <th class="px-5 py-3.5 text-right font-bold border-b border-[#1e293b]">Reserved</th>
                    <th class="px-5 py-3.5 text-right font-bold border-b border-[#1e293b]">Available</th>
                    <th class="px-5 py-3.5 text-right font-bold border-b border-[#1e293b]">Min</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Status</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Update</th>
                    <th class="px-5 py-3.5 text-right font-bold border-b border-[#1e293b]">Aksi</th>
                </tr>
            </head>
            <tbody class="divide-y divide-[#1e293b]/40">
                @forelse ($stocks as $stock)
                    @php
                        $available = (int) $stock->qty_available;
                        $minStock = (int) ($stock->item?->minimum_stock ?? 0);
                        $isEmpty = $available <= 0;
                        $isLow = ! $isEmpty && $available <= $minStock;
                    @endphp
                    <tr class="hover:bg-slate-900/30 transition-colors">
                        <td class="px-5 py-4 font-mono text-xs text-indigo-300 font-semibold">{{ $stock->item?->sku }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-white">{{ $stock->item?->name }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">Satuan: {{ $stock->item?->unit }} @if($stock->item?->is_serialized) · Serialized @endif</div>
                        </td>
                        <td class="px-5 py-4 text-slate-300">{{ $stock->item?->category?->name ?? '-' }}</td>
                        <td class="px-5 py-4">
                            <div class="text-slate-300">{{ $stock->location?->name }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $stock->location?->code }}</div>
                        </td>
                        <td class="px-5 py-4 text-right text-slate-300">{{ number_format((int) $stock->qty_on_hand) }}</td>
                        <td class="px-5 py-4 text-right text-slate-400">{{ number_format((int) $stock->qty_reserved) }}</td>
                        <td class="px-5 py-4 text-right font-bold text-white">{{ number_format($available) }}</td>
                        <td class="px-5 py-4 text-right text-slate-400">{{ number_format($minStock) }}</td>
                        <td class="px-5 py-4">
                            @if ($isEmpty)
                                <span class="inline-flex rounded-full bg-rose-500/10 px-2.5 py-0.5 text-xs font-semibold text-rose-400 border border-rose-500/20">Kosong</span>
                            @elseif ($isLow)
                                <span class="inline-flex rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-semibold text-amber-400 border border-amber-500/20">Low Stock</span>
                            @else
                                <span class="inline-flex rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-400 border border-emerald-500/20">Aman</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-400">{{ $stock->last_movement_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('stock.card', $stock) }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition-colors">Kartu Stok</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-5 py-8 text-center text-slate-500">Data stock belum tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $stocks->links() }}
    </div>
</div>
@endsection

