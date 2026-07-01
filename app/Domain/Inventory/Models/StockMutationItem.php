<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Master\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutationItem extends Model
{
    use HasFactory;

    protected $fillable = ['stock_mutation_id', 'item_id', 'qty', 'serial_no', 'condition_status', 'notes'];

    protected function casts(): array
    {
        return ['qty' => 'integer'];
    }

    public function mutation(): BelongsTo
    {
        return $this->belongsTo(StockMutation::class, 'stock_mutation_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->mutation();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
