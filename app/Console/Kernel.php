<?php

namespace App\Console;

use App\Console\Commands\AdwUsersTableInsertion;
use App\Console\Commands\ClearRepairCount;
use App\Console\Commands\SendMaterialLimitNotification;
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
        SendMaterialLimitNotification::class,
        AdwUsersTableInsertion::class,
        ClearRepairCount::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('send:material-limit-notification')->cron('0 11 * * *');
        $schedule->command('insert:adw-users-table')->cron('0 23 * * *');
        $schedule->command('clear:repairCount')->cron('0 2 * * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
