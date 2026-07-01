<?php

namespace App\Policies;

use App\Domain\Inventory\Models\GoodsReturn;
use App\Models\User;

class GoodsReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('returns.view');
    }

    public function view(User $user, GoodsReturn $goodsReturn): bool
    {
        return $user->can('returns.view');
    }

    public function create(User $user): bool
    {
        return $user->can('returns.create');
    }

    public function update(User $user, GoodsReturn $goodsReturn): bool
    {
        return $user->can('returns.update');
    }

    public function delete(User $user, GoodsReturn $goodsReturn): bool
    {
        return $user->can('returns.delete');
    }

    public function verify(User $user, GoodsReturn $goodsReturn): bool
    {
        return $user->can('returns.verify');
    }

    public function post(User $user, GoodsReturn $goodsReturn): bool
    {
        return $user->can('returns.post');
    }

    public function print(User $user, GoodsReturn $goodsReturn): bool
    {
        return $user->can('returns.print');
    }
}
