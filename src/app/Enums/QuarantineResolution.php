<?php

namespace App\Enums;

enum QuarantineResolution: string
{
    case Released = 'released';
    case Disposed = 'disposed';
    case ReturnedToSupplier = 'returned_to_supplier';

    public function label(): string
    {
        return match ($this) {
            self::Released => 'Released',
            self::Disposed => 'Disposed',
            self::ReturnedToSupplier => 'Returned to Supplier',
        };
    }
}
