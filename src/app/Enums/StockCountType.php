<?php

namespace App\Enums;

enum StockCountType: string
{
    case Full = 'full';
    case Cycle = 'cycle';
    case Spot = 'spot';
    case Rfid = 'rfid';
}
