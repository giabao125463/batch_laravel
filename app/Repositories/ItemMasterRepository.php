<?php

namespace App\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use App\ItemMaster;
use App\Repositories\BaseRepository;

class ItemMasterRepository extends BaseRepository 
{

    /**
     * @var array
     */
    protected $fieldSearchable = [
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
        return ItemMaster::class;
    }

    /**
     * Return search result by column
     *
     * @return array
     */
    public function findByCol($col, $val){
        $result = $this->model->where($col, $val)->first();

        return $result;
    }

    /**
     * @param $jancodes
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function findByJancodes($jancodes)
    {
        $query = $this->model->newQuery();
        return $query->whereIn('scancode_new', $jancodes)->get();
    }
}