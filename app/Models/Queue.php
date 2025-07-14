<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'business_id',
        'service_id',
        'queue_number',
        'booking_date',
        'booking_time',
        'estimated_service_time',
        'status',
    ];

    /**
     * Mendefinisikan relasi ke User (sebagai pelanggan).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Mendefinisikan relasi ke Business.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Mendefinisikan relasi ke Service.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Mendefinisikan relasi ke Transaction.
     * Setiap antrean memiliki satu transaksi.
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
