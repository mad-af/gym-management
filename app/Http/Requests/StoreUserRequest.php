<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'employee_id' => ['nullable', 'exists:employees,id', 'unique:users,employee_id'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,name'],
            'has_all_opds' => ['required', 'boolean'],
            'opd_ids' => ['required_if:has_all_opds,false', 'array'],
            'opd_ids.*' => ['uuid', 'exists:opds,id'],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }
}
