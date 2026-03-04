<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class CompleteMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'completed_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'log_notes' => ['nullable', 'string'],
        ];
    }
}
