<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreFundingSourceRequest;
use App\Http\Requests\UpdateFundingSourceRequest;
use App\Models\FundingSource;
use App\Services\FundingSourceService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FundingSourceController extends Controller
{
    public function __construct(protected FundingSourceService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_FUNDING_SOURCES->value)->only(['index', 'show', 'selection']);
        $this->middleware('permission:'.Permission::CREATE_FUNDING_SOURCES->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_FUNDING_SOURCES->value)->only(['update']);
        $this->middleware('permission:'.Permission::DELETE_FUNDING_SOURCES->value)->only(['destroy']);
        $this->middleware('permission:'.Permission::ACTIVATE_FUNDING_SOURCES->value)->only(['activate']);
    }

    public function index(Request $request)
    {
        $result = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('status'),
        );

        return ApiResponse::success('Funding sources retrieved successfully.', $result);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
        );

        return ApiResponse::success('Funding sources selection retrieved successfully.', $items);
    }

    public function store(StoreFundingSourceRequest $request)
    {
        $fundingSource = $this->service->create($request->validated());

        return ApiResponse::success('Funding source created successfully.', $fundingSource, 201);
    }

    public function show(FundingSource $fundingSource)
    {
        return ApiResponse::success('Funding source details retrieved successfully.', $fundingSource);
    }

    public function update(UpdateFundingSourceRequest $request, FundingSource $fundingSource)
    {
        $updated = $this->service->update($fundingSource, $request->validated());

        return ApiResponse::success('Funding source updated successfully.', $updated);
    }

    public function destroy(FundingSource $fundingSource)
    {
        $this->service->delete($fundingSource);

        return ApiResponse::success('Funding source deleted successfully.');
    }

    public function activate(FundingSource $fundingSource)
    {
        $this->service->activate($fundingSource);

        return ApiResponse::success('Funding source activated successfully.');
    }
}
