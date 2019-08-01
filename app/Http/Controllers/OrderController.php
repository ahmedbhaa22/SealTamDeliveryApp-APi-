<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Validator;
use Response;
use App\User;
use App\Order;
use App\Driver;
use App\Http\ViewModel\ResultVM;
use App\Jobs\updateFireBase;
<<<<<<< HEAD
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
=======
use Carbon\Carbon;
>>>>>>> 95fc374d012710560f25b99acb3e4a7e56c91b70
class OrderController extends Controller
{

    private $_result;
    public function __construct()
    {

       $this->_result=new ResultVM();
    }


    public function cancel_order_status(Request $request){
        $validation=Validator::make($request->all(),
        [

           'resturant_id'     =>'required|numeric',
           'order_id' =>'required',


        ]);

        if($validation->fails())
        {
           $this->_result->IsSuccess = false;
           $this->_result->FaildReason =  $validation->errors()->first();
           return Response::json($this->_result,200);
        }
        $order = DB::table('orders')->where('resturant_id', $request->resturant_id)
        ->where('id', $request->order_id)->first();

       if($order){
           if($order->status=='0'|| $order->status == "1" ){
            DB::table('orders')->where('resturant_id', $request->resturant_id)
            ->where('id', $request->order_id)->update(['status'=>'-2']);
            $order = Order::find($request->order_id);

            $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/sealteamdeliveryapp-firebase-adminsdk-yra65-b8ba7856bd.json');
            $firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://sealteamdeliveryapp.firebaseio.com')->create();
            $database = $firebase->getDatabase();

                $newOrder = $database
                ->getReference('Orders/'.$order['resturant_id'].'/'.$order['id'])
                ->set(null);

            $this->_result->IsSuccess = true;
            return Response::json($this->_result,200);

           }
           else if($order->status < 0){
            $this->_result->IsSuccess = true;
            return Response::json($this->_result,200);
           }
           else{
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  'driver-arrived';
            return Response::json($this->_result,200);
           }

       }
       else{
        $this->_result->IsSuccess = false;
        $this->_result->FaildReason =  'order-seald';
        return Response::json($this->_result,200);
       }
    }

