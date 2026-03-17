<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\MembershipCardService;

class PublicMembershipCardController extends Controller
{
    public function show(Customer $customer, MembershipCardService $membershipCardService)
    {
        $binary = $membershipCardService->generatePdfBinary($customer);
        $fileName = sprintf('membership-card-%s.pdf', $customer->id);

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', $fileName),
        ]);
    }
}
