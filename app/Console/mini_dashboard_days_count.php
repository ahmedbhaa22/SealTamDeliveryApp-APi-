<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use  App\Events\drivers_status;

class mini_dashboard_days_count extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mini_dashboard:updatedays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        DB::table('mini_dashboards')->where('days_left', '>', 0)->decrement('days_left');
    }
}
