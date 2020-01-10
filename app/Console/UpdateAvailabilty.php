<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use  App\Events\drivers_status;

class UpdateAvailabilty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:updatSSe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Drivers Availabilty';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer  $drip
     * @return mixed
     */
    public function handle()
    {
         
        $newTime = strtotime('-2 minutes');
        $toBeOffline  =  date('Y-m-d H:i:s', $newTime);
        
        $drivers =  DB::table('drivers')->where('updated_at', '<=', $toBeOffline)->where('availability','on')->get();
       
     
        foreach($drivers as $driver){
            DB::table('drivers')->where('user_id', $driver->user_id)->update(['availability'=>'off']);
            event(new drivers_status($driver->user_id));

        }
    }
}