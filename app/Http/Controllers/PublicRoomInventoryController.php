<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Room;
use App\Services\PublicRoomInventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicRoomInventoryController extends Controller
{
    public function __construct(protected PublicRoomInventoryService $service)
    {
        // No auth middleware needed for public access
    }

    public function show(Room $room): Response
    {
        return Inertia::render('Public/RoomInventory/Detail', [
            'roomId' => $room->id,
        ]);
    }

    public function data(Room $room)
    {
        $data = $this->service->getRoomInventoryDetail($room->id);

        return ApiResponse::success('Room inventory detail retrieved successfully.', $data);
    }

    public function assets(Request $request, Room $room)
    {
        $assets = $this->service->getRoomAssets(
            $room->id,
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('status'),
            $request->input('condition'),
        );

        return ApiResponse::success('Room inventory assets retrieved successfully.', $assets);
    }

    public function pdf(Room $room)
    {
        return $this->service->generateRoomInventoryPdf($room->id);
    }
}
