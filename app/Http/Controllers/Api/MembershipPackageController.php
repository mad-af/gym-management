<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMembershipPackageRequest;
use App\Http\Requests\UpdateMembershipPackageRequest;
use App\Models\MembershipPackage;
use App\Services\MembershipPackageService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MembershipPackageController extends Controller
{
    public function __construct(protected MembershipPackageService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_MEMBERSHIP_PACKAGES->value)->only(['index', 'show', 'selection']);
        $this->middleware('permission:'.Permission::CREATE_MEMBERSHIP_PACKAGES->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_MEMBERSHIP_PACKAGES->value)->only(['update']);
        $this->middleware('permission:'.Permission::DELETE_MEMBERSHIP_PACKAGES->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $packages = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->boolean('is_active', null),
        );

        return ApiResponse::success('Membership packages retrieved successfully.', $packages);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
            $request->has('is_active') ? $request->boolean('is_active') : true,
        );

        return ApiResponse::success('Membership packages selection retrieved successfully.', $items);
    }

    public function store(StoreMembershipPackageRequest $request)
    {
        $package = $this->service->create($request->validated());

        return ApiResponse::success('Membership package created successfully.', $package, 201);
    }

    public function show(MembershipPackage $membershipPackage)
    {
        return ApiResponse::success('Membership package details retrieved successfully.', $membershipPackage->load(['items']));
    }

    public function update(UpdateMembershipPackageRequest $request, MembershipPackage $membershipPackage)
    {
        $updated = $this->service->update($membershipPackage, $request->validated());

        return ApiResponse::success('Membership package updated successfully.', $updated);
    }

    public function destroy(MembershipPackage $membershipPackage)
    {
        $this->service->delete($membershipPackage);

        return ApiResponse::success('Membership package deleted successfully.');
    }
}
