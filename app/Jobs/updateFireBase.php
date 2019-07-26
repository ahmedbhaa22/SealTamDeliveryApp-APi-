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
use DB;
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

    public function __construct( $order)
    {
        $this->resturant_id =$order->resturant_id;
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
        if($this->order->driver_id){
            $Driver = DB::table('users')
            ->join('drivers','users.id', '=', 'drivers.user_id')
            ->where('users.UserType','driver')->where('drivers.user_id',$this->order->driver_id)
            ->first();
            $this->order= $this->order->toArray();
             $this->order['DriverName'] =$Driver->name;
             $this->order['DriverPhone'] =$Driver->telephone;
             $this->order['Driverlat'] =$Driver->lat;
             $this->order['Driverlng'] =$Driver->lng;
             $newOrder = $database
             ->getReference('Orders/'.$this->order['resturant_id'].'/'.$this->order['id'])
             ->set($this->order);
        }
        else{
            $newOrder = $database
            ->getReference('Orders/'.$this->resturant_id.'/'.$this->order->id)
            ->set($this->order);
        }


    }
    public function retryUntil()
        {
            return now()->addSeconds(3);
        }
}
