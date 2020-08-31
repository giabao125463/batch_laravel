<?php

namespace App\Repositories;

use App\Models\Delivery;
use App\Repositories\BaseRepository;

/**
 * Class DeliveryRepository
 * @package App\Repositories
 */

class DeliveryRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        // TODO: Add searchable field
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
        return Delivery::class;
    }
}
