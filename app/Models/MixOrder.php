<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MixOrder extends Model
{

    protected $casts = [
        'ordernum'     => 'string',
        'mixnum'       => 'string',
        'sales_date'   => 'date',
    ];

    public $fillable = [
        'ordernum',
        'mixnum',
        'sales_date',
    ];
}
