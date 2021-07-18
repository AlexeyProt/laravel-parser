<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    const CREATED_AT =  'published_at';

    const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime:Y-m-d',
    ];

}
