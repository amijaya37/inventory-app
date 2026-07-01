<?php

namespace App\Policies;

use App\Domain\Inventory\Models\Stock;
use App\Models\User;

class StockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('stock.view');
    }

    public function view(User $user, Stock $stock): bool
    {
        return $user->can('stock.view');
    }

    public function viewCard(User $user, Stock $stock): bool
    {
        return $user->can('stock.card');
    }

    public function export(User $user): bool
    {
        return $user->can('stock.export');
    }

    public function recalculate(User $user): bool
    {
        return $user->can('stock.recalculate');
    }
}
