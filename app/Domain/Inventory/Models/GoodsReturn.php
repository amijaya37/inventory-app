<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Inventory\Enums\TransactionStatus;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReturn extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'return_no', 'return_date', 'origin_type', 'origin_user_id', 'origin_location_id',
        'origin_pic_name', 'origin_pic_phone', 'warehouse_location_id', 'return_reason',
        'status', 'posted_at', 'created_by', 'updated_by', 'posted_by', 'remarks',
    ];

    protected function casts(): array
    {
        return ['status' => TransactionStatus::class, 'return_date' => 'date', 'posted_at' => 'datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReturnItem::class);
    }

    public function originUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'origin_user_id');
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'warehouse_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
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
        $this->forceFill(['status' => TransactionStatus::Posted, 'posted_at' => now(), 'posted_by' => $actorId, 'updated_by' => $actorId])->save();
    }
}
