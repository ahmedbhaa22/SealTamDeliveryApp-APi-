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

            updateFireBase::dispatch($order)->onQueue('firebase');
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

            if($order){
                if($order->driver_id != $request->driver_id ){
                    $this->_result->IsSuccess = true;
                       $this->_result->FaildReason = 'not-driver';
                   return Response::json($this->_result,200);
                }

                $oldStatus =$order->status;
                if($oldStatus >= $request->status || $order->status == '-2'|| $order->status == '-1'){
                       $this->_result->FaildReason = 'not-available';

                    $this->_result->IsSuccess = true;
                   return Response::json($this->_result,200);
                }

                else{

                    $update =  DB::table('orders')
                    ->where('id', $request->order_id)
                    ->update(['status' => $request->status]);
                    if($request->status == '4'){
                        $Driver =Driver::where('user_id',$order->driver_id)
                        ->first();
                        $Driver->CurrentBalance += $order->deliveryCost;
                        if($Driver->CurrentBalance >= 100){
                            $Driver->canReceiveOrder = 0;

                        }
                        $Driver->save();
                    }

                    $order = Order::find($request->order_id);
                    updateFireBase::dispatch($order)->onQueue('firebase');

                }
            }

               $this->_result->FaildReason = 'ddd';

              $this->_result->IsSuccess = true;
             return Response::json($this->_result,200);

         } // end change_order_status


          public function get_current_order($driver_id) {

            $currentOrder =  DB::table('orders')
                  ->join('order_drivers','orders.id', '=', 'order_drivers.order_id')
                  ->join('resturants','resturants.user_id', '=', 'orders.resturant_id')
                  ->join('users','users.id', '=', 'orders.resturant_id')
                  ->select('orders.id','orders.status as status','deliveryCost','customerPhone','customerName','OrderNumber','orderDest','orderCost','users.name as ResturantName','resturants.lat as resturantslat','resturants.lng as resturantslng')
                  ->where('orders.driver_id', $driver_id)->whereIn('orders.status', ['1', '2', '3'])
                  ->get();


            $pendingOrder =DB::table('orders')
                ->join('order_drivers','orders.id', '=', 'order_drivers.order_id')
                ->join('resturants','resturants.user_id', '=', 'orders.resturant_id')
                ->join('users','users.id', '=', 'orders.resturant_id')
                ->select('orders.id','orders.status as status','customerPhone','customerName','OrderNumber','orderDest','orderCost','users.name as ResturantName','resturants.lat as resturantslat','resturants.lng as resturantslng')
                ->where('order_drivers.driver_id',$driver_id)->whereNull('orders.driver_id')->whereIn('orders.status', ['0'])
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

}
