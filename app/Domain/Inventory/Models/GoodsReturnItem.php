<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Inventory\Enums\ReturnFinalAction;
use App\Domain\Master\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_return_id', 'item_id', 'qty', 'serial_no', 'condition_status', 'final_action', 'notes',
    ];

    protected function casts(): array
    {
        return ['qty' => 'integer', 'final_action' => ReturnFinalAction::class];
    }

    public function goodsReturn(): BelongsTo
    {
        return $this->belongsTo(GoodsReturn::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->goodsReturn();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GoodsReturnPhoto::class);
    }

    public function shouldReturnToStock(): bool
    {
        return $this->getRawOriginal('final_action') === ReturnFinalAction::ReturnToStock->value;
    }
}
