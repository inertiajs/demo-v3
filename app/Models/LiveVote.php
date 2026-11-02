<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveVote extends Model
{
    protected $fillable = [
        'visitor',
        'framework',
    ];
}
