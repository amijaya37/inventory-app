@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold">{{ $title }}</h1><p class="text-sm text-slate-400">Saldo stock per barang dan lokasi.</p></div>
    @include('reports._filter', ['showPeriod' => false])
    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70"><table class="min-w-full divide-y divide-slate-800 text-sm"><thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400"><tr><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Lokasi</th><th class="px-4 py-3 text-right">On Hand</th><th class="px-4 py-3 text-right">Reserved</th><th class="px-4 py-3 text-right">Available</th><th class="px-4 py-3 text-right">Min</th><th class="px-4 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-800">@forelse($rows as $row)<tr><td class="px-4 py-3 font-mono">{{ $row->sku }}</td><td class="px-4 py-3">{{ $row->item_name }}</td><td class="px-4 py-3">{{ $row->category_name ?? '-' }}</td><td class="px-4 py-3">{{ $row->location_name }}</td><td class="px-4 py-3 text-right">{{ number_format($row->qty_on_hand) }}</td><td class="px-4 py-3 text-right">{{ number_format($row->qty_reserved) }}</td><td class="px-4 py-3 text-right font-semibold">{{ number_format($row->qty_available) }}</td><td class="px-4 py-3 text-right">{{ number_format($row->minimum_stock) }}</td><td class="px-4 py-3">@if($row->is_low_stock)<span class="rounded bg-red-500/20 px-2 py-1 text-xs text-red-200">Low Stock</span>@else<span class="rounded bg-green-500/20 px-2 py-1 text-xs text-green-200">Aman</span>@endif</td></tr>@empty<tr><td colspan="9" class="px-4 py-6 text-center text-slate-400">Data tidak ditemukan.</td></tr>@endforelse</tbody></table></div>
    {{ $rows->links() }}
</div>
@endsection
