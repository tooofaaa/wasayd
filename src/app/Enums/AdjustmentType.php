<?php

namespace App\Enums;

enum AdjustmentType: string
{
    case CountVariance = 'count_variance';
    case Manual = 'manual';
    case Damage = 'damage';
    case Expiry = 'expiry';
}
