<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMembershipTransactionRequest;
use App\Http\Requests\UpdateMembershipTransactionRequest;
use App\Models\MembershipTransaction;
use App\Services\MembershipTransactionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MembershipTransactionController extends Controller
{
    public function __construct(protected MembershipTransactionService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_MEMBERSHIP_TRANSACTIONS->value)->only(['index', 'show', 'stats']);
        $this->middleware('permission:'.Permission::CREATE_MEMBERSHIP_TRANSACTIONS->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_MEMBERSHIP_TRANSACTIONS->value)->only(['update']);
        $this->middleware('permission:'.Permission::DELETE_MEMBERSHIP_TRANSACTIONS->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $transactions = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('customer_id'),
            $request->input('status'),
        );

        return ApiResponse::success('Membership transactions retrieved successfully.', $transactions);
    }

    public function stats(Request $request)
    {
        $stats = $this->service->getStats();

        return ApiResponse::success('Membership transaction statistics retrieved successfully.', $stats);
    }

    public function store(StoreMembershipTransactionRequest $request)
    {
        $transaction = $this->service->create($request->validated(), $request->user()?->id);

        return ApiResponse::success('Membership transaction created successfully.', $transaction, 201);
    }

    public function show(MembershipTransaction $membershipTransaction)
    {
        return ApiResponse::success('Membership transaction details retrieved successfully.', $membershipTransaction->load(['customer', 'package', 'creator']));
    }

    public function update(UpdateMembershipTransactionRequest $request, MembershipTransaction $membershipTransaction)
    {
        $updated = $this->service->update($membershipTransaction, $request->validated());

        return ApiResponse::success('Membership transaction updated successfully.', $updated);
    }

    public function destroy(MembershipTransaction $membershipTransaction)
    {
        $this->service->delete($membershipTransaction);

        return ApiResponse::success('Membership transaction deleted successfully.');
    }
}
