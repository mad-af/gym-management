<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMembershipPackageRequest;
use App\Http\Requests\UpdateMembershipPackageRequest;
use App\Models\MembershipPackage;
use App\Services\MediaService;
use App\Services\MembershipPackageService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MembershipPackageController extends Controller
{
    public function __construct(protected MembershipPackageService $service, protected MediaService $mediaService)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_MEMBERSHIP_PACKAGES->value)->only(['index', 'show', 'selection', 'stats']);
        $this->middleware('permission:'.Permission::CREATE_MEMBERSHIP_PACKAGES->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_MEMBERSHIP_PACKAGES->value)->only(['update', 'activate']);
        $this->middleware('permission:'.Permission::DELETE_MEMBERSHIP_PACKAGES->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : null;

        $packages = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $isActive,
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

    public function stats(Request $request)
    {
        $stats = $this->service->getStats();

        return ApiResponse::success('Membership packages statistics retrieved successfully.', $stats);
    }

    public function store(StoreMembershipPackageRequest $request)
    {
        $data = $request->validated();
        unset($data['cover']);

        $package = $this->service->create($data);

        if ($request->hasFile('cover')) {
            $this->mediaService->upload($request->file('cover'), $package, 'cover');
            $package->load(['items', 'media']);
        }

        return ApiResponse::success('Membership package created successfully.', $package, 201);
    }

    public function show(MembershipPackage $membershipPackage)
    {
        return ApiResponse::success('Membership package details retrieved successfully.', $membershipPackage->load(['items', 'media']));
    }

    public function update(UpdateMembershipPackageRequest $request, MembershipPackage $membershipPackage)
    {
        $data = $request->validated();
        unset($data['cover']);

        $updated = $this->service->update($membershipPackage, $data);

        if ($request->hasFile('cover')) {
            $this->mediaService->upload($request->file('cover'), $updated, 'cover');
            $updated->load(['items', 'media']);
        }

        return ApiResponse::success('Membership package updated successfully.', $updated);
    }

    public function destroy(MembershipPackage $membershipPackage)
    {
        $this->service->delete($membershipPackage);

        return ApiResponse::success('Membership package deactivated successfully.');
    }

    public function activate(MembershipPackage $membershipPackage)
    {
        $this->service->activate($membershipPackage);

        return ApiResponse::success('Membership package activated successfully.');
    }
}
