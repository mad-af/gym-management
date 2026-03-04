<?php

namespace App\Http\Controllers;

use App\Services\QrRedirectService;
use Illuminate\Http\RedirectResponse;

class QrRedirectController extends Controller
{
    public function __construct(
        protected QrRedirectService $qrRedirectService
    ) {}

    public function redirect(string $type, string $qr_id): RedirectResponse
    {
        $url = $this->qrRedirectService->getRedirectUrl($type, $qr_id);

        return redirect($url);
    }
}
