<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_opd_id' => ['required', 'uuid', 'exists:opds,id'],
            'to_opd_id' => ['required', 'uuid', 'exists:opds,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.asset_id' => ['required', 'uuid', 'exists:assets,id'],
            'items.*.from_room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'items.*.to_room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
        ];
    }
}
