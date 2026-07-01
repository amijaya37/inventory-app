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

class StockMutation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'mutation_no', 'mutation_date', 'source_location_id', 'destination_location_id',
        'requested_by', 'posted_by', 'posted_at', 'status', 'remarks', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['status' => TransactionStatus::class, 'mutation_date' => 'date', 'posted_at' => 'datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockMutationItem::class);
    }

    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
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
