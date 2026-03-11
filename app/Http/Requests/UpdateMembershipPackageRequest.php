<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $package = $this->route('membership_package');
        $packageId = is_object($package) ? ($package->id ?? null) : $package;
        $itemExistsRule = Rule::exists('membership_package_items', 'id');
        if ($packageId) {
            $itemExistsRule = $itemExistsRule->where('package_id', $packageId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.id' => [
                'nullable',
                'uuid',
                $itemExistsRule,
            ],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
        ];
    }
}
