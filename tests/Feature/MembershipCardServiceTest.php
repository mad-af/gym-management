<?php

use App\Models\Customer;
use App\Services\MembershipCardService;
use App\Services\WhatsappConfigService;

afterEach(function (): void {
    \Mockery::close();
});

test('membership card service generates pdf binary', function () {
    $whatsappConfigService = \Mockery::mock(WhatsappConfigService::class);
    $service = new MembershipCardService($whatsappConfigService);

    $customer = new Customer([
        'name' => 'Budi Santoso',
        'code' => 'GYM-0001',
        'qr_code' => null,
        'phone' => '081234567890',
    ]);

    $binary = $service->generatePdfBinary($customer);

    expect($binary)->not->toBe('');
    expect(substr($binary, 0, 4))->toBe('%PDF');
});
