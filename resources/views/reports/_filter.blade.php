<form method="GET" action="{{ url()->current() }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
    <div class="grid gap-4 md:grid-cols-6">
        @if(($showPeriod ?? true) === true)
            <div><label class="mb-1 block text-xs text-slate-400">Dari Tanggal</label><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full rounded border border-slate-700 bg-slate-950 p-2 text-sm"></div>
            <div><label class="mb-1 block text-xs text-slate-400">Sampai Tanggal</label><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full rounded border border-slate-700 bg-slate-950 p-2 text-sm"></div>
        @endif
        <div><label class="mb-1 block text-xs text-slate-400">Lokasi</label><select name="location_id" class="w-full rounded border border-slate-700 bg-slate-950 p-2 text-sm"><option value="">Semua Lokasi</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(($filters['location_id'] ?? '') == $location->id)>{{ $location->name }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-xs text-slate-400">Kategori</label><select name="category_id" class="w-full rounded border border-slate-700 bg-slate-950 p-2 text-sm"><option value="">Semua Kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-xs text-slate-400">Supplier</label><select name="supplier_id" class="w-full rounded border border-slate-700 bg-slate-950 p-2 text-sm"><option value="">Semua Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(($filters['supplier_id'] ?? '') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-xs text-slate-400">Keyword</label><input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="SKU / nama" class="w-full rounded border border-slate-700 bg-slate-950 p-2 text-sm"></div>
    </div>
    <div class="mt-4 flex flex-wrap justify-end gap-2">
        <a href="{{ url()->current() }}" class="rounded border border-slate-700 px-4 py-2 text-sm">Reset</a>
        <button class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        @can('reports.export')<a href="{{ $exportRoute }}" class="rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white">Export Excel</a>@endcan
    </div>
</form>
