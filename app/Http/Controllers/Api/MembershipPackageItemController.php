<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMembershipPackageItemRequest;
use App\Http\Requests\UpdateMembershipPackageItemRequest;
use App\Models\MembershipPackageItem;
use App\Services\MembershipPackageItemService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MembershipPackageItemController extends Controller
{
    public function __construct(protected MembershipPackageItemService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_MEMBERSHIP_PACKAGE_ITEMS->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_MEMBERSHIP_PACKAGE_ITEMS->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_MEMBERSHIP_PACKAGE_ITEMS->value)->only(['update']);
        $this->middleware('permission:'.Permission::DELETE_MEMBERSHIP_PACKAGE_ITEMS->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $items = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('package_id'),
        );

        return ApiResponse::success('Membership package items retrieved successfully.', $items);
    }

    public function store(StoreMembershipPackageItemRequest $request)
    {
        $item = $this->service->create($request->validated());

        return ApiResponse::success('Membership package item created successfully.', $item, 201);
    }

    public function show(MembershipPackageItem $membershipPackageItem)
    {
        return ApiResponse::success('Membership package item details retrieved successfully.', $membershipPackageItem);
    }

    public function update(UpdateMembershipPackageItemRequest $request, MembershipPackageItem $membershipPackageItem)
    {
        $updated = $this->service->update($membershipPackageItem, $request->validated());

        return ApiResponse::success('Membership package item updated successfully.', $updated);
    }

    public function destroy(MembershipPackageItem $membershipPackageItem)
    {
        $this->service->delete($membershipPackageItem);

        return ApiResponse::success('Membership package item deleted successfully.');
    }
}
