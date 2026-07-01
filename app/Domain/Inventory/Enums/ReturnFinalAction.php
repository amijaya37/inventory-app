<?php

namespace App\Domain\Inventory\Enums;

enum ReturnFinalAction: string
{
    case ReturnToStock = 'return_to_stock';
    case Repair = 'repair';
    case Scrap = 'scrap';
    case Dispose = 'dispose';
}
