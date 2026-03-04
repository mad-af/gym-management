<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\RoomInventory\RoomInventoryAssetsRequest;
use App\Http\Requests\RoomInventory\RoomInventoryIndexRequest;
use App\Models\Room;
use App\Services\RoomInventoryService;
use Illuminate\Routing\Controller;

class RoomInventoryController extends Controller
{
    public function __construct(protected RoomInventoryService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_ROOM_INVENTORY->value)->only(['index', 'show', 'assets']);
        $this->middleware('permission:'.Permission::GENERATE_ROOM_INVENTORY_PDF->value)->only('pdf');
    }

    public function index(RoomInventoryIndexRequest $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $rooms = $this->service->getRoomsWithAssetCounts(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $currentOpd,
        );

        return ApiResponse::success('Rooms inventory retrieved successfully.', $rooms);
    }

    public function show(Room $room)
    {
        $data = $this->service->getRoomInventoryDetail($room);

        return ApiResponse::success('Room inventory detail retrieved successfully.', $data);
    }

    public function assets(RoomInventoryAssetsRequest $request, Room $room)
    {
        $assets = $this->service->getRoomAssets(
            $room,
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('status'),
        );

        return ApiResponse::success('Room inventory assets retrieved successfully.', $assets);
    }

    public function pdf(Room $room)
    {
        return $this->service->generateRoomInventoryPdf($room);
    }
}
