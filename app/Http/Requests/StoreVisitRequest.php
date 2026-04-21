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
            'customer_id' => ['required_without:code', 'nullable', 'uuid', 'exists:customers,id'],
            'code' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'membership_transaction_id' => ['nullable', 'uuid', 'exists:membership_transactions,id'],
            'visit_type' => ['required', Rule::in(['MEMBERSHIP', 'DAILY'])],
            'price' => ['nullable', 'numeric', 'min:0'],
            'payment_type' => ['nullable', 'string', Rule::in(['CASH', 'QRIS'])],
            'payment_proof' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'checkin_method' => ['required', Rule::in(['QR_CODE', 'CARD', 'MANUAL'])],
            'checkin_time' => ['nullable', 'date'],
        ];
    }
}
