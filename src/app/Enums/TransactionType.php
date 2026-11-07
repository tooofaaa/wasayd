<?php

namespace App\Enums;

enum TransactionType: string
{
    case Receiving = 'receiving';
    case Putaway = 'putaway';
    case Issue = 'issue';
    case Transfer = 'transfer';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case Count = 'count';
    case Disposal = 'disposal';
    case Reservation = 'reservation';
    case Release = 'release';
    case Quarantine = 'quarantine';
    case Unquarantine = 'unquarantine';

    public function label(): string
    {
        return match ($this) {
            self::Receiving => 'Receiving',
            self::Putaway => 'Putaway',
            self::Issue => 'Issue',
            self::Transfer => 'Transfer',
            self::Return => 'Return',
            self::Adjustment => 'Adjustment',
            self::Count => 'Stock Count',
            self::Disposal => 'Disposal',
            self::Reservation => 'Reservation',
            self::Release => 'Release',
            self::Quarantine => 'Quarantine',
            self::Unquarantine => 'Unquarantine',
        };
    }

    public function affectsStockPositively(): bool
    {
        return match ($this) {
            self::Receiving, self::Return, self::Unquarantine, self::Release => true,
            default => false,
        };
    }

    public function affectsStockNegatively(): bool
    {
        return match ($this) {
            self::Issue, self::Disposal, self::Quarantine, self::Reservation => true,
            default => false,
        };
    }
}
