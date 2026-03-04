<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\Transfer\ApproveTransferRequest;
use App\Http\Requests\Transfer\RejectTransferRequest;
use App\Http\Requests\Transfer\StoreTransferRequest;
use App\Models\TransferRequest;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TransferController extends Controller
{
    public function __construct(protected TransferService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_ASSET_TRANSFERS->value)->only(['index', 'show', 'stats']);
        $this->middleware('permission:'.Permission::CREATE_ASSET_TRANSFERS->value)->only(['store']);
        $this->middleware('permission:'.Permission::APPROVE_ASSET_TRANSFERS->value)->only(['approve', 'reject', 'cancel']);
    }

    public function index(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $transfers = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search', ''),
            $request->input('page', 1),
            $request->input('status'),
            $request->input('type'),
            $currentOpd,
            $currentOpd,
        );

        return ApiResponse::success('Transfer requests retrieved successfully.', $transfers);
    }

    public function store(StoreTransferRequest $request)
    {
        $user = $request->user();
        $currentOpd = $request->session()->get('current_opd_id');

        $transfer = $this->service->createTransfer(
            $request->validated(),
            $user->id,
            $currentOpd
        );

        return ApiResponse::success('Transfer request created successfully.', $transfer, 201);
    }

    public function show(Request $request, TransferRequest $transfer)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $transfer = $this->service->findById($transfer->id, $currentOpd);

        return ApiResponse::success('Transfer request details retrieved successfully.', $transfer);
    }

    public function approve(ApproveTransferRequest $request, TransferRequest $transfer)
    {
        if ($transfer->status->isFinal()) {
            return ApiResponse::error('Completed, rejected or cancelled transfer cannot be modified.', 422);
        }

        $user = $request->user();
        $currentOpd = $request->session()->get('current_opd_id');

        $transfer = $this->service->approveTransfer(
            $transfer,
            $user->id,
            $currentOpd
        );

        return ApiResponse::success('Transfer request approved successfully.', $transfer);
    }

    public function reject(RejectTransferRequest $request, TransferRequest $transfer)
    {
        if ($transfer->status->isFinal()) {
            return ApiResponse::error('Completed, rejected or cancelled transfer cannot be modified.', 422);
        }

        $user = $request->user();
        $currentOpd = $request->session()->get('current_opd_id');

        $transfer = $this->service->rejectTransfer(
            $transfer,
            $user->id,
            $currentOpd,
            $request->validated('reason')
        );

        return ApiResponse::success('Transfer request rejected successfully.', $transfer);
    }

    public function cancel(Request $request, TransferRequest $transfer)
    {
        if ($transfer->status->isFinal()) {
            return ApiResponse::error('Completed, rejected or cancelled transfer cannot be modified.', 422);
        }

        $currentOpd = $request->session()->get('current_opd_id');

        $transfer = $this->service->cancelTransfer($transfer, $currentOpd);

        return ApiResponse::success('Transfer request cancelled successfully.', $transfer);
    }

    public function stats(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $stats = $this->service->getStats($currentOpd);

        return ApiResponse::success('Transfer statistics retrieved successfully.', $stats);
    }
}
