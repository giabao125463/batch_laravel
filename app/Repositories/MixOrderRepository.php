<?php

namespace App\Repositories;

use App\Models\MixOrder;
use App\Models\MixOrders;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

/**
 * Class MixOrderRepository
 * @package App\Repositories
 */
class MixOrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'ordernum',
        'mixnum',
        'sales_date',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return MixOrder::class;
    }
}
