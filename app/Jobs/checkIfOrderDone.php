<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Order;
use App\Jobs\updateFireBase;
use App\Driver;
use DB;
class checkIfOrderDone implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order =$this->order;

        if($this->order->status == 0){
            $EcludianDistanceQuery ="SELECT `driver_id`,cost,SQRT( POWER(`cost`- $order->expectedDeliveryCost,2) ) as DIstance FROM `order_drivers`  WHERE `order_id`='$order->id' AND `status` = '1' ORDER BY DIstance ASC , cost ASC LIMIT 10;";
             $costs  = DB::select($EcludianDistanceQuery);
             if(count($costs) > 0){
                $selectd_driver = $costs[0]->driver_id;
                $order->deliveryCost = $costs[0]->cost;
                $order->companyProfit = $costs[0]->cost *.25;
                $order->driver_id = $costs[0]->driver_id;
                $order->status = '1';
                $order->save();

                updateFireBase::dispatch($order)->onQueue('firebase');
                $this->SendNotification($Driver->deviceToken,$order,'orderaccepted');
            }
            else{

                $order->status = '-1';
                $order->save();
                updateFireBase::dispatch($order)->onQueue('firebase');
            }

        }

    }


     function SendNotification($deviceToken,$order,$message){


        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array (
                'registration_ids' => array (
                        $deviceToken
                ),
                'data' => array (
                        'NotIficationType'=>$message,
                        'Data'=>[
                            'OrderId'=>$order->id,
                        ]
                )
        );
        $fields = json_encode ( $fields );

        $headers = array (
                'Authorization: key=' . "AAAAM7mSsoE:APA91bFwY_7HlIj1-R72mGcOvpXAVRfUqYnAMwkpFTORJnoCkQzxyi-Rh8mRbiESDPg4xPurR5Z1hjXQW1SzqkksL68UQCx_3zVzXBaOX6LSNhTs_mtAQ7W4AgIkOkdwGd7dL8I4RObu",
                'Content-Type: application/json'
        );

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

        $result = curl_exec ( $ch );
        curl_close ( $ch );
       }
}
