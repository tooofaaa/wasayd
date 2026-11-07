<?php

namespace App\Enums;

enum QuarantineStatus: string
{
    case Active = 'active';
    case UnderReview = 'under_review';
    case Released = 'released';
    case Disposed = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::UnderReview => 'Under Review',
            self::Released => 'Released',
            self::Disposed => 'Disposed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'yellow',
            self::UnderReview => 'blue',
            self::Released => 'green',
            self::Disposed => 'red',
        };
    }
}
