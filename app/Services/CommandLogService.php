<?php

namespace App\Services;

use App\Models\CommandLog;
use App\Repositories\CommandLogRepository;
use App\Services\MakeshopApi\ProductQueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CommandLogService extends BaseService
{

    /** @var ProductQueryBuilder */
    private $commandLogRepository;

    /**
     * CommandLogService constructor.
     *
     * @param ProductQueryBuilder $productQueryBuilder
     * @param CommandLogRepository $CommandLogRepository
     * @param CommandLogRepository $commandLogRepository
     */
    public function __construct(CommandLogRepository $commandLogRepository)
    {
        $this->commandLogRepository = $commandLogRepository;
    }

    /**
     * Find command log by class name
     *
     * @param string $className
     * @return void
     */
    public function find($className)
    {
        return CommandLog::where('command_name', $className)->first();
    }
}
