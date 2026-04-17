<?php

namespace App\Services;

use App\Enums\PaymentTypeEnum;

class PaymentTypeService
{
    public function getAll(): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            PaymentTypeEnum::cases()
        );
    }
}
