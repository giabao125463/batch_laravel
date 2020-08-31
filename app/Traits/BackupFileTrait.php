<?php

namespace App\Traits;

use App\Repositories\CommandLogRepository;
use App\Models\CommandLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Trait BackupFileTrait
 */
trait BackupFileTrait
{
    /**
     * @var string $dirPath
     */
    private $dirPath;

    /**
     * @var Storage $storage
     */
    private $storage;

    /**
     * Get $storage
     *
     * @return  Storage
     */
    public function getBackupStorage()
    {
        if (empty($this->storage)) {

            return Storage::disk('local-backup');
        }
        return $this->storage;
    }

    /**
     * Set $storage
     *
     * @param  Storage  $storage  $storage
     *
     * @return  self
     */
    public function setBackupStorage(Storage $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Get $dirPath
     *
     * @return  string
     */
    public function getBackupDirPath()
    {
        if (!$this->getBackupStorage()->exists($this->dirPath)) {
            try {
                $this->getBackupStorage()->makeDirectory($this->dirPath, 0755, true);
            } catch (\Exception $ex) {
                Log::error('Can\'t create backup folder: ' . $this->getBackupStorage()->path($this->dirPath));
            }
        }

        return $this->dirPath;
    }

    /**
     * Set $dirPath
     *
     * @param  string  $dirPath  $dirPath
     *
     * @return  self
     */
    public function setBackupDirPath(string $dirPath)
    {
        $this->dirPath = $dirPath;

        return $this;
    }

    /**
     * Get file path of backup file
     *
     * @param string $backupName
     * @return string
     */
    private function getBackupFilePath($backupName)
    {
        $filePath = $this->getBackupDirPath() . '/' . $backupName;
        return $this->getBackupStorage()->path($filePath);
    }

    /**
     * Backup file to backup folder
     *
     * @param string $filePath Absolute path of original file
     * @param string $backupName File name
     * @return void
     */
    public function backup($filePath, $backupName)
    {
        if (!File::exists($filePath)) {

            return false;
        }
        try {
            $backupPath = $this->getBackupFilePath($backupName);
            Log::info('[BACKUP] ' . $filePath . ' -> ' . $backupPath);

            return File::copy($filePath, $backupPath);
        } catch(\Exception $ex) {
            Log::error('[BACKUP FAIL] Can\'t copy file');

            return false;
        }
    }
}
