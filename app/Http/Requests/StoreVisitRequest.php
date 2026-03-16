<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required_without:qr_code', 'nullable', 'uuid', 'exists:customers,id'],
            'qr_code' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'membership_transaction_id' => ['nullable', 'uuid', 'exists:membership_transactions,id'],
            'visit_type' => ['required', Rule::in(['MEMBERSHIP', 'DAILY'])],
            'price' => ['nullable', 'numeric', 'min:0'],
            'checkin_method' => ['required', Rule::in(['QR_CODE', 'CARD', 'MANUAL'])],
            'checkin_time' => ['nullable', 'date'],
        ];
    }
}
