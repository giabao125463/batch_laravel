<?php

namespace App\Repositories;

use App\Models\CommandLog;
use App\Repositories\BaseRepository;

/**
 * Class CommandLogRepository
 * @package App\Repositories
 */

class CommandLogRepository extends BaseRepository
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
        return CommandLog::class;
    }
}
