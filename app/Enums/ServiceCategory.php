<?php

namespace App\Enums;

enum ServiceCategory: string
{
    case FAMILY = 'family';
    case REAL_ESTATE = 'real_estate';
    case FINANCIAL = 'financial';
    case CONTRACTS = 'contracts';

    public function label(): string
    {
        return __("services.categories.{$this->value}");
    }
}
