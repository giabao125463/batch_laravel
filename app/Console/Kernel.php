<?php

namespace App\Console;

use App\Console\Commands\ProductEC2DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\Demo::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 商品マスタ連携
        // $schedule->command('item:pos2db')
        //          ->dailyAt('2:00');

        // 受注取得
        $schedule->command('order:ec2db')
                 ->everyFiveMinutes();

        // POS売上連携
        // $schedule->command('order:db2pos')
        //          ->unlessBetween('1:40', '7:00')
        //          ->everyTenMinutes();

        // 在庫取得
        $schedule->command('product:ec2db')
                 ->dailyAt('1:40');

        // 在庫連携
        // $schedule->command('stock:posdb2ec')
        //          ->dailyAt('2:30');

        // 出荷情報更新
        $schedule->command('shipping:ec2db')
                 ->dailyAt('8:00');

        // MIX売上連携
        $schedule->command('order:db2mix')
                 ->dailyAt('22:00');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
