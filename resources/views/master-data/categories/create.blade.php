@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Tambah Kategori</h1>
        <p class="text-sm text-slate-600">Buat kategori baru untuk pengelompokan barang IT.</p>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <form method="POST" action="{{ route('categories.store') }}">
            @include('master-data.categories._form')
        </form>
    </div>
</div>
@endsection
