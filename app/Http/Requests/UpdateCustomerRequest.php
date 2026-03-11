<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')->id ?? null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'qr_code' => ['nullable', 'string', 'max:255', 'unique:customers,qr_code,'.$customerId],
            'code' => ['nullable', 'string', 'max:50', 'regex:/^\d+$/', 'unique:customers,code,'.$customerId],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp,svg', 'max:10240'],
        ];
    }
}
