<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemMaster
 * @package App\Models
 */
class ItemMaster extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_master';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $guarded = [];
}