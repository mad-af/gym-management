<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'membership_transaction_id' => ['nullable', 'uuid', 'exists:membership_transactions,id'],
            'visit_type' => ['required', Rule::in(['MEMBERSHIP', 'DAILY'])],
            'price' => ['nullable', 'numeric', 'min:0'],
            'checkin_method' => ['required', Rule::in(['QR_CODE', 'CARD', 'MANUAL'])],
            'checkin_time' => ['required', 'date'],
        ];
    }
}
