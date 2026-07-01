<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Inventory\Enums\TransactionStatus;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsIssue extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'issue_no', 'issue_date', 'source_location_id', 'recipient_type', 'recipient_user_id',
        'recipient_name', 'recipient_department', 'recipient_phone', 'target_location_id',
        'pic_user_id', 'requested_by', 'posted_by', 'posted_at', 'document_no',
        'handover_document_path', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'issue_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsIssueItem::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(TransactionDocument::class, 'documentable');
    }

    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    public function targetLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'target_location_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
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

    public function markAsPosted(int $actorId, string $documentNo): void
    {
        $this->forceFill([
            'status' => TransactionStatus::Posted,
            'posted_at' => now(),
            'posted_by' => $actorId,
            'document_no' => $documentNo,
        ])->save();
    }
}
