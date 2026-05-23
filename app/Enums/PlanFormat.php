<?php

namespace App\Enums;

enum PlanFormat: string
{
    case ONLINE = 'online';
    case IN_OFFICE = 'in_office';
    case BOTH = 'both';

    public function label(): string
    {
        return __("plans.format_{$this->value}");
    }
}
