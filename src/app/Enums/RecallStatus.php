<?php

namespace App\Enums;

enum RecallStatus: string
{
    case Initiated = 'initiated';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Initiated => 'Initiated',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Initiated => 'yellow',
            self::InProgress => 'blue',
            self::Completed => 'green',
            self::Cancelled => 'gray',
        };
    }
}
