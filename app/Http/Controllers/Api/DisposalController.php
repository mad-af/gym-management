<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\Disposal\StoreDisposalRequest;
use App\Http\Resources\DisposalDocumentResource;
use App\Models\DisposalDocument;
use App\Services\DisposalService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DisposalController extends Controller
{
    public function __construct(protected DisposalService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_ASSET_DISPOSALS->value)->only(['index', 'show', 'stats']);
        $this->middleware('permission:'.Permission::CREATE_ASSET_DISPOSALS->value)->only(['store']);
    }

    public function index(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $documents = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $currentOpd,
            $request->input('disposal_type'),
            $request->input('date_from'),
            $request->input('date_to'),
        );

        return ApiResponse::success('Disposal documents retrieved successfully.', $documents);
    }

    public function stats(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $stats = $this->service->getStats($currentOpd);

        return ApiResponse::success(
            'Disposal statistics retrieved successfully.',
            $stats
        );
    }

    public function store(StoreDisposalRequest $request)
    {
        $user = $request->user();
        $currentOpd = $request->session()->get('current_opd_id');

        $document = $this->service->createDisposalDocument(
            $request->validated(),
            $user->id,
            $currentOpd
        );

        return ApiResponse::success(
            'Disposal document created successfully.',
            new DisposalDocumentResource($document),
            201
        );
    }

    public function show(DisposalDocument $disposal)
    {
        $document = $this->service->findById($disposal->id);

        return ApiResponse::success(
            'Disposal document details retrieved successfully.',
            new DisposalDocumentResource($document)
        );
    }
}
