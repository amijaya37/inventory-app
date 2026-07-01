<?php

namespace App\Domain\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReturnPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['goods_return_item_id', 'file_name', 'file_path', 'mime_type', 'file_size', 'uploaded_by'];

    public function returnItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReturnItem::class, 'goods_return_item_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
