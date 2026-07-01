<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class PostStockMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('mutations.post') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
