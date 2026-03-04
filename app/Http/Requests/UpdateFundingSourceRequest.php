<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundingSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fundingSource = $this->route('funding_source') ?? $this->route('fundingSource');
        $id = $fundingSource?->id;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:funding_sources,name,'.$id],
            'description' => ['nullable', 'string'],
        ];
    }
}
