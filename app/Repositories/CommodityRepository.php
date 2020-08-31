<?php

namespace App\Repositories;

use App\Models\Commodity;
use App\Repositories\BaseRepository;

/**
 * Class CommodityRepository
 * @package App\Repositories
 */
class CommodityRepository extends BaseRepository
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
        return Commodity::class;
    }
}
