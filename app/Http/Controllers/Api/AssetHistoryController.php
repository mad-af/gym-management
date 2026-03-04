<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Requests\AssetHistoryIndexRequest;
use App\Services\AssetHistoryService;
use Illuminate\Routing\Controller;

class AssetHistoryController extends Controller
{
    public function __construct(protected AssetHistoryService $service)
    {
        $this->middleware('permission:view_assets')->only('index');
    }

    public function index(AssetHistoryIndexRequest $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $result = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('action'),
            $request->input('asset_id'),
            $request->input('date_from'),
            $request->input('date_to'),
            $currentOpd,
        );

        return ApiResponse::success('Asset histories retrieved successfully.', $result);
    }
}
