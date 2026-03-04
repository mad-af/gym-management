<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Models\AssetProposal;
use App\Services\AssetProposalService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AssetProposalController extends Controller
{
    public function __construct(protected AssetProposalService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_ASSET_PROPOSALS->value)->only(['index', 'show', 'stats']);
        $this->middleware('permission:'.Permission::CREATE_ASSET_PROPOSALS->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_ASSET_PROPOSALS->value)->only([
            'update',
            'approve',
            'reject',
            'complete',
        ]);
        $this->middleware('permission:'.Permission::DELETE_ASSET_PROPOSALS->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $currentOpd = $request->session()->get('current_opd_id');

        $proposedByUserId = null;

        if ($user && ! $user->can(Permission::EDIT_ASSET_PROPOSALS->value)) {
            $proposedByUserId = $user->id;
        }

        $result = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('status'),
            $currentOpd,
            $request->input('category_id'),
            $request->input('date_from'),
            $request->input('date_to'),
            $proposedByUserId,
        );

        return ApiResponse::success('Asset proposals retrieved successfully.', $result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'uuid', 'exists:asset_categories,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'specification' => ['nullable', 'string'],
            'qty' => ['required', 'integer', 'min:1'],
            'estimated_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $currentOpd = $request->session()->get('current_opd_id');

        // Add opd_id from session
        if ($currentOpd) {
            $validated['opd_id'] = $currentOpd;
        }

        $proposal = $this->service->create($validated, $user->id, $currentOpd);

        return ApiResponse::success('Asset proposal created successfully.', $proposal, 201);
    }

    public function show(AssetProposal $proposal)
    {
        $data = $this->service->detail($proposal);

        return ApiResponse::success('Asset proposal details retrieved successfully.', $data);
    }

    public function update(Request $request, AssetProposal $proposal)
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'uuid', 'exists:asset_categories,id'],
            'item_name' => ['sometimes', 'string', 'max:255'],
            'specification' => ['sometimes', 'nullable', 'string'],
            'qty' => ['sometimes', 'integer', 'min:1'],
            'estimated_price' => ['sometimes', 'numeric', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $currentOpd = $request->session()->get('current_opd_id');

        $updated = $this->service->update($proposal, $validated, $currentOpd);

        return ApiResponse::success('Asset proposal updated successfully.', $updated);
    }

    public function destroy(Request $request, AssetProposal $proposal)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $this->service->delete($proposal, $currentOpd);

        return ApiResponse::success('Asset proposal deleted successfully.');
    }

    public function approve(Request $request, AssetProposal $proposal)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $proposal = $this->service->approve($proposal, $currentOpd);

        return ApiResponse::success('Asset proposal approved successfully.', $proposal);
    }

    public function reject(Request $request, AssetProposal $proposal)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $proposal = $this->service->reject($proposal, $currentOpd);

        return ApiResponse::success('Asset proposal rejected successfully.', $proposal);
    }

    public function complete(Request $request, AssetProposal $proposal)
    {
        $currentOpd = $request->session()->get('current_opd_id');
        $user = $request->user();

        $proposal = $this->service->complete($proposal, $user->id, $currentOpd);

        return ApiResponse::success('Asset proposal completed successfully.', $proposal);
    }

    public function stats(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $stats = $this->service->getStats($currentOpd);

        return ApiResponse::success('Asset proposal statistics retrieved successfully.', $stats);
    }
}
