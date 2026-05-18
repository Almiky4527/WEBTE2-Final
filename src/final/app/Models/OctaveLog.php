<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OctaveLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'success',
        'error',
    ];
}
