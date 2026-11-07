<?php

namespace App\Enums;

enum WarehouseType: string
{
    case Standard = 'standard';
    case ColdStorage = 'cold_storage';
    case Quarantine = 'quarantine';
    case Hazmat = 'hazmat';
    case Controlled = 'controlled';
}
