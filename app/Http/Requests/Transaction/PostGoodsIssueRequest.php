<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class PostGoodsIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('goods-out.post') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
