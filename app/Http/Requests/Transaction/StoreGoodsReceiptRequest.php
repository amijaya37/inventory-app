<?php

namespace App\Http\Requests\Transaction;

use App\Domain\Master\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('goods-in.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'source_type' => ['nullable', Rule::in(['purchase'])],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'warehouse_location_id' => ['required', 'exists:locations,id'],
            'po_no' => ['nullable', 'string', 'max:100'],
            'invoice_no' => ['nullable', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date'],
            'receipt_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'po_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', Rule::exists('items', 'id')->where('is_active', true)],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.serial_numbers' => ['nullable', 'array'],
            'items.*.serial_numbers.*' => ['nullable', 'string', 'max:100'],
            'items.*.warranty_until' => ['nullable', 'date'],
            'items.*.condition_status' => ['required', Rule::in(['new', 'good', 'used', 'defect'])],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allSerialNumbers = [];

            foreach ($this->input('items', []) as $index => $detail) {
                $itemId = $detail['item_id'] ?? null;
                if (! $itemId) {
                    continue;
                }

                $item = Item::query()->find($itemId);
                if (! $item) {
                    continue;
                }

                $qty = (int) ($detail['qty'] ?? 0);
                $serialNumbers = array_values(array_filter($detail['serial_numbers'] ?? [], fn ($serial): bool => filled($serial)));

                if ((bool) $item->is_serialized && count($serialNumbers) !== $qty) {
                    $validator->errors()->add("items.{$index}.serial_numbers", "Barang {$item->name} wajib memiliki jumlah serial number yang sama dengan qty.");
                }

                foreach ($serialNumbers as $serialNumber) {
                    $key = strtolower(trim((string) $serialNumber));
                    if (in_array($key, $allSerialNumbers, true)) {
                        $validator->errors()->add("items.{$index}.serial_numbers", "Serial number {$serialNumber} duplikat dalam transaksi ini.");
                    }
                    $allSerialNumbers[] = $key;
                }
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'warehouse_location_id.required' => 'Lokasi gudang wajib dipilih.',
            'receipt_date.required' => 'Tanggal barang masuk wajib diisi.',
            'items.required' => 'Minimal harus ada satu barang.',
            'items.*.item_id.required' => 'Barang wajib dipilih.',
            'items.*.qty.required' => 'Qty wajib diisi.',
            'items.*.qty.min' => 'Qty minimal 1.',
            'items.*.unit_price.required' => 'Harga satuan wajib diisi.',
            'po_file.mimes' => 'File PO harus PDF, JPG, JPEG, atau PNG.',
            'invoice_file.mimes' => 'File invoice harus PDF, JPG, JPEG, atau PNG.',
        ];
    }
}
