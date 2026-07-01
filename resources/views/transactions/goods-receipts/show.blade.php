@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Detail Barang Masuk</h1>
            <p class="text-sm text-gray-400">{{ $receipt->receipt_no }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('goods-receipts.index') }}" class="rounded border border-slate-700 px-4 py-2 text-sm">Kembali</a>
            @can('goods-in.post')
                @if ($receipt->isDraft())
                    <form method="POST" action="{{ route('goods-receipts.post', $receipt) }}" onsubmit="return confirm('Posting transaksi ini? Stok akan bertambah dan transaksi terkunci.');">
                        @csrf
                        <button class="rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white">Posting</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="rounded border border-green-700 bg-green-950/40 p-3 text-sm text-green-200">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded border border-red-700 bg-red-950/40 p-3 text-sm text-red-200">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Status</div><div class="font-semibold">{{ $receipt->status->value }}</div></div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Tanggal Masuk</div><div class="font-semibold">{{ $receipt->receipt_date?->format('d/m/Y') }}</div></div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Total</div><div class="font-semibold">Rp {{ number_format((float) $receipt->total_amount, 0, ',', '.') }}</div></div>
    </div>

    <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <h2 class="mb-4 font-semibold">Header</h2>
        <dl class="grid gap-4 text-sm md:grid-cols-2">
            <div><dt class="text-slate-400">Supplier</dt><dd>{{ $receipt->supplier?->name ?? '-' }}</dd></div>
            <div><dt class="text-slate-400">Gudang</dt><dd>{{ $receipt->warehouseLocation?->name ?? '-' }}</dd></div>
            <div><dt class="text-slate-400">PO</dt><dd>{{ $receipt->po_no ?? '-' }}</dd></div>
            <div><dt class="text-slate-400">Invoice</dt><dd>{{ $receipt->invoice_no ?? '-' }}</dd></div>
            <div><dt class="text-slate-400">Dibuat oleh</dt><dd>{{ $receipt->creator?->name ?? '-' }}</dd></div>
            <div><dt class="text-slate-400">Diposting oleh</dt><dd>{{ $receipt->poster?->name ?? '-' }} @if($receipt->posted_at) — {{ $receipt->posted_at->format('d/m/Y H:i') }} @endif</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-400">Catatan</dt><dd>{{ $receipt->remarks ?? '-' }}</dd></div>
        </dl>
    </div>

    @include('transactions.partials.documents', [
        'documents' => $receipt->documents,
        'uploadAction' => route('goods-receipts.documents.store', $receipt),
    ])

    <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/70">
        <table class="min-w-full divide-y divide-slate-800 text-sm">
            <thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400">
                <tr><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Qty</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Kondisi</th><th class="px-4 py-3">Serial</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach ($receipt->items as $detail)
                    <tr>
                        <td class="px-4 py-3">{{ $detail->item?->sku }} - {{ $detail->item?->name }}</td>
                        <td class="px-4 py-3">{{ $detail->qty }}</td>
                        <td class="px-4 py-3">Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">Rp {{ number_format((float) $detail->total_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $detail->condition_status }}</td>
                        <td class="px-4 py-3">{{ implode(', ', $detail->serial_numbers ?? []) ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
