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

    public function CreateNewOrder(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [
            'resturant_id'=>'required|exists:users,id|exists:resturants,user_id',
            'orderCost'=>'required|numeric|max:500000',
            'customerPhone'=>'required',
            'customerName'=>'required',
            'OrderNumber'=>'required',
            'orderDest'=>'required',
            'expectedDeliveryCost'=>'required|numeric|max:500000',
        ]
        );
        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }
        //Validate Order On The Data Base
        $resturant =$resturant = DB::table('users')
        ->join('resturants', 'users.id', '=', 'resturants.user_id')
        ->where('users.UserType', 'resturant')->where('resturants.user_id', $request->resturant_id)
        ->first();
        //Search For Nearest Drivers,
        $drivers = $this->SelectNearestDriver($resturant->lat, $resturant->lng);
        //Add Order And OrderDetails TO DataBase
        if (count($drivers) > 0) {
            DB::beginTransaction();

            try {
                $order = $this->AddNewOrder($request);
                foreach ($drivers as $driver) {
                    $this->SendNotification($driver->deviceToken, $order, $resturant, 'neworder');
                    $this->AddDriverOrder($driver->user_id, $order->id);
                }
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
            DB::commit();

            $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/sealteamdeliveryapp-firebase-adminsdk-yra65-b8ba7856bd.json');
            $firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://sealteamdeliveryapp.firebaseio.com')->create();
            $database = $firebase->getDatabase();
            if ($order->status == '-1'|| $order->status == '-2' || $order->status == '4') {
                $newOrder = $database
                ->getReference('Orders/'.$order['resturant_id'].'/'.$order['id'])
                ->set(null);
            }
            if ($order->driver_id) {
                $Driver = DB::table('users')
                ->join('drivers', 'users.id', '=', 'drivers.user_id')
                ->where('users.UserType', 'driver')->where('drivers.user_id', $order->driver_id)
                ->first();
                $order= $order->toArray();
                $order['DriverName'] =$Driver->name;
                $order['DriverPhone'] =$Driver->telephone;
                $order['DriverImage'] =$Driver->image;
                $order['Driverlat'] =$Driver->lat;
                $order['Driverlng'] =$Driver->lng;
            }
            $newOrder = $database
                 ->getReference('Orders/'.$order['resturant_id'].'/'.$order['id'])
                 ->set($order);
        } else {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason = trans('messages.NoDriverAvailAbleToAcceptThisRequest');
            return Response::json($this->_result, 200);
        }

        $id = $this->dispatch((new checkIfOrderDone($order))->onQueue('firebase')->delay(now()->addSeconds(552)));
        $order->JobId = $id;
        $order->save();


        $this->_result->Data = $id;
        $this->_result->IsSuccess = true;
        return Response::json($this->_result, 200);
        //Push New OrderObject TO FireBase
    }

    public function AddNewOrder($order)
    {
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



    public function SelectNearestDriver($lat, $lng)
    {
        $EcludianDistanceQuery ="SELECT `user_id`,`deviceToken`,SQRT( POWER(`lat`-$lat,2) + POWER(`lng`-$lng, 2) ) as DIstance FROM `drivers`  WHERE `availability`='on' AND `canReceiveOrder` = '1' AND  `deviceToken` IS NOT NULL ORDER BY DIstance ASC LIMIT 10   ;";

        return DB::select($EcludianDistanceQuery);
    }

    public function SendNotification($deviceToken, $order, $resturant, $message)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array(
            'registration_ids' => array(
                    $deviceToken
            ),
            'data' => array(
                    'NotIficationType'=>$message,
                    'Data'=>[
                        'ResturantName'=>$resturant->name,
                        'ResturantLocation'=>$resturant->location,
                        'ResturantLat'=>$resturant->lat,
                        'Resturantlng'=>$resturant->lng,
                        'OrderId'=>$order->id,
                        'OrderLocation'=>$order->orderDest,
                        'OrderCost'=>$order->orderCost,
                    ]
            )
    );
        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=' . "AAAAM7mSsoE:APA91bFwY_7HlIj1-R72mGcOvpXAVRfUqYnAMwkpFTORJnoCkQzxyi-Rh8mRbiESDPg4xPurR5Z1hjXQW1SzqkksL68UQCx_3zVzXBaOX6LSNhTs_mtAQ7W4AgIkOkdwGd7dL8I4RObu",
            'Content-Type: application/json'
    );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);
    }

    public function AddDriverOrder($driver_id, $order_id)
    {
        return Order_driver_Table::create([
        'driver_id'=>$driver_id,
        'order_id'=>$order_id,
        'status'=>'0',
        ]);
    }




    public function OrderNotficationResponse(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [
            'driver_id'=>'required|exists:users,id|exists:drivers,user_id',
            'delivrycost'=>'numeric|max:500000',
            'responseStatus'=>'required',
            'order_id'=>"required|exists:orders,id"
        ]
        );
        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }
        if ($request->responseStatus=='-1') {
            $this->UpdateOrderDriverTable($request->order_id, $request->driver_id, '-1');
        } elseif ($request->responseStatus=='1') {
            $this->UpdateOrderDriverTable($request->order_id, $request->driver_id, '1', $request->delivrycost);
        }

        $NumberOfDriversDidnotRespond = Order_driver_Table::where('order_id', $request->order_id)->where('status', '0')->count();
        $order =Order_Table::find($request->order_id);

        if ($NumberOfDriversDidnotRespond == 0 && $order->status =='0') {
            checkIfOrderDone::dispatch($order)->onQueue('firebase');
        }


        $this->_result->IsSuccess = true;
        return Response::json($this->_result, 200);
    }

    public function UpdateOrderDriverTable($order_id, $driver_id, $status, $cost=null)
    {
        Order_driver_Table::where('order_id', $order_id)->where('driver_id', $driver_id)->update(['status'=> $status,'cost'=>$cost]);
    }

    public function Assignorder($order)
    {
        // $EcludianDistanceQuery ="SELECT `driver_id`,cost,SQRT( POWER(`cost`- $order->expectedDeliveryCost,2) ) as DIstance FROM `order_drivers`  WHERE `order_id`='$order->id' AND `status` = '1' ORDER BY DIstance ASC LIMIT 10;";
        // $costs  = DB::select($EcludianDistanceQuery);
        // if(count($costs) > 0){

        // }
    }

    public function order_plus(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [
            'resturant_id'=>'required|exists:users,id|exists:resturants,user_id',
            'orderCost'=>'required|numeric|max:500000',
            'customerPhone'=>'required',
            'order_id'=>'required|exists:orders,id',
            'customerName'=>'required',
            'OrderNumber'=>'required',
            'orderDest'=>'required',
            'deliveryCost'=>'required|numeric|max:500000',
        ]
        );
        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }
        $order =Order_Table::where('id', $request->order_id)->where('resturant_id', $request->resturant_id)->where('resturant_id', $request->resturant_id)->first();

        if ($order == null && $order->status !='2') {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  trans('messages.Driver_Left');
            return Response::json($this->_result, 200);
        }
        $Driver = DB::table('users')
        ->join('drivers', 'users.id', '=', 'drivers.user_id')
        ->where('users.UserType', 'driver')->where('drivers.user_id', $order->driver_id)
        ->first();

        if ($Driver->CurrentBalance > 100) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  trans('messages.YouCannotAddOrdersForThisDriver');
            return Response::json($this->_result, 200);
        }
        DB::beginTransaction();

        try {
            $newOrder =   Order_Table::create([
            'status' => '2',
            'resturant_id'=>$order->resturant_id,
            'orderCost'=>$request->orderCost,
            'deliveryCost'=>$request->deliveryCost,
            'customerPhone'=>$request->customerPhone,
            'customerName'=>$request->customerName,
            'OrderNumber'=>$request->OrderNumber,
            'orderDest'=>$request->orderDest,
            'expectedDeliveryCost'=>$request->deliveryCost,
            "driver_id"=>$order->driver_id,
            "companyProfit"=>$order->deliveryCost * .25 ,
            "arrived_at"=>$order->arrived_at
            ]);
            Order_driver_Table::create([
                'driver_id'=>$order->driver_id,
                'order_id'=>$newOrder->id,
                'cost'=>$request->deliveryCost,
                'status'=>'1',
                ]);


            $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/sealteamdeliveryapp-firebase-adminsdk-yra65-b8ba7856bd.json');
            $firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://sealteamdeliveryapp.firebaseio.com')->create();
            $database = $firebase->getDatabase();

            $this->SendNotificationOrderAccepted($Driver->deviceToken, $newOrder, 'orderaccepted');
            $newOrder= $newOrder->toArray();
            $newOrder['DriverName'] =$Driver->name;
            $newOrder['DriverPhone'] =$Driver->telephone;
            $newOrder['Driverlat'] =$Driver->lat;
            $newOrder['Driverlng'] =$Driver->lng;
            $newOrder['DriverRate'] =$Driver->rate;
            $newOrder['DriverImage'] =$Driver->image;

            $newOrder = $database
                 ->getReference('Orders/'.$newOrder['resturant_id'].'/'.$newOrder['id'])
                 ->set($newOrder);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();


        $this->_result->IsSuccess = true;
        return Response::json($this->_result, 200);
    }

    public function SendNotificationOrderAccepted($deviceToken, $order, $message)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array(
                'registration_ids' => array(
                        $deviceToken
                ),
                'data' => array(
                        'NotIficationType'=>'orderaccepted',
                        'Data'=>[
                            'OrderId'=>$order->id,
                        ]
                )
        );
        $fields = json_encode($fields);

        $headers = array(
                'Authorization: key=' . "AAAAM7mSsoE:APA91bFwY_7HlIj1-R72mGcOvpXAVRfUqYnAMwkpFTORJnoCkQzxyi-Rh8mRbiESDPg4xPurR5Z1hjXQW1SzqkksL68UQCx_3zVzXBaOX6LSNhTs_mtAQ7W4AgIkOkdwGd7dL8I4RObu",
                'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);
    }
}
