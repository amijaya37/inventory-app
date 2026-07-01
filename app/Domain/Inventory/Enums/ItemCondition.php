<?php

namespace App\Domain\Inventory\Enums;

enum ItemCondition: string
{
    case New = 'new';
    case Good = 'good';
    case Used = 'used';
    case NeedRepair = 'need_repair';
    case Broken = 'broken';
    case Disposal = 'disposal';
}
