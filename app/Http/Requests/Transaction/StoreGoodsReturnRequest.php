<?php

namespace App\Http\Requests\Transaction;

use App\Domain\Master\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGoodsReturnRequest extends FormRequest
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
        return $this->user()?->can('returns.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required', 'date'],
            'origin_type' => ['required', Rule::in(['user', 'location'])],
            'origin_user_id' => ['nullable', 'required_if:origin_type,user', 'exists:users,id'],
            'origin_location_id' => ['nullable', 'required_if:origin_type,location', 'exists:locations,id'],
            'origin_pic_name' => ['required', 'string', 'max:150'],
            'origin_pic_phone' => ['nullable', 'string', 'max:50'],
            'warehouse_location_id' => ['required', 'exists:locations,id'],
            'return_reason' => ['required', 'string', 'max:2000'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.serial_no' => ['nullable', 'string', 'max:100'],
            'items.*.condition_status' => ['required', Rule::in(['layak_pakai', 'rusak_ringan', 'rusak_berat', 'scrap'])],
            'items.*.final_action' => ['required', Rule::in(['return_to_stock', 'repair', 'scrap', 'dispose'])],
            'items.*.notes' => ['nullable', 'string', 'max:2000'],
            'items.*.photos' => ['nullable', 'array', 'max:5'],
            'items.*.photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ($this->input('items', []) as $index => $line) {
                $condition = $line['condition_status'] ?? null;
                $action = $line['final_action'] ?? null;
                $itemId = (int) ($line['item_id'] ?? 0);
                $item = $itemId > 0 ? Item::query()->find($itemId) : null;

                if ($condition === 'scrap' && $action === 'return_to_stock') {
                    $validator->errors()->add("items.{$index}.final_action", 'Barang scrap tidak boleh dikembalikan ke stok.');
                }

                if ($condition === 'rusak_berat' && $action === 'return_to_stock') {
                    $validator->errors()->add("items.{$index}.final_action", 'Barang rusak berat tidak boleh langsung kembali ke stok.');
                }

                if ($item?->is_serialized && empty($line['serial_no'])) {
                    $validator->errors()->add("items.{$index}.serial_no", 'Serial number wajib untuk barang serialized.');
                }
            }
        }];
    }
}
