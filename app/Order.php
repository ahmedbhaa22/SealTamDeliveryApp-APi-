<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\expense;
use App\Models\income;
use App\Helpers\FCM;
use App\Helpers\FireBaseHelper;
use App\Jobs\updateFireBase;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = [];

    const NEWORDER = '0';

    protected $hidden = [
        'arrived_at', 'received_at','delivered_at',
    ];
    public function Orderdriver()
    {
        return $this->hasMany('App\order_driver', 'order_id', 'id');
    }
    public function drivers()
    {
        return $this->hasManyThrough('App\Driver', 'App\order_driver', 'order_id', 'user_id', 'id', 'driver_id');
    }

    public function resturant()
    {
        return $this->hasOne('App\Resturant', 'user_id', 'resturant_id');
    }

    public function Assigneddriver()
    {
        return $this->hasOne('App\Driver', 'user_id', 'driver_id');
    }
    public function order_drivers()
    {
        return $this->belongsToMany('App\Driver', 'App\order_driver', 'order_id', 'driver_id');
    }
    public function order_income()
    {
        return $this->hasOne(income::class);
    }
    public function order_expense()
    {
        return $this->hasOne(expense::class);
    }
    public static function addOrderExpenseAndIncome($order_id, $Driver)
    {
        $order= self::with('order_income')->with('order_expense')->find($order_id);

        if ($order->order_income ==null) {
            $order->order_income  = new income();
            $order->order_income->storeOrderProfit($order, $Driver->miniDashboard);
            if ($order->order_expense ==null) {
                if ($Driver->miniDashboard !=null &&($Driver->miniDashboard->days_left >0 ||$Driver->miniDashboard->type=='No Limit')) {
                    $order->order_expense  = new expense();
                    $order->order_expense->storeOrderMiniDashboardProfit($order, $Driver->miniDashboard);
                }
            }
        }
    }
    public function savenewOrder($resturant)
    {
        $this->status = Order::NEWORDER;
        $this->resturant_id =$resturant->user_id;
        $this->orderCost =request()->orderCost;
        $this->deliveryCost =$resturant->type=='Normal'? null : $resturant->order_price;
        $this->customerPhone =request()->customerPhone;
        $this->customerName =request()->customerName;
        $this->OrderNumber =request()->OrderNumber;
        $this->orderDest =request()->orderDest;
        $this->expectedDeliveryCost =request()->expectedDeliveryCost;
        $this->companyProfit =$resturant->type=='Normal'? null : $resturant->order_price * .25 ;
        $this->save();
    }
    public function create_new_order($resturant, $drivers)
    {
        $this->savenewOrder($resturant);

        $driverIds = array_map(function ($p) {
            return $p['user_id'];
        }, $drivers->toArray());

        $this->order_drivers()->sync($driverIds);
        $this->drivers()->update([
            'busy'=>1
        ]);
        FireBaseHelper::getInstance()->addOrder($this);
        $notification =  array(
            'NotIficationType'=>$resturant->shop_type=='Normal' ?'neworder':"newFixedOrder",
            'Data'=>[
                'ResturantName'=>auth()->user()->name,
                'ResturantLocation'=>$resturant->location,
                'ResturantLat'=>$resturant->lat,
                'Resturantlng'=>$resturant->lng,
                'OrderId'=>$this->id,
                'OrderLocation'=>$this->orderDest,
                'OrderCost'=>$this->orderCost,
                //'DeliveryCost'=>$resturant->type=='Normal'? null : $resturant->order_price
            ]
            );

        foreach ($drivers as $driver) {
            FCM::snedNotification($notification, $driver->deviceToken);
        }
    }

    public function AcceptForFixed()
    {
        $orderdrriver= $this->Orderdriver()
            ->where('driver_id', auth()->user()->driver->user_id)->first();
        if ($orderdrriver!= null) {
            $driver = $this->driver_id=auth()->user()->driver;
            $orderdrriver->status='1';
            $orderdrriver->save();

            $this->driver_id=$driver->user_id;
            $this->status = 1;
            $this->save();
            $this->drivers()->where('driver_id', '<>', $driver->user_id)->update([
                'busy'=>0
            ]);
            $notification =  array(
                'NotIficationType'=>'orderaccepted',
                'Data'=>[
                    'OrderId'=>$this->id,
                ]
                );
            FCM::snedNotification($notification, $driver->deviceToken);
            updateFireBase::dispatch($this)->onQueue('firebase');
        }
    }

    public function refuse()
    {
        $orderdrriver= $this->Orderdriver()
        ->where('driver_id', auth()->user()->driver->user_id)->first();
        if ($orderdrriver!= null) {
            $driver =auth()->user()->driver;

            $orderdrriver->status='-1';
            $orderdrriver->save();
            $driver->busy=0;
            $driver->save();
            if ($this->Orderdriver()->where('status', '-1')->count() ==$this->Orderdriver()->count()) {
                $this->status= '-1';
                $this->save();
                updateFireBase::dispatch($this)->onQueue('firebase');
            }
        }
    }
}
