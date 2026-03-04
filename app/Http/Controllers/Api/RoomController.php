<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoomController extends Controller
{
    public function __construct(protected RoomService $service)
    {
        $this->middleware('permission:view_rooms')->only('index', 'show', 'selection');
        $this->middleware('permission:create_rooms')->only('store');
        $this->middleware('permission:edit_rooms')->only('update');
        $this->middleware('permission:delete_rooms')->only('destroy');
        $this->middleware('permission:activate_rooms')->only('activate');
    }

    public function index(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $rooms = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search', ''),
            $request->input('page', 1),
            $currentOpd,
            $request->input('status')
        );

        return ApiResponse::success('Rooms retrieved successfully.', $rooms);
    }

    public function selection(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
            $currentOpd
        );

        return ApiResponse::success('Rooms selection retrieved successfully', $items);
    }

    public function store(StoreRoomRequest $request)
    {
        $validated = $request->validated();
        $currentOpd = $request->session()->get('current_opd_id');

        // Override opd_id with current session OPD for consistency
        if ($currentOpd) {
            $validated['opd_id'] = $currentOpd;
        }

        $room = $this->service->create($validated);

        return ApiResponse::success('Room created successfully.', $room, 201);
    }

    public function show(Room $room)
    {
        return ApiResponse::success('Room details retrieved successfully.', $room->load('opd'));
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        $validated = $request->validated();
        $currentOpd = $request->session()->get('current_opd_id');

        // Override opd_id with current session OPD for consistency
        if ($currentOpd) {
            $validated['opd_id'] = $currentOpd;
        }

        $updatedRoom = $this->service->update($room, $validated);

        return ApiResponse::success('Room updated successfully.', $updatedRoom);
    }

    public function destroy(Room $room)
    {
        $this->service->delete($room);

        return ApiResponse::success('Room deleted successfully.');
    }

    public function activate(Room $room)
    {
        $this->service->activate($room);

        return ApiResponse::success('Room activated successfully.');
    }
}
