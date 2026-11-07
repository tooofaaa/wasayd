<?php

namespace App\Enums;

enum InspectionResult: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Conditional = 'conditional';

    public function label(): string
    {
        return match($this) {
            self::Pass => 'Pass',
            self::Fail => 'Fail',
            self::Conditional => 'Conditional',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pass => 'success',
            self::Fail => 'destructive',
            self::Conditional => 'warning',
        };
    }
}
