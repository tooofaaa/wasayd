<?php

namespace App\Enums;

enum RfidEventType: string
{
    case Read = 'read';
    case Movement = 'movement';
    case Exception = 'exception';

    public function label(): string
    {
        return match ($this) {
            self::Read => 'Read',
            self::Movement => 'Movement',
            self::Exception => 'Exception',
        };
    }
}
