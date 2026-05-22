<?php

namespace App\Enums;

enum DocumentScanStatus: string
{
    case PENDING = 'pending';
    case CLEAN = 'clean';
    case INFECTED = 'infected';
}
