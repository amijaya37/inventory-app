<?php

namespace App\Policies;

use App\Domain\Inventory\Models\StockMutation;
use App\Models\User;

class StockMutationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('mutations.view');
    }

    public function view(User $user, StockMutation $stockMutation): bool
    {
        return $user->can('mutations.view');
    }

    public function create(User $user): bool
    {
        return $user->can('mutations.create');
    }

    public function update(User $user, StockMutation $stockMutation): bool
    {
        return $user->can('mutations.update');
    }

    public function delete(User $user, StockMutation $stockMutation): bool
    {
        return $user->can('mutations.delete');
    }

    public function post(User $user, StockMutation $stockMutation): bool
    {
        return $user->can('mutations.post');
    }

    public function print(User $user, StockMutation $stockMutation): bool
    {
        return $user->can('mutations.print');
    }
}
