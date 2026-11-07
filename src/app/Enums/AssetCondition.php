<?php

namespace App\Enums;

enum AssetCondition: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Fair = 'fair';
    case Poor = 'poor';
    case NonFunctional = 'non_functional';

    public function label(): string
    {
        return match($this) {
            self::Excellent => 'Excellent',
            self::Good => 'Good',
            self::Fair => 'Fair',
            self::Poor => 'Poor',
            self::NonFunctional => 'Non-Functional',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Excellent => 'success',
            self::Good => 'primary',
            self::Fair => 'warning',
            self::Poor => 'destructive',
            self::NonFunctional => 'destructive',
        };
    }
}
