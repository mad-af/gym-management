<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VisitController extends Controller
{
    public function __construct(protected VisitService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_VISITS->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_VISITS->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_VISITS->value)->only(['update']);
        $this->middleware('permission:'.Permission::DELETE_VISITS->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $visits = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('customer_id'),
            $request->input('visit_type'),
        );

        return ApiResponse::success('Visits retrieved successfully.', $visits);
    }

    public function store(StoreVisitRequest $request)
    {
        $data = $request->validated();

        if (empty($data['checkin_time'])) {
            unset($data['checkin_time']);
        }

        $visit = $this->service->create($data, $request->user()?->id);

        return ApiResponse::success('Visit created successfully.', $visit, 201);
    }

    public function show(Visit $visit)
    {
        return ApiResponse::success('Visit details retrieved successfully.', $visit->load(['customer', 'membershipTransaction', 'creator']));
    }

    public function update(UpdateVisitRequest $request, Visit $visit)
    {
        $updated = $this->service->update($visit, $request->validated());

        return ApiResponse::success('Visit updated successfully.', $updated);
    }

    public function destroy(Visit $visit)
    {
        $this->service->delete($visit);

        return ApiResponse::success('Visit deleted successfully.');
    }
}
