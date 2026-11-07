<?php

namespace App\Enums;

enum EpcStatus: string
{
    case Unassigned = 'unassigned';
    case Active = 'active';
    case InUse = 'in_use';
    case Decommissioned = 'decommissioned';
    case Lost = 'lost';
    case Damaged = 'damaged';
    case Returned = 'returned';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Unassigned => in_array($target, [self::Active]),
            self::Active => in_array($target, [self::InUse, self::Decommissioned, self::Lost, self::Damaged]),
            self::InUse => in_array($target, [self::Active, self::Decommissioned, self::Lost, self::Damaged, self::Returned]),
            self::Returned => in_array($target, [self::Active, self::Decommissioned]),
            self::Decommissioned, self::Lost, self::Damaged => false,
        };
    }
}
