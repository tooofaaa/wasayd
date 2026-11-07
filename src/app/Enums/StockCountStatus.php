<?php

namespace App\Enums;

enum StockCountStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Counting = 'counting';
    case Submitted = 'submitted';
    case Reviewing = 'reviewing';
    case Approved = 'approved';
    case Cancelled = 'cancelled';
}
