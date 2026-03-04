<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurrentOpdService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CurrentOpdController extends Controller
{
    public function __construct(
        private readonly CurrentOpdService $currentOpdService
    ) {}

    public function update(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $this->currentOpdService->updateCurrentOpd($user, $request->input('opd_id'));

            return response()->json([
                'message' => 'Current OPD updated successfully.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
