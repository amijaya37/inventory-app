<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'item_id',
        'location_id',
        'trx_date',
        'direction',
        'movement_type',
        'reference_type',
        'reference_id',
        'reference_no',
        'qty',
        'qty_before',
        'qty_after',
        'unit_cost',
        'remarks',
        'posted_by',
    ];

    protected function casts(): array
    {
        return [
            'trx_date' => 'datetime',
            'direction' => StockDirection::class,
            'movement_type' => StockMovementType::class,
            'qty' => 'integer',
            'qty_before' => 'integer',
            'qty_after' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
