<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\MediaService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(protected UserService $service, protected MediaService $mediaService)
    {
        $this->middleware('permission:view_users')->only('index', 'show', 'selection');
        $this->middleware('permission:create_users')->only('store');
        $this->middleware('permission:edit_users')->only('update');
        $this->middleware('permission:delete_users')->only('destroy');
        $this->middleware('permission:activate_users')->only('activate');
    }

    public function index(Request $request)
    {
        $users = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search', ''),
            $request->input('page', 1),
            $request->input('role'),
            $request->input('is_active'),
        );

        return ApiResponse::success('Users retrieved successfully.', $users);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1)
        );

        return ApiResponse::success('Users selection retrieved successfully', $items);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->service->create($request->validated());

        if ($request->hasFile('avatar')) {
            $this->mediaService->upload($request->file('avatar'), $user, 'avatar');
        }

        return ApiResponse::success('User created successfully.', $user, 201);
    }

    public function show(User $user)
    {
        return ApiResponse::success('User details retrieved successfully.', $user->load(['roles', 'employee', 'opds']));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $updatedUser = $this->service->update($user, $request->validated());

        if ($request->hasFile('avatar')) {
            $this->mediaService->upload($request->file('avatar'), $updatedUser, 'avatar');
        }

        return ApiResponse::success('User updated successfully.', $updatedUser);
    }

    public function destroy(User $user)
    {
        $this->service->delete($user);

        return ApiResponse::success('User deleted successfully.');
    }

    public function activate(User $user)
    {
        $this->service->activate($user);

        return ApiResponse::success('User activated successfully.');
    }
}
