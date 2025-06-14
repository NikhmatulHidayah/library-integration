<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'reservation_date',
        'status',
        'expire_date',
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'expire_date' => 'datetime',
    ];
}
