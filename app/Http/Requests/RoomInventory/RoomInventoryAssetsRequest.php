<?php

namespace App\Http\Requests\RoomInventory;

use Illuminate\Foundation\Http\FormRequest;

class RoomInventoryAssetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
