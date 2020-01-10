<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
use  App\Events\drivers_status;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $newTime = strtotime('-5 minutes');
        $toBeDelted  =  date('Y-m-d H:i:s', $newTime);
        
       
        
        DB::table('jobs')->where('created_at', '<=', $toBeDelted)->delete();
       if (stripos((string) shell_exec('ps xf | grep \'[q]ueue:work\''), 'artisan queue:work') === false) {
            $schedule->command('queue:work  --tries=30 --queue=firebase,drivers_status')->everyMinute()->appendOutputTo(storage_path() . '/logs/scheduler.log');
        }
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
