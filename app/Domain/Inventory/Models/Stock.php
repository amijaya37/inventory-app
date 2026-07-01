<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'location_id',
        'qty_on_hand',
        'qty_reserved',
        'last_movement_at',
    ];

    protected function casts(): array
    {
        return [
            'qty_on_hand' => 'integer',
            'qty_reserved' => 'integer',
            'qty_available' => 'integer',
            'last_movement_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(StockCard::class);
    }
}
