<?php

namespace App\Services;

use App\Models\AssetCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AssetCategoryService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $parentId = null
    ): LengthAwarePaginator {
        $query = AssetCategory::query()
            ->with('parent')
            ->latest();

        if ($parentId) {
            $query->where('parent_id', $parentId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(
        int $perPage = 20,
        ?string $search = null,
        int $page = 1,
        ?string $parentId = null,
        bool $onlyParents = false,
        ?int $level = null
    ): LengthAwarePaginator {
        $query = AssetCategory::query()
            ->select(['id', 'code', 'name', 'parent_id'])
            ->orderBy('name');

        if ($onlyParents) {
            $query->whereNull('parent_id');
        } elseif ($parentId) {
            $query->where('parent_id', $parentId);
        }

        if ($level !== null) {
            $query->where('level', $level);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): AssetCategory
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parent = AssetCategory::query()->findOrFail($parentId);

            if ((int) $parent->level !== 1) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Parent category must be level 1.'],
                ]);
            }

            $data['level'] = 2;
        } else {
            $data['level'] = 1;
        }

        return AssetCategory::create($data);
    }

    public function update(AssetCategory $category, array $data): AssetCategory
    {
        $newParentId = array_key_exists('parent_id', $data)
            ? $data['parent_id']
            : $category->parent_id;

        if ($newParentId && $category->id === $newParentId) {
            throw ValidationException::withMessages([
                'parent_id' => ['Category cannot be its own parent.'],
            ]);
        }

        if ($category->level === 2 && array_key_exists('parent_id', $data) && $newParentId !== $category->parent_id) {
            throw ValidationException::withMessages([
                'parent_id' => ['Level 2 category cannot change parent.'],
            ]);
        }

        if ($newParentId) {
            if ($category->children()->exists()) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Category with children cannot become a child.'],
                ]);
            }

            $parent = AssetCategory::query()->findOrFail($newParentId);

            if ((int) $parent->level !== 1) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Parent category must be level 1.'],
                ]);
            }

            if ($category->children()->whereKey($newParentId)->exists()) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Circular parent-child relation is not allowed.'],
                ]);
            }

            $data['level'] = 2;
        } else {
            $data['level'] = 1;
        }

        $category->update($data);

        return $category;
    }

    public function delete(AssetCategory $category): bool
    {
        if ((int) $category->level === 1) {
            throw ValidationException::withMessages([
                'id' => ['Parent category cannot be deleted.'],
            ]);
        }

        return (bool) $category->delete();
    }
}
