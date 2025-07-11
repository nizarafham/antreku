<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'price',
        'duration_minutes',
        'dp_percentage',
        'is_available',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Business.
     * Setiap layanan dimiliki oleh satu bisnis.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
