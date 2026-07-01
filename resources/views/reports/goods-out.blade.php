@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold">{{ $title }}</h1><p class="text-sm text-slate-400">Histori barang keluar yang sudah diposting.</p></div>
    @include('reports._filter', ['showPeriod' => true])
    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70"><table class="min-w-full divide-y divide-slate-800 text-sm"><thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400"><tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">No Keluar</th><th class="px-4 py-3">Penerima</th><th class="px-4 py-3">Lokasi Tujuan</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3">Kondisi</th><th class="px-4 py-3">Dokumen</th></tr></thead><tbody class="divide-y divide-slate-800">@forelse($rows as $row)<tr><td class="px-4 py-3">{{ \Carbon\Carbon::parse($row->issue_date)->format('d/m/Y') }}</td><td class="px-4 py-3 font-mono">{{ $row->issue_no }}</td><td class="px-4 py-3">{{ $row->recipient_name }}</td><td class="px-4 py-3">{{ $row->target_location_name ?? '-' }}</td><td class="px-4 py-3"><div>{{ $row->item_name }}</div><div class="font-mono text-xs text-slate-400">{{ $row->sku }}</div></td><td class="px-4 py-3">{{ $row->category_name ?? '-' }}</td><td class="px-4 py-3 text-right">{{ number_format($row->qty) }}</td><td class="px-4 py-3">{{ $row->condition_status }}</td><td class="px-4 py-3">{{ $row->document_no ?? '-' }}</td></tr>@empty<tr><td colspan="9" class="px-4 py-6 text-center text-slate-400">Data tidak ditemukan.</td></tr>@endforelse</tbody></table></div>
    {{ $rows->links() }}
</div>
@endsection
