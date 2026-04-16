<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', Rule::in(['CASH', 'DEBIT_CARD', 'CREDIT_CARD', 'E_WALLET', 'QRIS', 'TRANSFER'])],
        ];
    }
}
