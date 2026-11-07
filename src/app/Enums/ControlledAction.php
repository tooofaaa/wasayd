<?php

namespace App\Enums;

enum ControlledAction: string
{
    case Dispense = 'dispense';
    case Reconcile = 'reconcile';
    case Waste = 'waste';
    case Receive = 'receive';
    case Return = 'return';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Dispense => 'Dispense',
            self::Reconcile => 'Reconcile',
            self::Waste => 'Waste',
            self::Receive => 'Receive',
            self::Return => 'Return',
            self::Adjustment => 'Adjustment',
        };
    }

    public function requiresWitness(): bool
    {
        return match ($this) {
            self::Dispense, self::Waste, self::Reconcile => true,
            default => false,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Dispense => 'blue',
            self::Reconcile => 'purple',
            self::Waste => 'red',
            self::Receive => 'green',
            self::Return => 'yellow',
            self::Adjustment => 'gray',
        };
    }
}
