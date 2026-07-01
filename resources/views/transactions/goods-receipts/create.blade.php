@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Tambah Barang Masuk</h1>
        <p class="text-sm text-gray-400">Simpan sebagai draft dulu. Posting dilakukan setelah data direview.</p>
    </div>

    @if ($errors->any())
        <div class="rounded border border-red-700 bg-red-950/40 p-4 text-sm text-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('goods-receipts.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="mb-4 font-semibold">Header</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-1 text-sm">Supplier
                    <select name="supplier_id" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-sm">Lokasi Gudang
                    <select name="warehouse_location_id" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2" required>
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('warehouse_location_id') == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-sm">Nomor PO<input name="po_no" value="{{ old('po_no') }}" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2"></label>
                <label class="space-y-1 text-sm">Nomor Invoice<input name="invoice_no" value="{{ old('invoice_no') }}" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2"></label>
                <label class="space-y-1 text-sm">Tanggal Pembelian<input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2"></label>
                <label class="space-y-1 text-sm">Tanggal Barang Masuk<input type="date" name="receipt_date" value="{{ old('receipt_date', now()->toDateString()) }}" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2" required></label>
                <label class="space-y-1 text-sm">Upload PO<input type="file" name="po_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2"></label>
                <label class="space-y-1 text-sm">Upload Invoice<input type="file" name="invoice_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2"></label>
                <label class="space-y-1 text-sm md:col-span-2">Catatan<textarea name="remarks" rows="3" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2">{{ old('remarks') }}</textarea></label>
            </div>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold">Detail Barang</h2>
                <button type="button" onclick="addReceiptRow()" class="rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white">Tambah Item</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-400"><tr><th class="p-2">Barang</th><th class="p-2">Qty</th><th class="p-2">Harga</th><th class="p-2">Serial</th><th class="p-2">Garansi</th><th class="p-2">Kondisi</th><th class="p-2">Catatan</th><th></th></tr></thead>
                    <tbody id="details-body"></tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('goods-receipts.index') }}" class="rounded border border-slate-700 px-4 py-2 text-sm">Batal</a>
            <button class="rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white">Simpan Draft</button>
        </div>
    </form>
</div>

<script>
let rowIndex = 0;
const itemOptions = `@foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->sku }} - {{ $item->name }}{{ $item->is_serialized ? ' - SN' : '' }}</option>@endforeach`;
function addReceiptRow() {
    const tbody = document.getElementById('details-body');
    const tr = document.createElement('tr');
    tr.className = 'border-t border-slate-800';
    tr.innerHTML = `<td class="p-2"><select name="items[${rowIndex}][item_id]" class="min-w-56 rounded border border-slate-700 bg-slate-950 px-2 py-2" required><option value="">-- Pilih --</option>${itemOptions}</select></td>
<td class="p-2"><input type="number" min="1" value="1" name="items[${rowIndex}][qty]" class="w-20 rounded border border-slate-700 bg-slate-950 px-2 py-2" required></td>
<td class="p-2"><input type="number" min="0" value="0" name="items[${rowIndex}][unit_price]" class="w-32 rounded border border-slate-700 bg-slate-950 px-2 py-2" required></td>
<td class="p-2"><textarea data-serial-row="${rowIndex}" rows="2" class="w-44 rounded border border-slate-700 bg-slate-950 px-2 py-2" placeholder="1 serial per baris"></textarea></td>
<td class="p-2"><input type="date" name="items[${rowIndex}][warranty_until]" class="rounded border border-slate-700 bg-slate-950 px-2 py-2"></td>
<td class="p-2"><select name="items[${rowIndex}][condition_status]" class="rounded border border-slate-700 bg-slate-950 px-2 py-2"><option value="new">New</option><option value="good">Good</option><option value="used">Used</option><option value="defect">Defect</option></select></td>
<td class="p-2"><input name="items[${rowIndex}][notes]" class="w-40 rounded border border-slate-700 bg-slate-950 px-2 py-2"></td>
<td class="p-2"><button type="button" onclick="removeReceiptRow(this)" class="text-red-400">Hapus</button></td>`;
    tbody.appendChild(tr); rowIndex++;
}
function removeReceiptRow(button) {
    if (document.getElementById('details-body').children.length <= 1) { alert('Minimal harus ada satu item.'); return; }
    button.closest('tr').remove();
}
document.querySelector('form').addEventListener('submit', function () {
    document.querySelectorAll('textarea[data-serial-row]').forEach((textarea) => {
        const index = textarea.dataset.serialRow;
        textarea.value.split('\n').map(v => v.trim()).filter(Boolean).forEach((serial, serialIndex) => {
            const input = document.createElement('input'); input.type = 'hidden'; input.name = `items[${index}][serial_numbers][${serialIndex}]`; input.value = serial; this.appendChild(input);
        });
    });
});
addReceiptRow();
</script>
@endsection
