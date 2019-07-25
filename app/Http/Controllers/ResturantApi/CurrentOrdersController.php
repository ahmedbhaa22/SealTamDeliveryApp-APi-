<?php

namespace App\Http\Controllers\ResturantApi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\ViewModel\ResultVM;
use App\Order as Order_Table;
use App\Resturant as Resturant_Table;
use App\Order_driver as Order_driver_Table;
use Validator;
use Kreait\Firebase;
use Response;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use DB;
use App\Jobs\checkIfOrderDone;
use App\Jobs\updateFireBase;


class CurrentOrdersController extends Controller
{
    private $_result;

    public function __construct()
    {
       $this->_result=new ResultVM();
    }

    public function CreateNewOrder(Request $request){

        $validation=Validator::make($request->all(),
        [
            'totalCost'=>'required|numeric|min:0',
            'resturant_id'=>'required|exists:users,id|exists:resturants,user_id',
            'orderCost'=>'required|numeric',
            'customerPhone'=>'required',
            'customerName'=>'required',
            'OrderNumber'=>'required',
            'orderDest'=>'required',
            'expectedDeliveryCost'=>'required|numeric',
        ]);
        if($validation->fails())
        {
           $this->_result->IsSuccess = false;
           $this->_result->FaildReason =  $validation->errors()->first();
           return Response::json($this->_result,200);
        }
        //Validate Order On The Data Base
        $resturant =$resturant = DB::table('users')
        ->join('resturants','users.id', '=', 'resturants.user_id')
        ->where('users.UserType','resturant')->where('resturants.user_id',$request->resturant_id)
        ->first();
        //Search For Nearest Drivers,
        $drivers = $this->SelectNearestDriver($resturant->lat,$resturant->lng);
        //Add Order And OrderDetails TO DataBase
        if(count($drivers) > 0){
            DB::beginTransaction();

            try {

                $order = $this->AddNewOrder($request);
                foreach($drivers as $driver){
                    $this->SendNotification($driver->deviceToken,$order,$resturant);
                    $this->AddDriverOrder($driver->user_id,$order->id);
                }

            }
            catch(Exception $e){
                DB::rollback();
                throw $e;
            }
            DB::commit();
            $this->dispatch((new updateFireBase($request->resturant_id,$order))->onQueue('firebase'));


        }
        else{
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason = 'No Driver AvailAble To Accept This Request';
            return Response::json($this->_result,200);
        }

        $id = $this->dispatch((new checkIfOrderDone($order))->delay(now()->addSeconds(90)));
        $order->JobId = $id;
        $order->save();


        $this->_result->Data = $id;
        $this->_result->IsSuccess = true;
        return Response::json($this->_result,200);
        //Push New OrderObject TO FireBase

    }

   public function AddNewOrder($order){
        return Order_Table::create([
            'status' => '0',
            'resturant_id'=>$order->resturant_id,
            'orderCost'=>$order->orderCost,
            'deliveryCost'=>null,
            'customerPhone'=>$order->customerPhone,
            'customerName'=>$order->customerName,
            'OrderNumber'=>$order->OrderNumber,
            'orderDest'=>$order->orderDest,
            'expectedDeliveryCost'=>$order->expectedDeliveryCost,
            ]);
   }



   public function SelectNearestDriver($lat,$lng){

    $EcludianDistanceQuery ="SELECT `user_id`,`deviceToken`,SQRT( POWER(`lat`-$lat,2) + POWER(`lng`-$lng, 2) ) as DIstance FROM `drivers`  WHERE `availability`='on' AND `canReceiveOrder` = '1' AND  `deviceToken` IS NOT NULL ORDER BY DIstance ASC LIMIT 10   ;";

    return DB::select($EcludianDistanceQuery);

   }

   public function SendNotification($deviceToken,$order,$resturant){


    $url = 'https://fcm.googleapis.com/fcm/send';

    $fields = array (
            'registration_ids' => array (
                    $deviceToken
            ),
            'data' => array (
                    'NotIficationType'=>'neworder',
                    'Data'=>[
                        'ResturantName'=>$resturant->name,
                        'ResturantLocation'=>$resturant->location,
                        'ResturantLat'=>$resturant->lat,
                        'Resturantlng'=>$resturant->lng,
                        'OrderId'=>$order->order_id,
                        'OrderLocation'=>$order->orderDest,
                        'OrderCost'=>$order->orderCost,
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

   public function AddDriverOrder($driver_id,$order_id){
    return Order_driver_Table::create([
        'driver_id'=>$driver_id,
        'order_id'=>$order_id,
        'status'=>'0',
        ]);

   }




    public function OrderNotficationResponse(Request $request){
        $validation=Validator::make($request->all(),
        [
            'driver_id'=>'required|exists:users,id|exists:drivers,user_id',
            'delivrycost'=>'numeric',
            'responseStatus'=>'required',
            'order_id'=>"required|exists:orders,id"
        ]);
        if($validation->fails())
        {
           $this->_result->IsSuccess = false;
           $this->_result->FaildReason =  $validation->errors()->first();
           return Response::json($this->_result,200);
        }
        if($request->responseStatus=='-1'){
            $this->UpdateOrderDriverTable($request->order_id,$request->driver_id,'-1');
        }
        else if($request->responseStatus=='1'){
            $this->UpdateOrderDriverTable($request->order_id,$request->driver_id,'2',$request->delivrycost);
        }

        $NumberOfDriversDidnotRespond = Order_driver_Table::where('order_id',$request->order_id)->where('status','0')->count();
        $order =Order_Table::find($request->order_id);

        if($NumberOfDriversDidnotRespond == 0 && $order->status =='0' )
        {
            // DB::table('jobs')->where('id',$order->JobId)->delete();
            $this->dispatch((new checkIfOrderDone($order)));
               //You SHould Check If Request Already

        }


        $this->_result->IsSuccess = true;
        return Response::json($this->_result,200);
    }

    public function UpdateOrderDriverTable($order_id,$driver_id,$status,$cost=null){
        Order_driver_Table::where('order_id',$order_id)->where('driver_id',$driver_id)->update(['status'=> $status,'cost'=>$cost]);
    }

    public function Assignorder($order){
        // $EcludianDistanceQuery ="SELECT `driver_id`,cost,SQRT( POWER(`cost`- $order->expectedDeliveryCost,2) ) as DIstance FROM `order_drivers`  WHERE `order_id`='$order->id' AND `status` = '1' ORDER BY DIstance ASC LIMIT 10;";
        // $costs  = DB::select($EcludianDistanceQuery);
        // if(count($costs) > 0){

        // }

    }
}
