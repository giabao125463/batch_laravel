<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Link
 * @package App\Models
 */
class Link extends Model
{
    public $table = 'links';
    public $connection = 'mysql_auc_site';

    public $fillable = [
        'team26_id',
        'makeshop_id',
        'last_login_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'team26_id'         => 'string',
        'makeshop_id'       => 'string',
        'last_login_at'     => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [];
}
