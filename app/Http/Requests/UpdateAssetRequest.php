<?php

namespace App\Http\Requests;

use App\Enums\AssetConditionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assetId = $this->route('asset')->id ?? null;

        return [
            'asset_code' => ['required', 'string', 'max:100', 'unique:assets,asset_code,'.$assetId],
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'category_id' => ['required', 'exists:asset_categories,id'],
            'opd_id' => ['required', 'exists:opds,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'funding_source_id' => ['nullable', 'uuid', 'exists:funding_sources,id'],
            'condition' => ['required', new Enum(AssetConditionEnum::class)],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
