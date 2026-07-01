<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class PostGoodsReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('returns.post') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
