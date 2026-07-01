<?php

namespace App\Domain\Inventory\Services;

use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function next(string $type): string
    {
        $date = now()->format('Ymd');
        $count = DB::table($this->tableFor($type))->whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('%s-%s-%05d', $this->prefixFor($type), $date, $count);
    }

    private function tableFor(string $type): string
    {
        return match ($type) {
            'goods_receipt' => 'goods_receipts',
            'goods_issue' => 'goods_issues',
            'goods_return' => 'goods_returns',
            'stock_mutation' => 'stock_mutations',
            default => throw new \InvalidArgumentException("Unknown document type: {$type}"),
        };
    }

    private function prefixFor(string $type): string
    {
        return match ($type) {
            'goods_receipt' => 'BM',
            'goods_issue' => 'BK',
            'goods_return' => 'BT',
            'stock_mutation' => 'MT',
            default => throw new \InvalidArgumentException("Unknown document type: {$type}"),
        };
    }
}
