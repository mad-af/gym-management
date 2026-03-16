<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'app_description' => ['nullable', 'string', 'max:500'],
            'daily_visit_price' => ['required', 'numeric', 'min:0'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }
}
