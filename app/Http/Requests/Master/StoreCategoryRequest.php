<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('categories.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('categories', 'code')->withoutTrashed()],
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->withoutTrashed()],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'kode kategori',
            'name' => 'nama kategori',
            'description' => 'deskripsi',
            'is_active' => 'status aktif',
        ];
    }
}
