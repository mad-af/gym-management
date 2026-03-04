<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeCategory = $this->route('asset_category') ?? $this->route('assetCategory');
        $categoryId = $routeCategory?->id;

        return [
            'parent_id' => [
                'nullable',
                'uuid',
                'exists:asset_categories,id',
                $categoryId ? Rule::notIn([$categoryId]) : Rule::notIn([]),
            ],
            'code' => ['required', 'string', 'max:100', 'unique:asset_categories,code,'.$categoryId],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('asset_categories', 'name')
                    ->where(function ($query) {
                        $parentId = $this->input('parent_id');

                        if ($parentId) {
                            $query->where('parent_id', $parentId);
                        } else {
                            $query->whereNull('parent_id');
                        }
                    })
                    ->ignore($categoryId),
            ],
            'useful_life_years' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
