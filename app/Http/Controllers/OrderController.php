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
use App\Http\ViewModel\OrderSearchVM;

use App\Jobs\updateFireBase;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use App\Order_driver as Order_driver_Table;
use  App\Events\drivers_status;

class OrderController extends Controller
{
    private $_result;
    public function __construct()
    {
        $this->_result=new ResultVM();
    }


    public function all_orders()
    {
        $driver_id = Input::get('driver_id');
        $resturant_id = Input::get('resturant_id');

        //Case 1
        if ($driver_id == null && $resturant_id == null) {
            $allorders = DB::table('orders')->orderBy('created_at', 'desc')->paginate(12);

            $Completed_Orders = DB::table('orders')->where('status', '4')->count();
            $canceld_orders =  DB::table('orders')->where('status', '-2')->count();
            $nodriver_orders =  DB::table('orders')->where('status', '-1')->count();
            $pending_orders =  DB::table('orders')->where('status', '0')->count();



            if (count($allorders) > 0) {
                $this->_result->IsSuccess = true;
                $this->_result->Data = [
                'Completed_Orders '=>$Completed_Orders ,
                'canceld_orders'=>$canceld_orders,
                'nodriver_orders'=>$nodriver_orders,
                'pending_orders'=>$pending_orders,
                'AllOrders'=>$allorders

              ];
                return Response::json($this->_result, 200);
            } else {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason = 'No Orders Found';
                return Response::json($this->_result, 200);
            }
        }
        //Case 2
        elseif ($driver_id == null && $resturant_id != null) {
            $allorders = DB::table('orders')->where('resturant_id', $resturant_id)->paginate(12);
            $Completed_Orders = DB::table('orders')->where('resturant_id', $resturant_id)->where('status', '4')->count();
            $canceld_orders =  DB::table('orders')->where('resturant_id', $resturant_id)->where('status', '-2')->count();
            $nodriver_orders =  DB::table('orders')->where('resturant_id', $resturant_id)->where('status', '-1')->count();
            $pending_orders =  DB::table('orders')->where('resturant_id', $resturant_id)->where('status', '0')->count();

            if (count($allorders) > 0) {
                $this->_result->IsSuccess = true;
                $this->_result->Data = [
                'Completed_Orders '=>$Completed_Orders ,
                'canceld_orders'=>$canceld_orders,
                'nodriver_orders'=>$nodriver_orders,
                'pending_orders'=>$pending_orders,
                'AllOrders'=>$allorders

              ];
                return Response::json($this->_result, 200);
            } else {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason = 'No Orders  Found';
                return Response::json($this->_result, 200);
            }
        }

        //Case 3
        elseif ($driver_id != null && $resturant_id == null) {
            $allorders = DB::table('orders')->where('driver_id', $driver_id)->paginate(12);
            $Completed_Orders = DB::table('orders')->where('driver_id', $driver_id)->where('status', '4')->count();
            $canceld_orders =  DB::table('orders')->where('driver_id', $driver_id)->where('status', '-2')->count();
            $nodriver_orders =  DB::table('orders')->where('driver_id', $driver_id)->where('status', '-1')->count();
            $pending_orders =  DB::table('orders')->where('driver_id', $driver_id)->where('status', '0')->count();

            if (count($allorders) > 0) {
                $this->_result->IsSuccess = true;
                $this->_result->Data = [
                'Completed_Orders '=>$Completed_Orders ,
                'canceld_orders'=>$canceld_orders,
                'nodriver_orders'=>$nodriver_orders,
                'pending_orders'=>$pending_orders,
                'AllOrders'=>$allorders

              ];
                return Response::json($this->_result, 200);
            } else {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason = 'No Orders  Found';
                return Response::json($this->_result, 200);
            }
        }

        //Case 4

        else {
            $allorders = DB::table('orders')->where('driver_id', $driver_id)->where('resturant_id', $resturant_id)->paginate(12);

            $Completed_Orders = DB::table('orders')->where('driver_id', $driver_id)->where('resturant_id', $resturant_id)->where('status', '4')
             ->count();
            $canceld_orders =  DB::table('orders')->where('driver_id', $driver_id)->where('resturant_id', $resturant_id)->where('status', '-2')
             ->count();
            $nodriver_orders =  DB::table('orders')->where('driver_id', $driver_id)->where('resturant_id', $resturant_id)->where('status', '-1')
             ->count();
            $pending_orders =  DB::table('orders')->where('driver_id', $driver_id)->where('resturant_id', $resturant_id)->where('status', '0')
             ->count();

            if (count($allorders) > 0) {
                $this->_result->IsSuccess = true;
                $this->_result->Data = [
                'Completed_Orders '=>$Completed_Orders ,
                'canceld_orders'=>$canceld_orders,
                'nodriver_orders'=>$nodriver_orders,
                'pending_orders'=>$pending_orders,
                'AllOrders'=>$allorders

              ];
                return Response::json($this->_result, 200);
            } else {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason = 'No Orders  Found';
                return Response::json($this->_result, 200);
            }
        }
    } // End  ALL ORDERS


