@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl rounded-2xl border border-slate-800 bg-slate-900/80 p-8 text-center shadow-xl">
    <div class="text-sm font-semibold uppercase tracking-wide text-blue-300">Error 404</div>
    <h1 class="mt-3 text-3xl font-bold text-white">Halaman Tidak Ditemukan</h1>
    <p class="mt-3 text-slate-300">Halaman atau data yang diminta tidak tersedia.</p>
    <div class="mt-6 flex justify-center gap-3">
        <a href="{{ route('dashboard') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Kembali ke Dashboard</a>
        <a href="{{ url('/') }}" class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">Halaman Awal</a>
    </div>
</div>
@endsection
