<?php

namespace App\Policies;

use App\Domain\Inventory\Models\TransactionDocument;
use App\Models\User;

class TransactionDocumentPolicy
{
    public function view(User $user, TransactionDocument $document): bool
    {
        return $user->can('documents.view');
    }

    public function download(User $user, TransactionDocument $document): bool
    {
        if (! $user->can('documents.download')) {
            return false;
        }

        return match ($document->module) {
            'goods_receipts' => $user->can('goods-receipts.documents.download') || $user->can('goods-in.view'),
            'goods_issues' => $user->can('goods-issues.documents.download') || $user->can('goods-out.view'),
            default => true,
        };
    }

    public function delete(User $user, TransactionDocument $document): bool
    {
        return $user->can('documents.delete');
    }
}