    public function cancel_order_status(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [

           'resturant_id'     =>'required|numeric',
           'order_id' =>'required',


        ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }
        $order = DB::table('orders')->where('resturant_id', $request->resturant_id)
        ->where('id', $request->order_id)->first();

        if ($order) {
            if ($order->status=='0'|| $order->status == "1") {
                DB::table('orders')->where('resturant_id', $request->resturant_id)
            ->where('id', $request->order_id)->update(['status'=>'-2']);
                $order = Order::find($request->order_id);

                $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/sealteamdeliveryapp-firebase-adminsdk-yra65-b8ba7856bd.json');
                $firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://sealteamdeliveryapp.firebaseio.com')->create();
                $database = $firebase->getDatabase();

                $newOrder = $database
                ->getReference('Orders/'.$order['resturant_id'].'/'.$order['id'])
                ->set(null);

                if ($order->driver_id) {
                    $otherDriverOrders  =  DB::table('orders')->where('driver_id', $order->driver_id)->where('status', '>=', '1')->where('status', '<', '4')->count();

                    if ($otherDriverOrders ==0) {
                        DB::table('drivers')->where('user_id', $order->driver_id)->update(["busy"=>false]);
                        event(new drivers_status($order->driver_id));
                    }
                } else {
                    $unluckyDrivers = Order_driver_Table::where('order_id', $order->id)->where('status', '1')
                ->get();


                    foreach ($unluckyDrivers as $unlucky) {
                        DB::table('drivers')->where('user_id', $unlucky->driver_id)->update(["busy"=>false]);
                        event(new drivers_status($unlucky->driver_id));
                    }

                    $DriversDidnotRespond = Order_driver_Table::where('order_id', $order->id)->where('status', '0')->get();

                    foreach ($DriversDidnotRespond as $notResponded) {
                        DB::table('drivers')->where('user_id', $notResponded->driver_id)->update(["busy"=>false]);
                        event(new drivers_status($notResponded->driver_id));
                    }
                }
                $this->_result->IsSuccess = true;
                return Response::json($this->_result, 200);
            } elseif ($order->status < 0) {
                $this->_result->IsSuccess = true;
                return Response::json($this->_result, 200);
            } else {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason =  trans('messages.Driver_Arrived');

                return Response::json($this->_result, 200);
            }
        } else {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  trans('messages.order_seald');
            ;
            return Response::json($this->_result, 200);
        }
    }

    public function change_order_status(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [

                'driver_id'     =>'required|numeric',
                'order_id' =>'required',
                'status'   =>'required|in:2,3,4,5',

             ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }

        $order = DB::table('orders')->where('driver_id', $request->driver_id)
             ->where('id', $request->order_id)->first();



        if ($order) {
            if ($order->driver_id != $request->driver_id) {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason = 'not-driver';
                return Response::json($this->_result, 200);
            }

            $oldStatus =$order->status;

            if ($oldStatus >= $request->status || $order->status == '-2'|| $order->status == '-1') {
                $this->_result->FaildReason = 'not-available';

                $this->_result->IsSuccess = false;
                $this->_result->FaildReason =  'Closed';

                return Response::json($this->_result, 200);
            } else {
                if ($request->status == '2') {
                    $update =  DB::table('orders')
                       ->where('id', $request->order_id)
                       ->update(['status' => $request->status, 'arrived_at'=>Carbon::now()]);
                    //  ->update(['status' => $request->status, 'arrived_at'=>now()]);
                     //  ->update(['status' => $request->status, 'arrived_at'=>date("Y-m-d H:i:s")]);
                }

                if ($request->status == '3') {
                    $update =  DB::table('orders')
                       ->where('id', $request->order_id)
                       ->update(['status' => $request->status, 'received_at'=>Carbon::now()]);
                }


                if ($request->status == '4') {
                    $update =  DB::table('orders')
                      ->where('id', $request->order_id)
                      ->update(['status' => $request->status, 'delivered_at'=>Carbon::now()]);
                    $otherDriverOrders  =  DB::table('orders')->where('driver_id', $order->driver_id)->where('status', '>=', '1')->where('status', '<', '4')->count();



                    $Driver =Driver::where('user_id', $order->driver_id)->with('miniDashboard')
                      ->first();

                    $Driver->CurrentBalance += $order->deliveryCost;
                    if ($Driver->CurrentBalance >= 4000) {
                        $Driver->canReceiveOrder = '0';
                    }
                    if ($otherDriverOrders ==0) {
                        $Driver->busy =false;
                        event(new drivers_status($Driver->user_id));
                    }
                    $Driver->save();
                    Order::addOrderExpenseAndIncome($order->id, $Driver);
                }

                $order = Order::find($request->order_id);
                $this->update_order_at_fireBase($order);


                $this->_result->IsSuccess = true;
                $this->_result->Data = $update;
                return Response::json($this->_result, 200);
            }
        }
    } // end change_order_status

