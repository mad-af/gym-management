<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerController extends Controller
{
    public function __construct(protected CustomerService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_CUSTOMERS->value)->only(['index', 'show', 'selection']);
        $this->middleware('permission:'.Permission::CREATE_CUSTOMERS->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_CUSTOMERS->value)->only(['update']);
        $this->middleware('permission:'.Permission::DELETE_CUSTOMERS->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $customers = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
        );

        return ApiResponse::success('Customers retrieved successfully.', $customers);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
        );

        return ApiResponse::success('Customers selection retrieved successfully.', $items);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->service->create($request->validated());

        return ApiResponse::success('Customer created successfully.', $customer, 201);
    }

    public function show(Customer $customer)
    {
        return ApiResponse::success('Customer details retrieved successfully.', $customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $updated = $this->service->update($customer, $request->validated());

        return ApiResponse::success('Customer updated successfully.', $updated);
    }

    public function destroy(Customer $customer)
    {
        $this->service->delete($customer);

        return ApiResponse::success('Customer deleted successfully.');
    }
}
