<?php

namespace App\Http\Requests\Master;

use App\Domain\Master\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('categories.update') ?? false;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->id : null;

        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('categories', 'code')->ignore($categoryId)->withoutTrashed()],
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($categoryId)->withoutTrashed()],
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
