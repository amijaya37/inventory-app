<?php

namespace App\Http\Requests\Transaction;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Master\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStockMutationRequest extends FormRequest
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
        return $this->user()?->can('mutations.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'mutation_date' => ['required', 'date'],
            'source_location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
            'destination_location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
            'requested_by' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', Rule::exists('items', 'id')->where('is_active', true)],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.serial_no' => ['nullable', 'string', 'max:100'],
            'items.*.condition_status' => ['nullable', Rule::in(['layak_pakai', 'rusak_ringan', 'rusak_berat', 'scrap'])],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $sourceLocationId = (int) $this->input('source_location_id');
            $destinationLocationId = (int) $this->input('destination_location_id');

            if ($sourceLocationId > 0 && $sourceLocationId === $destinationLocationId) {
                $validator->errors()->add('destination_location_id', 'Lokasi tujuan tidak boleh sama dengan lokasi asal.');
            }

            $grouped = [];
            foreach ($this->input('items', []) as $line) {
                if (! is_array($line)) {
                    continue;
                }
                $itemId = (int) ($line['item_id'] ?? 0);
                $qty = (int) ($line['qty'] ?? 0);
                if ($itemId <= 0) {
                    continue;
                }
                $grouped[$itemId] = ($grouped[$itemId] ?? 0) + $qty;
            }

            foreach ($grouped as $itemId => $requiredQty) {
                $stock = Stock::query()
                    ->where('item_id', $itemId)
                    ->where('location_id', $sourceLocationId)
                    ->first();

                if (! $stock || $stock->qty_available < $requiredQty) {
                    $validator->errors()->add('items', "Stok item ID {$itemId} di lokasi asal tidak cukup.");
                }
            }

            foreach ($this->input('items', []) as $index => $line) {
                $itemId = (int) ($line['item_id'] ?? 0);
                $item = $itemId > 0 ? Item::query()->find($itemId) : null;

                if ($item?->is_serialized && empty($line['serial_no'])) {
                    $validator->errors()->add("items.{$index}.serial_no", 'Serial number wajib untuk barang serialized.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'source_location_id.required' => 'Lokasi asal wajib dipilih.',
            'destination_location_id.required' => 'Lokasi tujuan wajib dipilih.',
            'items.required' => 'Minimal satu barang harus dimutasi.',
            'items.*.qty.min' => 'Qty mutasi minimal 1.',
        ];
    }
}
