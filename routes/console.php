<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

use  App\Events\drivers_status;

Artisan::command('DriverOff', function () {
    $newTime = strtotime('-120 minutes');
    $toBeOffline  =  date('Y-m-d H:i:s', $newTime);

    $drivers =  DB::table('drivers')->where('updated_at', '<=', $toBeOffline)->where('availability', 'on')->get();


    foreach ($drivers as $driver) {
        DB::table('drivers')->where('user_id', $driver->user_id)->update(['availability'=>'off']);
        event(new drivers_status($driver->user_id));
    }
});

Artisan::command('mini_dashboards_days_count', function () {
    DB::table('mini_dashboards')->where('days_left', '>', 0)->decrement('days_left');
});
