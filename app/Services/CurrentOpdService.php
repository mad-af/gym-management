<?php

namespace App\Services;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CurrentOpdService
{
    /**
     * Update current OPD for the authenticated user
     *
     * @throws ValidationException
     */
    public function updateCurrentOpd(User $user, ?string $opdId): void
    {
        // Validate OPD ID
        $this->validateOpdId($opdId, $user);

        if ($opdId) {
            // Check access permission
            $this->checkOpdAccess($user, $opdId);

            // Update session
            session(['current_opd_id' => $opdId]);
            session()->forget('current_opd_id_cleared'); // Clear the marker since user selected an OPD
        } else {
            // Clear current OPD (user selects "Semua OPD")
            // Permission check already done in validateOpdId
            session()->forget('current_opd_id');
            session(['current_opd_id_cleared' => true]); // Mark that user intentionally cleared OPD selection
        }
    }

    /**
     * Validate OPD ID format and existence
     *
     * @throws ValidationException
     */
    private function validateOpdId(?string $opdId, User $user): void
    {
        if ($opdId === null) {
            // Only users with has_all_opds permission can select "Semua OPD"
            if (! $user->has_all_opds) {
                throw ValidationException::withMessages([
                    'opd_id' => 'Anda tidak memiliki izin untuk memilih Semua OPD',
                ]);
            }

            return; // Valid - user wants to clear selection
        }

        if (! is_string($opdId)) {
            throw ValidationException::withMessages([
                'opd_id' => 'OPD ID harus berupa string',
            ]);
        }

        // Check if OPD exists
        $opdExists = Opd::where('id', $opdId)->exists();
        if (! $opdExists) {
            throw ValidationException::withMessages([
                'opd_id' => 'OPD tidak ditemukan',
            ]);
        }
    }

    /**
     * Check if user has access to the specified OPD
     *
     * @throws ValidationException
     */
    private function checkOpdAccess(User $user, string $opdId): void
    {
        $opdQuery = $user->has_all_opds
            ? Opd::query()
            : $user->opds();

        $hasAccess = $opdQuery->where('id', $opdId)->exists();

        if (! $hasAccess) {
            throw ValidationException::withMessages([
                'opd_id' => 'Anda tidak memiliki akses ke OPD ini',
            ]);
        }
    }

    /**
     * Get current OPD for user from session
     */
    public function getCurrentOpd(User $user): ?Opd
    {
        $currentOpdId = session('current_opd_id');

        if (! $currentOpdId) {
            return null;
        }

        $opdQuery = $user->has_all_opds
            ? Opd::query()
            : $user->opds();

        return $opdQuery->find($currentOpdId);
    }

    /**
     * Set default OPD for user if not already set
     */
    public function setDefaultOpd(User $user): ?Opd
    {
        $currentOpdId = session('current_opd_id');

        if ($currentOpdId) {
            return $this->getCurrentOpd($user);
        }

        $opdQuery = $user->has_all_opds
            ? Opd::query()
            : $user->opds();

        $availableOpds = $opdQuery->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        if ($availableOpds->isEmpty()) {
            return null;
        }

        $defaultOpd = $availableOpds->first();
        session(['current_opd_id' => $defaultOpd->id]);
        session()->forget('current_opd_id_cleared'); // Clear the marker since we're setting a default

        return $defaultOpd;
    }
}
