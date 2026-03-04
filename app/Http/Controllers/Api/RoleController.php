<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoleController extends Controller
{
    public function __construct(protected RoleService $service)
    {
        $this->middleware('permission:view_roles')->only('index', 'show', 'selection');
        $this->middleware('permission:create_roles')->only('store');
        $this->middleware('permission:edit_roles')->only('update');
        $this->middleware('permission:delete_roles')->only('destroy');
        $this->middleware('permission:activate_roles')->only('activate');

    }

    public function index(Request $request)
    {

        $roles = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search', ''),
            $request->input('page', 1),
            $request->input('is_active')
        );

        return ApiResponse::success('Roles retrieved successfully', $roles);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1)
        );

        return ApiResponse::success('Roles selection retrieved successfully', $items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = $this->service->create($request->all());

        return ApiResponse::success('Role created successfully.', $role, 201);
    }

    public function show(Role $role)
    {
        return ApiResponse::success('Role details', $role->load('permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $updatedRole = $this->service->update($role, $request->all());

        return ApiResponse::success('Role updated successfully.', $updatedRole);
    }

    public function destroy(Role $role)
    {
        $this->service->delete($role);

        return ApiResponse::success('Role deleted successfully.');
    }

    public function permissions()
    {
        $permissions = $this->service->getGroupedPermissions();

        return ApiResponse::success('Permissions retrieved successfully', $permissions);
    }

    public function activate(Role $role)
    {
        $this->service->activate($role);

        return ApiResponse::success('Role activated successfully.');
    }
}
