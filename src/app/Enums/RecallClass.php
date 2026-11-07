<?php

namespace App\Enums;

enum RecallClass: string
{
    case ClassI = 'class_i';
    case ClassII = 'class_ii';
    case ClassIII = 'class_iii';

    public function label(): string
    {
        return match ($this) {
            self::ClassI => 'Class I',
            self::ClassII => 'Class II',
            self::ClassIII => 'Class III',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ClassI => 'Dangerous or defective products that could cause serious health problems or death',
            self::ClassII => 'Products that might cause temporary health problems or pose slight threat',
            self::ClassIII => 'Products unlikely to cause adverse health reactions but violate regulations',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ClassI => 'red',
            self::ClassII => 'orange',
            self::ClassIII => 'yellow',
        };
    }
}
