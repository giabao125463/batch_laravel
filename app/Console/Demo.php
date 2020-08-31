<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Traits\CommandLogTrait;
use Illuminate\Support\Facades\Storage;

class Demo extends Command
{
    use CommandLogTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command demo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->logName = 'demo_file_log_name';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->startLog();
        // Get file FTP
        $file = Storage::disk('ftp-1')->get('file.jpg');
        $fileContents = 'DEMO CONTENT';
        // Put file FTP
        Storage::disk('ftp-1')->put('avatars/1', $fileContents);
        $this->endLog();
    }
}
