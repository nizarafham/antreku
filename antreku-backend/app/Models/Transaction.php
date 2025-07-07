<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id', 'midtrans_order_id', 'amount',
        'payment_method', 'status', 'paid_at'
    ];

    protected $casts = ['paid_at' => 'datetime'];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }
}
