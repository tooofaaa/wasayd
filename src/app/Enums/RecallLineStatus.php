<?php

namespace App\Enums;

enum RecallLineStatus: string
{
    case Identified = 'identified';
    case Quarantined = 'quarantined';
    case Recovered = 'recovered';
    case Disposed = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::Identified => 'Identified',
            self::Quarantined => 'Quarantined',
            self::Recovered => 'Recovered',
            self::Disposed => 'Disposed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Identified => 'gray',
            self::Quarantined => 'yellow',
            self::Recovered => 'blue',
            self::Disposed => 'red',
        };
    }
}
