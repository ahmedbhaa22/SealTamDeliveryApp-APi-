<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class expense extends BaseModel
{
    public $fillable =['amount','employee_id','describtion','order_id','mini_dashboard_id','user_id' ,'type','date' ];


    public function storeOrderMiniDashboardProfit($order, $mini_dashboard)
    {
        $this->setData($order->companyProfit * ($mini_dashboard->earning_ratio/100), $order);
        $this->type = 'affiliate';
        $this->describtion = '('.$mini_dashboard->earning_ratio.'%) of order #'.$order->id .' Profit Goes To MiniDashboard #'.$mini_dashboard->id.' Due To Affliate Policy';
        $this->order_id = $order->id;
        $this->mini_dashboard_id = $mini_dashboard->id;
        $this->currency_id= $mini_dashboard->currency_id;
        $this->date = date('Y-m-d');

        $this->save();
    }


    public function setData($amount, $order)
    {
        $this->amount = $amount;
        $this->user_id = $order->driver_id;
    }
}
