<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMembershipCardWhatsappRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'target' => ['nullable', 'string', 'max:30'],
        ];
    }
}
