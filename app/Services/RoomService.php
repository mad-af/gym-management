<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class RoomService
{
    public function getAll(int $perPage = 10, ?string $search = null, int $page = 1, ?string $opdId = null, ?string $status = null): LengthAwarePaginator
    {
        $query = Room::with('opd')->latest();

        if ($opdId) {
            $query->opd($opdId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereHas('opd', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(int $perPage = 20, ?string $search = null, int $page = 1, ?string $opdId = null): LengthAwarePaginator
    {
        $query = Room::query()
            ->where('status', StatusEnum::ACTIVE)
            ->select(['id', 'name', 'code', 'opd_id']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($opdId) {
            $query->opd($opdId);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Room
    {
        $data['status'] = StatusEnum::ACTIVE;
        $data['qr_id'] = $data['qr_id'] ?? $this->generateQrId();

        return Room::create($data);
    }

    protected function generateQrId(): string
    {
        $length = 8;
        do {
            $id = Str::random($length);
        } while (Room::where('qr_id', $id)->exists());

        return $id;
    }

    public function update(Room $room, array $data): Room
    {
        $room->update($data);

        return $room;
    }

    public function delete(Room $room): bool
    {
        return $this->deactivate($room);
    }

    public function deactivate(Room $room): bool
    {
        return $room->update(['status' => StatusEnum::INACTIVE]);
    }

    public function activate(Room $room): bool
    {
        return $room->update(['status' => StatusEnum::ACTIVE]);
    }
}
