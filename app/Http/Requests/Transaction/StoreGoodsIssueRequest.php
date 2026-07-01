<?php

namespace App\Http\Requests\Transaction;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Master\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGoodsIssueRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $rawItems = $this->input('items', []);
        $items = is_array($rawItems) ? array_values(array_filter($rawItems, static function (mixed $line): bool {
            return is_array($line) && (filled($line['item_id'] ?? null) || filled($line['qty'] ?? null));
        })) : [];

        $this->merge(['items' => $items]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('goods-out.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'issue_date' => ['required', 'date'],
            'source_location_id' => ['required', 'exists:locations,id'],
            'recipient_type' => ['required', Rule::in(['user', 'location', 'external'])],
            'recipient_user_id' => ['nullable', 'required_if:recipient_type,user', 'exists:users,id'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_department' => ['nullable', 'string', 'max:150'],
            'recipient_phone' => ['nullable', 'string', 'max:50'],
            'target_location_id' => ['nullable', 'required_if:recipient_type,location', 'exists:locations,id'],
            'pic_user_id' => ['required', 'exists:users,id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id', 'distinct'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.serial_no' => ['nullable', 'string', 'max:100'],
            'items.*.condition_status' => ['required', Rule::in(['new', 'good', 'used', 'repaired', 'damaged'])],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $sourceLocationId = (int) $this->input('source_location_id');
            $targetLocationId = $this->input('target_location_id');

            if ($targetLocationId && (int) $targetLocationId === $sourceLocationId) {
                $validator->errors()->add('target_location_id', 'Lokasi tujuan tidak boleh sama dengan lokasi asal.');
            }

            foreach ($this->input('items', []) as $index => $line) {
                $itemId = (int) ($line['item_id'] ?? 0);
                $qty = (int) ($line['qty'] ?? 0);
                $stock = Stock::query()->where('item_id', $itemId)->where('location_id', $sourceLocationId)->first();
                $available = $stock instanceof Stock ? (int) $stock->qty_available : 0;

                if ($qty > $available) {
                    $validator->errors()->add("items.{$index}.qty", 'Stok tidak cukup pada baris '.($index + 1).". Tersedia {$available}, diminta {$qty}.");
                }

                $item = $itemId > 0 ? Item::query()->find($itemId) : null;
                if ($item?->is_serialized && empty($line['serial_no'])) {
                    $validator->errors()->add("items.{$index}.serial_no", 'Serial number wajib untuk barang serialized.');
                }
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'issue_date' => 'tanggal barang keluar',
            'source_location_id' => 'lokasi asal',
            'recipient_name' => 'nama penerima',
            'pic_user_id' => 'PIC',
            'items.*.item_id' => 'barang',
            'items.*.qty' => 'jumlah',
        ];
    }
}
