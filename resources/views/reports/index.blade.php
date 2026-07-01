@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold">Laporan</h1><p class="text-sm text-slate-400">Laporan dasar inventory dan export Excel.</p></div>
    <div class="grid gap-4 md:grid-cols-3">
        <a href="{{ route('reports.stock') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-5 hover:bg-slate-800"><h2 class="font-semibold">Laporan Stock</h2><p class="mt-2 text-sm text-slate-400">Saldo stock per barang dan lokasi.</p></a>
        <a href="{{ route('reports.goods-in') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-5 hover:bg-slate-800"><h2 class="font-semibold">Barang Masuk</h2><p class="mt-2 text-sm text-slate-400">Histori barang masuk posted.</p></a>
        <a href="{{ route('reports.goods-out') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-5 hover:bg-slate-800"><h2 class="font-semibold">Barang Keluar</h2><p class="mt-2 text-sm text-slate-400">Histori barang keluar posted.</p></a>
    </div>
</div>
@endsection
