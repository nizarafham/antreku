<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'phone_number', 'password'];
    protected $hidden = ['password'];

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'customer_id');
    }
}
