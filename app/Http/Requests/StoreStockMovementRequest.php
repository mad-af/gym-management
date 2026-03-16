<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'type' => ['required', Rule::in(['IN', 'ADJUSTMENT'])],
            'quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
