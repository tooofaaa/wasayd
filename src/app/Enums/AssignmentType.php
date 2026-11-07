<?php

namespace App\Enums;

enum AssignmentType: string
{
    case User = 'user';
    case Department = 'department';
    case Location = 'location';

    public function label(): string
    {
        return match($this) {
            self::User => 'User',
            self::Department => 'Department',
            self::Location => 'Location',
        };
    }
}
