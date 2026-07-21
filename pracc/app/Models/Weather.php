<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weather extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'city',
        'temperature',
        'description',
        'humidity',
        'pressure',
        'recorded_at',
    ];
}
