<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'book_id',
        'user_id',
        'rating',
        'comment',
    ];
}
