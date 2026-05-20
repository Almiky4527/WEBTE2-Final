<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AnimType;

class AnimUsageStat extends Model
{
    protected $fillable = [
        'token',
        'anim',
        'country',
        'city',
    ];

    protected $casts = [
        'anim' => AnimType::class,
    ];
}
