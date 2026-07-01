@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Kartu Stok</h1>
            <p class="text-sm text-gray-400">Riwayat pergerakan stok berdasarkan barang dan lokasi.</p>
        </div>
        <a href="{{ route('stock.index') }}" class="rounded border border-slate-700 px-4 py-2 text-sm">Kembali</a>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">SKU</div><div class="mt-1 font-semibold">{{ $stock->item?->sku }}</div></div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Barang</div><div class="mt-1 font-semibold">{{ $stock->item?->name }}</div></div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Lokasi</div><div class="mt-1 font-semibold">{{ $stock->location?->code }} - {{ $stock->location?->name }}</div></div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><div class="text-xs text-slate-400">Available Saat Ini</div><div class="mt-1 text-2xl font-bold">{{ number_format((int) $stock->qty_available) }} <span class="text-sm font-normal text-slate-400">{{ $stock->item?->unit }}</span></div></div>
    </div>

    <form method="GET" action="{{ route('stock.card', $stock) }}" class="grid gap-3 rounded-xl border border-slate-800 bg-slate-900/70 p-4 md:grid-cols-4">
        <label class="space-y-1 text-sm">Tanggal Dari<input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2"></label>
        <label class="space-y-1 text-sm">Tanggal Sampai<input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2"></label>
        <div class="flex items-end gap-2 md:col-span-2"><button class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Filter</button><a href="{{ route('stock.card', $stock) }}" class="rounded border border-slate-700 px-4 py-2 text-sm">Reset</a></div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/70">
        <table class="min-w-full divide-y divide-slate-800 text-sm">
            <thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400">
                <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Referensi</th><th class="px-4 py-3">Tipe</th><th class="px-4 py-3 text-right">Masuk</th><th class="px-4 py-3 text-right">Keluar</th><th class="px-4 py-3 text-right">Saldo Awal</th><th class="px-4 py-3 text-right">Saldo Akhir</th><th class="px-4 py-3">User</th><th class="px-4 py-3">Catatan</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($stockCards as $card)
                    @php
                        $direction = $card->direction?->value ?? (string) $card->direction;
                        $movement = $card->movement_type?->value ?? (string) $card->movement_type;
                        $isIn = $direction === 'in';
                    @endphp
                    <tr class="hover:bg-slate-800/50">
                        <td class="px-4 py-3">{{ $card->trx_date?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $card->reference_no ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs {{ $isIn ? 'bg-green-900/60 text-green-200' : 'bg-red-900/60 text-red-200' }}">{{ $movement }}</span></td>
                        <td class="px-4 py-3 text-right text-green-300">{{ $isIn ? number_format((int) $card->qty) : '-' }}</td>
                        <td class="px-4 py-3 text-right text-red-300">{{ ! $isIn ? number_format((int) $card->qty) : '-' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((int) $card->qty_before) }}</td>
                        <td class="px-4 py-3 text-right font-bold">{{ number_format((int) $card->qty_after) }}</td>
                        <td class="px-4 py-3">{{ $card->postedBy?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $card->remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-slate-400">Belum ada histori kartu stok.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-800 px-4 py-3">{{ $stockCards->links() }}</div>
    </div>
</div>
@endsection
