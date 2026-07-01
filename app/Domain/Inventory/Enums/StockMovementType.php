<?php

namespace App\Domain\Inventory\Enums;

enum StockMovementType: string
{
    case GoodsReceipt = 'goods_receipt';
    case GoodsIssue = 'goods_issue';
    case GoodsReturn = 'return_in';
    case MutationOut = 'mutation_out';
    case MutationIn = 'mutation_in';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
}
