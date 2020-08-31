<?php
namespace App\Traits;

use App\Repositories\CommandLogRepository;
use App\Models\CommandLog;
use Illuminate\Support\Facades\Log;

/**
 * Trait CommandTrait
 */
trait CommandLogTrait
{
    protected $logName;

    /**
     * Able to run batch
     *
     * @return void
     */
    public function ableToExecute()
    {
        $log = $this->getCommandLog();
        if ($log) {
            return $log->last_succeed_time >= $log->started_at || $log->ended_at >= $log->started_at;
        }
        return true;
    }

    protected function getCommandLog()
    {
        $commandName = $this->getCommandName();
        $log         = CommandLog::where('command_name', $commandName)->first();
        if (!$log) {
            $log               = new CommandLog();
            $log->command_name = $commandName;
            $log->save();
        }

        return $log;
    }

    protected function getCommandName()
    {
        return get_class($this);
    }

    /**
     * Write start log
     *
     * @param mixed|null $startedAt
     * @return void
     */
    public function startLog($startedAt = null)
    {
        Log::info('Start command: ' . $this->getCommandName());
        $log             = $this->getCommandLog();
        $log->started_at = $startedAt ?? new \DateTime();
        $log->save();
    }

    /**
     * Write end log
     *
     * @return void
     */
    public function endLog()
    {
        Log::info('End command: ' . $this->getCommandName());
        $log                    = $this->getCommandLog();
        $log->ended_at          = new \DateTime();
        $log->save();
    }


    /**
     * logSuccess
     *
     * @param mixed|null $succeedAt
     * @return void
     */
    public function logSuccess($succeedAt = null)
    {
        Log::info('Success command: ' . $this->getCommandName());
        $log                    = $this->getCommandLog();
        $log->last_succeed_time = $succeedAt ?? new \DateTime();
        $log->save();
    }

    /**
     * getLastSucceedTime
     *
     * @return void
     */
    public function getLastSucceedTime()
    {
        $log = $this->getCommandLog();
        if ($log) {
            return $log->last_succeed_time;
        }
        return null;
    }
}
