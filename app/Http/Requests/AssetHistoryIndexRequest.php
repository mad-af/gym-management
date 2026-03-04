<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetHistoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'in:created,updated,deleted,restored'],
            'asset_id' => ['nullable', 'uuid', 'exists:assets,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
