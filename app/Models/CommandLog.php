<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CommandLog
 * @package App\Models
 */
class CommandLog extends Model
{
    public $fillable = [];

    protected $casts = [
        'started_at'        => 'datetime',
        'ended_at'          => 'datetime',
        'last_succeed_time' => 'datetime',
    ];
}
