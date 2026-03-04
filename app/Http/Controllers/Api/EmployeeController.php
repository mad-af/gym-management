<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EmployeeController extends Controller
{
    public function __construct(protected EmployeeService $service, protected MediaService $mediaService)
    {
        $this->middleware('permission:view_employees')->only('index', 'show', 'selection');
        $this->middleware('permission:create_employees')->only('store');
        $this->middleware('permission:edit_employees')->only('update');
        $this->middleware('permission:delete_employees')->only('destroy');
        $this->middleware('permission:activate_employees')->only('activate');
    }

    public function index(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $employees = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search', ''),
            $request->input('page', 1),
            $currentOpd,
            $request->input('status')
        );

        return ApiResponse::success('Employees retrieved successfully.', $employees);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
            $request->boolean('only_without_user')
        );

        return ApiResponse::success('Employees selection retrieved successfully', $items);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $validated = $request->validated();
        $currentOpd = $request->session()->get('current_opd_id');

        // Override opd_id with current session OPD for consistency
        if ($currentOpd) {
            $validated['opd_id'] = $currentOpd;
        }

        $employee = $this->service->create($validated);

        if ($request->hasFile('avatar')) {
            $this->mediaService->upload($request->file('avatar'), $employee, 'avatar');
        }

        return ApiResponse::success('Employee created successfully.', $employee, 201);
    }

    public function show(Employee $employee)
    {
        return ApiResponse::success('Employee details retrieved successfully.', $employee->load('opd'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();
        $currentOpd = $request->session()->get('current_opd_id');

        // Override opd_id with current session OPD for consistency
        if ($currentOpd) {
            $validated['opd_id'] = $currentOpd;
        }

        $updatedEmployee = $this->service->update($employee, $validated);

        if ($request->hasFile('avatar')) {
            $this->mediaService->upload($request->file('avatar'), $updatedEmployee, 'avatar');
        }

        return ApiResponse::success('Employee updated successfully.', $updatedEmployee);
    }

    public function destroy(Employee $employee)
    {
        $this->service->delete($employee);

        return ApiResponse::success('Employee deleted successfully.');
    }

    public function activate(Employee $employee)
    {
        $this->service->activate($employee);

        return ApiResponse::success('Employee activated successfully.');
    }
}