    public function get_current_order($driver_id)
    {
        $currentOrder =  DB::table('orders')
                  ->join('resturants', 'resturants.user_id', '=', 'orders.resturant_id')
                  ->join('users', 'users.id', '=', 'orders.resturant_id')
                  ->select('orders.id', 'orders.status as status', 'deliveryCost', 'customerPhone', 'customerName', 'OrderNumber', 'orderDest', 'orderCost', 'users.name as ResturantName', 'users.rate as ResturantRate', 'resturants.user_id as resturantID', 'resturants.lat as resturantslat', 'resturants.lng as resturantslng', 'resturants.location as resturantslocation', 'resturants.telephone as resturantsTelephone')
                  ->where('orders.driver_id', $driver_id)->whereIn('orders.status', ['1', '2', '3'])
                  ->get()->toArray();




        $pendingOrder =DB::table('orders')
                ->join('order_drivers', 'order_drivers.order_id', 'orders.id')
                ->join('resturants', 'resturants.user_id', '=', 'orders.resturant_id')
                ->join('users', 'users.id', '=', 'orders.resturant_id')
                ->select('orders.id', 'orders.status as status', 'deliveryCost', 'customerPhone', 'customerName', 'OrderNumber', 'orderDest', 'orderCost', 'users.name as ResturantName', 'users.rate as ResturantRate', 'resturants.user_id as resturantID', 'resturants.lat as resturantslat', 'resturants.lng as resturantslng', 'resturants.location as resturantslocation', 'resturants.telephone as resturantsTelephone')
                ->where('order_drivers.driver_id', $driver_id)->where('order_drivers.status', '0')->whereNull('orders.driver_id')->whereIn('orders.status', ['0'])
                ->get()->toArray();

        $this->_result->IsSuccess = true;
        $this->_result->Data =['CurrentOrders'=>array_merge($currentOrder, $pendingOrder)];
        return Response::json($this->_result, 200);
    } // end get_current_order



    public function get_history(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [

                'driver_id'     =>'required|numeric',
                'date' => 'date_format:"Y-m-d"|required',

             ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }


        $orderHistory =  DB::table('orders')
                  ->join('resturants', 'resturants.user_id', '=', 'orders.resturant_id')
                  ->join('users', 'users.id', '=', 'orders.resturant_id')
                  ->select('orders.id', 'orders.status as status', 'deliveryCost', 'customerPhone', 'customerName', 'OrderNumber', 'orderDest', 'orderCost', 'users.name as ResturantName', 'resturants.lat as resturantslat', 'resturants.lng as resturantslng', 'resturants.location as resturantslocation', 'resturants.telephone as resturantsTelephone')
                  ->where('orders.driver_id', $request->driver_id)->whereDate('orders.created_at', date($request->date))
                  ->get();

        if (count($orderHistory) > 0) {
            $ordersCount = count($orderHistory);

            $currentBalance =  DB::table('drivers')
                  ->where('user_id', $request->driver_id)->select('CurrentBalance')->first()->CurrentBalance;
            $balance = (int) $currentBalance;

            $this->_result->IsSuccess = true;
            $this->_result->Data =['OrdersCount'=>$ordersCount,'CurrentBalance'=>$balance, 'OrderHistory'=>$orderHistory];
            return Response::json($this->_result, 200);
        } else {
            $currentBalance =  DB::table('drivers')
                  ->where('user_id', $request->driver_id)->select('CurrentBalance')
                  ->first()->CurrentBalance;
            $balance = (int) $currentBalance;

            $this->_result->IsSuccess = true;
            $this->_result->Data =['OrdersCount'=>'','CurrentBalance'=>$balance ,'orderHistory'=>[]];

            return Response::json($this->_result, 200);
        }
    } // end get_history

