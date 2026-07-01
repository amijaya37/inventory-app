<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Inventory\Enums\TransactionStatus;
use App\Domain\Master\Models\Location;
use App\Domain\Master\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'purchase_date' => 'date',
            'receipt_date' => 'date',
            'posted_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(TransactionDocument::class, 'documentable');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'warehouse_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function isDraft(): bool
    {
        return $this->getRawOriginal('status') === TransactionStatus::Draft->value;
    }

    public function isPosted(): bool
    {
        return $this->getRawOriginal('status') === TransactionStatus::Posted->value;
    }

    public function markAsPosted(int $actorId): void
    {
        $this->forceFill([
            'status' => TransactionStatus::Posted,
            'posted_at' => now(),
            'posted_by' => $actorId,
        ])->save();
    }
}
