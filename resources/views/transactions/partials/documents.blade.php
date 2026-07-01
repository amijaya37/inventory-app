<div class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="font-semibold">Dokumen Transaksi</h2>
        <p class="text-xs text-slate-400">Private storage, akses lewat permission.</p>
    </div>

    @can('documents.upload')
        <form method="POST" action="{{ $uploadAction }}" enctype="multipart/form-data" class="mb-5 grid gap-3 md:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1 block text-xs text-slate-400">Jenis Dokumen</label>
                <select name="document_type" class="w-full rounded border border-slate-700 bg-slate-950 p-2 text-sm" required>
                    <option value="invoice">Invoice</option>
                    <option value="po">PO</option>
                    <option value="berita_acara">Berita Acara</option>
                    <option value="foto">Foto</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-400">File</label>
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full rounded border border-slate-700 bg-slate-950 p-2 text-sm" required>
                <p class="mt-1 text-xs text-slate-500">PDF/JPG/PNG/WEBP maksimal 10 MB.</p>
            </div>
            <div class="flex items-end">
                <button class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Upload</button>
            </div>
        </form>
    @endcan

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-800 text-sm">
            <thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400">
                <tr><th class="px-4 py-3">Jenis</th><th class="px-4 py-3">File</th><th class="px-4 py-3">Uploader</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($documents as $document)
                    <tr>
                        <td class="px-4 py-3">{{ $document->document_type }}</td>
                        <td class="px-4 py-3">{{ $document->original_name }}</td>
                        <td class="px-4 py-3">{{ $document->uploader?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $document->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @can('download', $document)
                                <a class="text-blue-400 underline" href="{{ route('documents.download', $document) }}">Download</a>
                            @endcan
                            @can('delete', $document)
                                <form method="POST" action="{{ route('documents.destroy', $document) }}" class="inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                    @csrf @method('DELETE')
                                    <button class="ml-2 text-red-400 underline">Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-4 text-center text-slate-400">Belum ada dokumen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
