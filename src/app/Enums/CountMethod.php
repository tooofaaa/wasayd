<?php

namespace App\Enums;

enum CountMethod: string
{
    case Manual = 'manual';
    case Barcode = 'barcode';
    case Rfid = 'rfid';
}
