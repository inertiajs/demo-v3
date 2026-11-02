<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveReaction extends Model
{
    protected $fillable = [
        'visitor',
        'reaction',
    ];
}
