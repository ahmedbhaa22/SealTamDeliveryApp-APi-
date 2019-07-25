<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Kreait\Firebase;
use Response;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;

class updateFireBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

     public $resturant_id;
     public $order;
    public function __construct($resturant_id,$order)
    {
        $this->resturant_id =$resturant_id;
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/sealteamdeliveryapp-firebase-adminsdk-yra65-b8ba7856bd.json');
        $firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://sealteamdeliveryapp.firebaseio.com')->create();
        $database = $firebase->getDatabase();

        $newOrder = $database
            ->getReference('Orders/'.$this->resturant_id.'/'.$this->order->id)
            ->set($this->order);

    }
    public function retryUntil()
        {
            return now()->addSeconds(8);
        }
}
