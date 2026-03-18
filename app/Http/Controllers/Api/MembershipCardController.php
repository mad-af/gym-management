<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PrintMembershipCardRequest;
use App\Http\Requests\SendMembershipCardWhatsappRequest;
use App\Models\Customer;
use App\Services\MembershipCardService;

class MembershipCardController extends Controller
{
    public function __construct(private readonly MembershipCardService $membershipCardService)
    {
        $this->middleware('permission:'
            .Permission::VIEW_MEMBERSHIP_TRANSACTIONS->value
            .'|'.Permission::CREATE_MEMBERSHIP_TRANSACTIONS->value
        )->only(['print']);
        $this->middleware('permission:'.Permission::CREATE_MEMBERSHIP_TRANSACTIONS->value)->only(['sendWhatsapp']);
    }

    public function print(PrintMembershipCardRequest $request)
    {
        $customer = Customer::query()->findOrFail($request->validated('customer_id'));
        $binary = $this->membershipCardService->generatePdfBinary($customer);
        $fileName = sprintf('membership-card-%s.pdf', $customer->id);

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', $fileName),
        ]);
    }

    public function sendWhatsapp(SendMembershipCardWhatsappRequest $request)
    {
        $validated = $request->validated();
        $customer = Customer::query()->findOrFail($validated['customer_id']);
        $result = $this->membershipCardService->sendViaWhatsapp($customer, $validated['target'] ?? null);

        return ApiResponse::success('Membership card berhasil dikirim ke WhatsApp.', $result);
    }
}
