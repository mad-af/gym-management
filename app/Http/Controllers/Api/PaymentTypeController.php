<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Services\PaymentTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PaymentTypeController extends Controller
{
    public function __construct(protected PaymentTypeService $service) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success('Payment types retrieved successfully.', $this->service->getAll());
    }
}
