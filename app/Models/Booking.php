<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'flight_id',
        'booking_reference',
        'seat_count',
        'total_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'seat_count'  => 'integer',
        ];
    }

    // A booking belongs to one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // A booking belongs to one flight
    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    // A booking can have many passengers
    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }
}