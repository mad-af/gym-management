<?php

namespace App\Http\Requests\AssetEmployee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
        ];
    }
}
