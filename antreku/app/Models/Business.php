<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'description',
        'status',
        'open_time',
        'close_time',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model User.
     * Setiap bisnis dimiliki oleh satu user.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model Service.
     * Setiap bisnis bisa memiliki banyak layanan.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model Queue.
     * Setiap bisnis bisa memiliki banyak antrean.
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
