<?php

namespace App\Policies;

use App\Domain\Inventory\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('goods-in.view');
    }

    public function view(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->can('goods-in.view');
    }

    public function create(User $user): bool
    {
        return $user->can('goods-in.create');
    }

    public function update(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->can('goods-in.update');
    }

    public function delete(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->can('goods-in.delete');
    }

    public function post(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->can('goods-in.post');
    }

    public function print(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->can('goods-in.print');
    }
}
