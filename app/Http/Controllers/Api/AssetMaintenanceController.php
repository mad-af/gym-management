<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\Maintenance\CancelMaintenanceRequest;
use App\Http\Requests\Maintenance\CompleteMaintenanceRequest;
use App\Http\Requests\Maintenance\ScheduleMaintenanceRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceRequest;
use App\Models\AssetMaintenance;
use App\Models\AssetMaintenanceLog;
use App\Services\AssetMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AssetMaintenanceController extends Controller
{
    public function __construct(protected AssetMaintenanceService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_ASSET_MAINTENANCES->value)->only(['index', 'show', 'stats']);
        $this->middleware('permission:'.Permission::MANAGE_ASSET_MAINTENANCES->value)->only(['store', 'update', 'complete', 'cancel']);
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $currentOpd = $request->session()->get('current_opd_id');

        if ($currentOpd) {
            $filters['opd_id'] = $currentOpd;
        }

        $maintenances = $this->service->list($filters);

        return ApiResponse::success('Asset maintenances retrieved successfully.', $maintenances);
    }

    public function show(AssetMaintenance $maintenance)
    {
        $data = $this->service->detail($maintenance);

        return ApiResponse::success('Asset maintenance detail retrieved successfully.', $data);
    }

    public function store(ScheduleMaintenanceRequest $request)
    {
        $maintenance = $this->service->schedule($request->validated());

        return ApiResponse::success('Asset maintenance scheduled successfully.', $maintenance, 201);
    }

    public function update(UpdateMaintenanceRequest $request, AssetMaintenance $maintenance)
    {
        $maintenance = $this->service->update($maintenance, $request->validated());

        return ApiResponse::success('Asset maintenance updated successfully.', $maintenance);
    }

    public function start(AssetMaintenance $maintenance)
    {
        $maintenance = $this->service->start($maintenance);

        return ApiResponse::success('Asset maintenance started successfully.', $maintenance);
    }

    public function complete(CompleteMaintenanceRequest $request, AssetMaintenance $maintenance)
    {
        $maintenance = $this->service->complete($maintenance, $request->validated());

        return ApiResponse::success('Asset maintenance completed successfully.', $maintenance);
    }

    public function cancel(CancelMaintenanceRequest $request, AssetMaintenance $maintenance)
    {
        $maintenance = $this->service->cancel($maintenance, $request->input('reason'));

        return ApiResponse::success('Asset maintenance cancelled successfully.', $maintenance);
    }

    public function logs(Request $request, AssetMaintenance $maintenance)
    {
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);

        $query = AssetMaintenanceLog::query()
            ->where('maintenance_id', $maintenance->id)
            ->with('performedBy')
            ->orderByDesc('created_at');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (AssetMaintenanceLog $log) {
            $performedBy = $log->performedBy;

            return [
                'id' => $log->id,
                'notes' => $log->notes,
                'performed_by_name' => $performedBy?->name,
                'performed_by' => $performedBy ? [
                    'name' => $performedBy->name,
                    'email' => $performedBy->email,
                    'avatar' => $performedBy->avatar,
                ] : null,
                'created_at' => $log->created_at,
            ];
        });

        $result = [
            'items' => [
                'data' => $items,
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ];

        return ApiResponse::success('Asset maintenance logs retrieved successfully.', $result);
    }

    public function stats(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $stats = $this->service->getStats($currentOpd);

        return ApiResponse::success('Asset maintenance statistics retrieved successfully.', $stats);
    }
}
