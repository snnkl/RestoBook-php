<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Table extends Model
{
    use HasFactory;

    protected $fillable = ['restaurant_id', 'number', 'capacity', 'price_per_seat', 'is_active'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function bookings(): HasMany
    {
            return $this->hasMany(Booking::class);
    }
}
