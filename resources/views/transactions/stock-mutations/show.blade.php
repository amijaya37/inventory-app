@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div><h1 class="text-2xl font-bold">Detail Mutasi Barang</h1><p class="font-mono text-sm text-slate-400">{{ $mutation->mutation_no }}</p></div>
        <a href="{{ route('stock-mutations.index') }}" class="rounded border border-slate-700 px-4 py-2 text-sm">Kembali</a>
    </div>
    @if(session('success'))<div class="rounded border border-green-800 bg-green-950/40 p-3 text-green-200">{{ session('success') }}</div>@endif
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Status</div><div class="mt-1 font-bold">{{ $mutation->status?->value ?? $mutation->status }}</div></div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Tanggal</div><div class="mt-1">{{ $mutation->mutation_date?->format('d/m/Y') }}</div></div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Lokasi Asal</div><div class="mt-1">{{ $mutation->sourceLocation?->name }}</div></div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Lokasi Tujuan</div><div class="mt-1">{{ $mutation->destinationLocation?->name }}</div></div>
    </div>
    <div class="grid gap-4 rounded-xl border border-slate-800 bg-slate-900/70 p-4 md:grid-cols-2">
        <div><div class="text-xs text-slate-400">Requested By</div><div>{{ $mutation->requester?->name ?? '-' }}</div></div>
        <div><div class="text-xs text-slate-400">Catatan</div><div>{{ $mutation->remarks ?? '-' }}</div></div>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/70"><table class="min-w-full divide-y divide-slate-800 text-sm"><thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400"><tr><th class="px-4 py-3">Barang</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3">Serial</th><th class="px-4 py-3">Kondisi</th><th class="px-4 py-3">Catatan</th></tr></thead><tbody class="divide-y divide-slate-800">@foreach($mutation->items as $line)<tr><td class="px-4 py-3"><div>{{ $line->item?->name }}</div><div class="font-mono text-xs text-slate-400">{{ $line->item?->sku }}</div></td><td class="px-4 py-3 text-right">{{ number_format($line->qty) }}</td><td class="px-4 py-3">{{ $line->serial_no ?? '-' }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', $line->condition_status) }}</td><td class="px-4 py-3">{{ $line->notes ?? '-' }}</td></tr>@endforeach</tbody></table></div>
    @if($mutation->isDraft())<form method="POST" action="{{ route('stock-mutations.post', $mutation) }}">@csrf<button class="rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white">Posting Mutasi</button></form>@endif
</div>
@endsection
