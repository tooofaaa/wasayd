<?php

namespace App\Enums;

enum RfidReaderStatus: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Maintenance = 'maintenance';
    case Decommissioned = 'decommissioned';

    public function color(): string
    {
        return match ($this) {
            self::Online => 'green',
            self::Offline => 'red',
            self::Maintenance => 'yellow',
            self::Decommissioned => 'gray',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::Offline => 'Offline',
            self::Maintenance => 'Maintenance',
            self::Decommissioned => 'Decommissioned',
        };
    }
}
