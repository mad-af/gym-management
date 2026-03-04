<?php

namespace App\Http\Requests\Disposal;

use App\Enums\DisposalTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDisposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'uuid', 'exists:opds,id'],
            'disposal_type' => ['required', new Enum(DisposalTypeEnum::class)],
            'disposal_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'document_path' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.asset_id' => ['required', 'uuid', 'exists:assets,id'],
            'items.*.reason' => ['required', 'string'],
            'items.*.condition_at_disposal' => ['required', 'string'],
        ];
    }
}
