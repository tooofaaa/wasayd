<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case InUse = 'in_use';
    case Maintenance = 'maintenance';
    case Repair = 'repair';
    case Calibration = 'calibration';
    case Quarantine = 'quarantine';
    case Retired = 'retired';
    case Decommissioned = 'decommissioned';
    case Lost = 'lost';
    case Disposed = 'disposed';

    public function label(): string
    {
        return match($this) {
            self::Available => 'Available',
            self::InUse => 'In Use',
            self::Maintenance => 'Maintenance',
            self::Repair => 'Repair',
            self::Calibration => 'Calibration',
            self::Quarantine => 'Quarantine',
            self::Retired => 'Retired',
            self::Decommissioned => 'Decommissioned',
            self::Lost => 'Lost',
            self::Disposed => 'Disposed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Available => 'success',
            self::InUse => 'primary',
            self::Maintenance, self::Repair, self::Calibration => 'warning',
            self::Quarantine => 'destructive',
            self::Retired, self::Decommissioned, self::Disposed => 'secondary',
            self::Lost => 'destructive',
        };
    }
}
