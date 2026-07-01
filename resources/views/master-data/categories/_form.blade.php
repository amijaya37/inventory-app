@csrf

<div class="space-y-4">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kode Kategori</label>
        <input type="text" name="code" id="code" value="{{ old('code', $category->code) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: TONER" required>
        @error('code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama Kategori</label>
        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: Toner Printer" required>
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
        <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Keterangan tambahan kategori">{{ old('description', $category->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked(old('is_active', $category->is_active))>
        <label for="is_active" class="text-sm text-slate-700">Aktif</label>
    </div>

    <div class="flex items-center gap-2 pt-4">
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Simpan</button>
        <a href="{{ route('categories.index') }}" class="rounded-md bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">Batal</a>
    </div>
</div>
