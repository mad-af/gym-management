<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Room;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QrRedirectService
{
    /**
     * Get the redirect URL based on the QR type and ID.
     *
     * @throws NotFoundHttpException|ModelNotFoundException
     */
    public function getRedirectUrl(string $type, string $qrId): string
    {
        if ($type === 'a') {
            $asset = Asset::where('qr_id', $qrId)->firstOrFail();

            return route('public.asset.show', ['id' => $asset->id]);
        }

        if ($type === 'r') {
            $room = Room::where('qr_id', $qrId)->firstOrFail();

            return route('public.room-inventory.show', ['room' => $room->id]);
        }

        abort(404);
    }
}
