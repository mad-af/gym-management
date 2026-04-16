<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'package_id' => ['required', 'uuid', 'exists:membership_packages,id'],
            'status' => ['nullable', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', Rule::in(['CASH', 'DEBIT_CARD', 'CREDIT_CARD', 'E_WALLET', 'QRIS', 'TRANSFER'])],
        ];
    }
}
