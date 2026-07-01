<?php

namespace App\Policies;

use App\Domain\Inventory\Models\GoodsIssue;
use App\Models\User;

class GoodsIssuePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('goods-out.view');
    }

    public function view(User $user, GoodsIssue $goodsIssue): bool
    {
        return $user->can('goods-out.view');
    }

    public function create(User $user): bool
    {
        return $user->can('goods-out.create');
    }

    public function update(User $user, GoodsIssue $goodsIssue): bool
    {
        return $user->can('goods-out.update');
    }

    public function delete(User $user, GoodsIssue $goodsIssue): bool
    {
        return $user->can('goods-out.delete');
    }

    public function post(User $user, GoodsIssue $goodsIssue): bool
    {
        return $user->can('goods-out.post');
    }

    public function print(User $user, GoodsIssue $goodsIssue): bool
    {
        return $user->can('goods-out.print');
    }
}