    public function get_history_resturants(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [
                    'resturant'     =>'required|exists:users,id',
                    'date' => 'date_format:"Y-m-d"|required',
                ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }


        $orderHistory =  DB::table('orders')
                            ->leftJoin('drivers', 'drivers.user_id', '=', 'orders.driver_id')
                            ->leftJoin('users', 'users.id', '=', 'orders.driver_id')
                            ->select('orders.id', 'orders.resturant_id', 'orders.created_at', 'orders.updated_at', 'orders.status as status', 'deliveryCost', 'customerPhone', 'customerName', 'OrderNumber', 'orderDest', 'orderCost', 'users.name as DriverName', "drivers.telephone as DriverPhone", "drivers.lat as Driverlat", "drivers.lng as Driverlng", "drivers.image as DriverImage", 'deliveryCost', 'users.rate as DriverRate', 'orders.driverRate as OrderRate')
                            ->where('orders.resturant_id', $request->resturant)->whereDate('orders.created_at', date($request->date))
                            ->orderBy('created_at', 'DESC')
                            ->get();


        $this->_result->IsSuccess = true;
        $this->_result->Data =[ 'OrderHistory'=>$orderHistory];
        return Response::json($this->_result, 200);
    } // end get_history_resturants



    public function rate_driver(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [

           'resturant_id'     =>'required|numeric',
           'order_id' =>'required|numeric',
           'rate' =>'required|in:1,2,3,4,5',


        ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
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
        return Response::json($this->_result, 200);
    }// end rate_driver


    public function rate_resturant(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [

           'driver_id'     =>'required|numeric',
           'order_id' =>'required|numeric',
           'rate' =>'required',


        ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }


        $update =  DB::table('orders')
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
        return Response::json($this->_result, 200);
    }// end rate_resturant


    public function get_driver_rate($driver_id)
    {
        $update =  DB::table('users')
                  ->where('id', $driver_id)
                  ->select('rate')->get();

        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    }// end get_driver_rate


    public function get_resturant_rate($resturant_id)
    {
        $update =  DB::table('users')
                  ->where('id', $resturant_id)
                  ->select('rate')->get();

        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    }// end get_resturant_rate


    public function update_order_at_fireBase($order)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/sealteamdeliveryapp-firebase-adminsdk-yra65-b8ba7856bd.json');
        $firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://sealteamdeliveryapp.firebaseio.com')->create();
        $database = $firebase->getDatabase();
        if ($order->status == '-1'|| $order->status == '-2' || $order->status == '4') {
            $newOrder = $database
        ->getReference('Orders/'.$order['resturant_id'].'/'.$order['id'])
        ->set(null);
        } else {
            $newOrder = $database
        ->getReference('Orders/'.$order->resturant_id.'/'.$order->id.'/status')
        ->set($order->status);
        }
    }

    public function GetListPage(OrderSearchVM $searchVM)
    {
        $orders  = $searchVM->queryBuilder->select('orders.id as OrderId', 'orders.status as OrderStatus', 'orders.customerName', 'orders.customerPhone', 'orders.orderDest as Location', 'orders.orderCost', 'orders.deliveryCost', 'orders.companyProfit', 'orders.created_at', 'orders.expectedDeliveryCost', 'orders.driverRate', 'orders.resturantRate', 'd.name as driver', 'r.name as shop')->orderBy('orders.id', 'DESC')->paginate(10);

        $ordersStatus =  $searchVM->queryBuilder->select('orders.status as orderStatus ', DB::raw("count(`orders`.`status`) as count"))->groupBy('orders.status')->get();

        $this->_result->Data = ['orders' =>$orders,'status'=>$ordersStatus];
        return Response::json($this->_result);
    }

    public function GetDetailsPage(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [

             'id'     =>'required|numeric|exists:orders,id',
            ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }

        $order  =  DB::table('orders')
        ->join('resturants', 'resturants.user_id', '=', 'orders.resturant_id')
        ->join('users', 'users.id', '=', 'orders.resturant_id')
        ->leftjoin('drivers', 'drivers.user_id', '=', 'orders.driver_id')
        ->leftjoin('users as d', 'd.id', '=', 'orders.driver_id')
        ->select('orders.*', 'd.name as dname', 'drivers.telephone as dtel', 'd.email as demail', 'deliveryCost', 'customerPhone', 'customerName', 'OrderNumber', 'orderDest', 'orderCost', 'users.name as ResturantName', 'users.rate as ResturantRate', 'resturants.user_id as resturantID', 'resturants.location as resturantslocation', 'resturants.lng as resturantslng', 'resturants.location as resturantslocation', 'resturants.telephone as resturantsTelephone', 'users.email as ResturantEmail')
        ->where('orders.id', $request->id)
        ->first();
        $orderdrivers  =  DB::table('order_drivers')

        ->join('users', 'users.id', '=', 'order_drivers.driver_id')
         ->select('users.name', 'order_drivers.status', 'order_drivers.cost')
        ->where('order_drivers.order_id', $request->id)
        ->get()->toArray();

        $this->_result->IsSuccess = true;
        $this->_result->Data = ['order'=>$order,'drivers'=>$orderdrivers];
        return Response::json($this->_result, 200);
    }
}
