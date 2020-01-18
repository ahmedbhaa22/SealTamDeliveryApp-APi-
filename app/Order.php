<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\expense;
use App\Models\income;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = [];


    protected $hidden = [
        'arrived_at', 'received_at','delivered_at',
    ];
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
            $order->order_income->storeOrderProfit($order);
            if ($order->order_expense ==null) {
                if ($Driver->miniDashboard !=null &&$Driver->miniDashboard->days_left >0) {
                    $order->order_expense  = new expense();

                    $order->order_expense->storeOrderMiniDashboardProfit($order, $Driver->miniDashboard);
                }
            }
        }
    }
}
