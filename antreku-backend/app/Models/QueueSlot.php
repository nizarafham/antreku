<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueSlot extends Model
{
    use HasFactory;
    protected $fillable = ['business_id', 'slot_datetime', 'is_available'];
    protected $casts = ['slot_datetime' => 'datetime'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