   public function change_order_status(Request $request) {

            $validation=Validator::make($request->all(),
             [

                'driver_id'     =>'required|numeric',
                'order_id' =>'required',
                'status'   =>'required|in:2,3,4,5',

             ]);

             if($validation->fails())
             {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason =  $validation->errors()->first();
                return Response::json($this->_result,200);
             }

            $order = DB::table('orders')->where('driver_id', $request->driver_id)
             ->where('id', $request->order_id)->first();

<<<<<<< HEAD
            if($order){
                if($order->driver_id != $request->driver_id ){
                    $this->_result->IsSuccess = false;
                       $this->_result->FaildReason = 'not-driver';
                   return Response::json($this->_result,200);
=======
            if($order) 
            {
               if($order->driver_id != $request->driver_id )
                 {
                  $this->_result->IsSuccess = true;
                  $this->_result->FaildReason = 'not-driver';
                  return Response::json($this->_result,200);
>>>>>>> 95fc374d012710560f25b99acb3e4a7e56c91b70
                }

                $oldStatus =$order->status;

                if($oldStatus >= $request->status || $order->status == '-2'|| $order->status == '-1')
                {
                    $this->_result->FaildReason = 'not-available';

                    $this->_result->IsSuccess = false;
                    $this->_result->FaildReason =  'Closed';

                   return Response::json($this->_result,200);
                }
                else {

                    
                    if ($request->status == '2')
                    {
                      $update =  DB::table('orders')
                       ->where('id', $request->order_id)
                       ->update(['status' => $request->status, 'arrived_at'=>Carbon::now('GMT+2')]);
                    }

                     if ($request->status == '3')
                    {
                       $update =  DB::table('orders')
                       ->where('id', $request->order_id)
                       ->update(['status' => $request->status, 'received_at'=>Carbon::now('GMT+2')]);
                     //  ->update(['status' => $request->status, 'received_at'=>now()]);
                     //  ->update(['status' => $request->status, 'received_at'=>date("Y-m-d H:i:s")]);
                    }


                    if($request->status == '4')
                    {
                      $update =  DB::table('orders')
                      ->where('id', $request->order_id)
                      ->update(['status' => $request->status, 'delivered_at'=>Carbon::now('GMT+2')]);


                      $Driver =Driver::where('user_id',$order->driver_id)
                      ->first();
                      
                     $Driver->CurrentBalance += $order->deliveryCost;
                      if($Driver->CurrentBalance >= 100)
                      {
                          $Driver->canReceiveOrder = '0';

                      }
                      $Driver->save();
                    }

                    $order = Order::find($request->order_id);
                    updateFireBase::dispatch($order)->onQueue('firebase');


                     $this->_result->IsSuccess = true;
                     $this->_result->Data = $update;
                    return Response::json($this->_result,200);



                }
            }


              

         } // end change_order_status

public function get_current_order($driver_id) {

            $currentOrder =  DB::table('orders')
                  ->join('order_drivers','orders.id', '=', 'order_drivers.order_id')
                  ->join('resturants','resturants.user_id', '=', 'orders.resturant_id')
                  ->join('users','users.id', '=', 'orders.resturant_id')
                  ->select('orders.id','orders.status as status','deliveryCost','customerPhone','customerName','OrderNumber','orderDest','orderCost','users.name as ResturantName','users.rate as ResturantRate','resturants.user_id as resturantID','resturants.lat as resturantslat','resturants.lng as resturantslng','resturants.location as resturantslocation','resturants.telephone as resturantsTelephone')
                  ->where('orders.driver_id', $driver_id)->whereIn('orders.status', ['1', '2', '3'])
                  ->get();




            $pendingOrder =DB::table('orders')
                ->join('order_drivers','orders.id', '=', 'order_drivers.order_id')
                ->join('resturants','resturants.user_id', '=', 'orders.resturant_id')
                ->join('users','users.id', '=', 'orders.resturant_id')
                ->select('orders.id','orders.status as status','customerPhone','customerName','OrderNumber','orderDest','orderCost','users.name as ResturantName','users.rate as ResturantRate','resturants.user_id as resturantID','resturants.lat as resturantslat','resturants.lng as resturantslng','resturants.location as resturantslocation','resturants.telephone as resturantsTelephone')
                ->where('order_drivers.driver_id',$driver_id)->where('order_drivers.status','0')->whereNull('orders.driver_id')->whereIn('orders.status', ['0'])
                ->get();

              $this->_result->IsSuccess = true;
              $this->_result->Data =['CurrentOrders'=>$currentOrder,'PendingOrders'=>$pendingOrder];
             return Response::json($this->_result,200);



         } // end get_current_order



                public function  get_history(Request $request) {


            $validation=Validator::make($request->all(),
             [

                'driver_id'     =>'required|numeric',
                'date' => 'date_format:"Y-m-d"|required',

             ]);

             if($validation->fails())
             {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason =  $validation->errors()->first();
                return Response::json($this->_result,200);
             }


 $orderHistory =  DB::table('orders')
                  ->join('order_drivers','orders.id', '=', 'order_drivers.order_id')
                  ->join('resturants','resturants.user_id', '=', 'orders.resturant_id')
                  ->join('users','users.id', '=', 'orders.resturant_id')
                  ->select('orders.id','orders.status as status','deliveryCost','customerPhone','customerName','OrderNumber','orderDest','orderCost','users.name as ResturantName','resturants.lat as resturantslat','resturants.lng as resturantslng')
                  ->where('orders.driver_id', $request->driver_id)->whereDate('orders.created_at', date($request->date))
                  ->get();

  if(count($orderHistory) > 0) {
                $ordersCount = count($orderHistory);

               $currentBalance =  DB::table('drivers')
                  ->where('user_id', $request->driver_id)->select('CurrentBalance')
                  ->get();

              $this->_result->IsSuccess = true;
              $this->_result->Data =['CurrentBalance'=>$currentBalance,'OrdersCount'=>$ordersCount, 'OrderHistory'=>$orderHistory];
              return Response::json($this->_result,200);

          } else {

              $this->_result->IsSuccess = false;
              $this->_result->FaildReason = 'There Is No Orders History Found';
              return Response::json($this->_result,200);
          }


         } // end get_history


    public function  get_history_resturants(Request $request) {


            $validation=Validator::make($request->all(),
                [
                    'resturant'     =>'required|exists:users,id',
                    'date' => 'date_format:"Y-m-d"|required',
                ]);

            if($validation->fails())
                {
                    $this->_result->IsSuccess = false;
                    $this->_result->FaildReason =  $validation->errors()->first();
                    return Response::json($this->_result,200);
                }


            $orderHistory =  DB::table('orders')
                            ->leftJoin('drivers','drivers.user_id', '=', 'orders.driver_id')
                            ->leftJoin('users','users.id', '=', 'orders.driver_id')
                            ->select('orders.id','orders.created_at','orders.updated_at','orders.status as status','deliveryCost','customerPhone','customerName','OrderNumber','orderDest','orderCost','users.name as DriverName',"drivers.telephone as DriverPhone","drivers.lat as Driverlat","drivers.lng as Driverlng","drivers.image as DriverImage",'deliveryCost','users.rate as DriverRate','orders.driverRate as OrderRate')
                            ->where('orders.resturant_id', $request->resturant)->whereDate('orders.created_at', date($request->date))
                            ->get();


            $this->_result->IsSuccess = true;
            $this->_result->Data =[ 'OrderHistory'=>$orderHistory];
            return Response::json($this->_result,200);




    } // end get_history



   public function rate_driver(Request $request)
        {
          $validation=Validator::make($request->all(),
        [

           'resturant_id'     =>'required|numeric',
           'order_id' =>'required|numeric',
           'rate' =>'required|in:1,2,3,4,5',


        ]);

        if($validation->fails())
        {
           $this->_result->IsSuccess = false;
           $this->_result->FaildReason =  $validation->errors()->first();
           return Response::json($this->_result,200);
        }


             $update =  DB::table('orders')
                  ->where('resturant_id', $request->resturant_id)
                  ->where('id', $request->order_id)
                  ->update(['driverRate' => $request->rate]);



        $get_Data = Order::where('id', $request->order_id)->first();

        if ($get_Data->count() > 0) {

        // $count_1 = Order::where('driver_id', $get_Data->driver_id)->where('driverRate', 1)->count();
        // $count_2 = Order::where('driver_id', $get_Data->driver_id)->where('driverRate', 2)->count();
        // $count_3 = Order::where('driver_id', $get_Data->driver_id)->where('driverRate', 3)->count();
        // $count_4 = Order::where('driver_id', $get_Data->driver_id)->where('driverRate', 4)->count();
        // $count_5 = Order::where('driver_id', $get_Data->driver_id)->where('driverRate', 5)->count();
        $total = DB::table('orders')->where('driver_id', $get_Data->driver_id)->whereNotNull('orders.driverRate')->sum('orders.driverRate');
        $count = DB::table('orders')->where('driver_id', $get_Data->driver_id)->whereNotNull('orders.driverRate')->count();

        $final  = $total/$count;
        //Five Queries ??? WHy ????????????????????
        //  $total_rate = $count_1 + $count_2 + $count_3 + $count_4 + $count_5; //???????? eh da ????
        //      $total_rate_final = ($count_1 * 1 + $count_2 * 2 + $count_3 * 3 + $count_4 * 4 + $count_5 * 5) / $total_rate;

          $finalupdate =  DB::table('users')
                  ->where('id', $get_Data->driver_id)
                  ->update(['rate' => $final]);

        }


        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result,200);




    }// end rate_driver


          public function rate_resturant(Request $request)
        {
          $validation=Validator::make($request->all(),
        [

           'driver_id'     =>'required|numeric',
           'order_id' =>'required|numeric',
           'rate' =>'required|in:1,2,3,4,5',


        ]);

        if($validation->fails())
        {
           $this->_result->IsSuccess = false;
           $this->_result->FaildReason =  $validation->errors()->first();
           return Response::json($this->_result,200);
        }


             $update =  DB::table('orders')
                  ->where('driver_id', $request->driver_id)
                  ->where('id', $request->order_id)
                  ->update(['resturantRate' => $request->rate]);



        $get_Data = Order::where('id', $request->order_id)->first();

        if ($get_Data->count() > 0) {

        // $count_1 = Order::where('resturant_id', $get_Data->resturant_id)->where('resturantRate', 1)->count();
        // $count_2 = Order::where('resturant_id', $get_Data->resturant_id)->where('resturantRate', 2)->count();
        // $count_3 = Order::where('resturant_id', $get_Data->resturant_id)->where('resturantRate', 3)->count();
        // $count_4 = Order::where('resturant_id', $get_Data->resturant_id)->where('resturantRate', 4)->count();
        // $count_5 = Order::where('resturant_id', $get_Data->resturant_id)->where('resturantRate', 5)->count();

        $total = DB::table('orders')->where('resturant_id', $get_Data->resturant_id)->whereNotNull('orders.resturantRate')->sum('orders.resturantRate');
        $count = DB::table('orders')->where('resturant_id', $get_Data->resturant_id)->whereNotNull('orders.resturantRate')->count();

        $final  = $total/$count;

        // $total_rate = $count_1 + $count_2 + $count_3 + $count_4 + $count_5;
        // $total_rate_final = ($count_1 * 1 + $count_2 * 2 + $count_3 * 3 + $count_4 * 4 + $count_5 * 5) / $total_rate;

          $finalupdate =  DB::table('users')
                  ->where('id', $get_Data->resturant_id)
                  ->update(['rate' => $final]);

        }


        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result,200);



          }// end rate_resturant


           public function get_driver_rate($driver_id)
        {

             $update =  DB::table('users')
                  ->where('id', $driver_id)
                  ->select('rate')->get();

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

          }// end get_driver_rate


 public function get_resturant_rate($resturant_id)
        {

             $update =  DB::table('users')
                  ->where('id', $resturant_id)
                  ->select('rate')->get();

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

          }// end get_resturant_rate






}
