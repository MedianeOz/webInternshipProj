<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends Model
{
    protected $fillable = [
        'name',
        'code',
        'logo_url'
        ];

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }
}