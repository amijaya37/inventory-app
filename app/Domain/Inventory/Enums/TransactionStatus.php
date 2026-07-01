<?php

namespace App\Domain\Inventory\Enums;

enum TransactionStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Cancelled = 'cancelled';
    case Reversed = 'reversed';
}
