<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreOpdRequest;
use App\Http\Requests\UpdateOpdRequest;
use App\Models\Opd;
use App\Services\OpdService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OpdController extends Controller
{
    public function __construct(protected OpdService $service)
    {
        $this->middleware('permission:view_opds')->only('index', 'show', 'selection');
        $this->middleware('permission:create_opds')->only('store');
        $this->middleware('permission:edit_opds')->only('update');
        $this->middleware('permission:delete_opds')->only('destroy');
        $this->middleware('permission:activate_opds')->only('activate');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $currentOpdId = session('current_opd_id');

        $opds = $this->service->getAll(
            $user,
            $request->input('per_page', 10),
            $request->input('search', ''),
            $request->input('page', 1),
            $request->input('status'),
            $currentOpdId
        );

        return ApiResponse::success('OPDs retrieved successfully.', $opds);
    }

    public function selection(Request $request)
    {
        $user = $request->user();
        $currentOpdId = session('current_opd_id');

        $items = $this->service->getSelection(
            $user,
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
            $currentOpdId
        );

        return ApiResponse::success('OPDs selection retrieved successfully', $items);
    }

    public function store(StoreOpdRequest $request)
    {
        $opd = $this->service->create($request->validated());

        return ApiResponse::success('OPD created successfully.', $opd, 201);
    }

    public function show(Opd $opd)
    {
        return ApiResponse::success('OPD details retrieved successfully.', $opd->load('head'));
    }

    public function update(UpdateOpdRequest $request, Opd $opd)
    {
        $updatedOpd = $this->service->update($opd, $request->validated());

        return ApiResponse::success('OPD updated successfully.', $updatedOpd);
    }

    public function destroy(Opd $opd)
    {
        $this->service->delete($opd);

        return ApiResponse::success('OPD deleted successfully.');
    }

    public function activate(Opd $opd)
    {
        $this->service->activate($opd);

        return ApiResponse::success('OPD activated successfully.');
    }
}
