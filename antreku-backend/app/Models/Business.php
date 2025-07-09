<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'address', 'phone_number',
        'logo_url', 'operating_hours', 'dp_amount'
    ];

    protected $casts = [
        'operating_hours' => 'array', // Otomatis konversi JSON ke Array & sebaliknya
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function queueSlots(): HasMany
    {
        return $this->hasMany(QueueSlot::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}

