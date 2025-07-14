<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'midtrans_order_id',
        'amount',
        'payment_method',
        'status',
        'payment_payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_payload' => 'array',
    ];

    /**
     * Mendefinisikan relasi ke Queue.
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }
}
