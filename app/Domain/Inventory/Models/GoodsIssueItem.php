<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Master\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_issue_id',
        'item_id',
        'qty',
        'serial_no',
        'condition_status',
        'notes',
    ];

    protected function casts(): array
    {
        return ['qty' => 'integer'];
    }

    public function goodsIssue(): BelongsTo
    {
        return $this->belongsTo(GoodsIssue::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->goodsIssue();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
