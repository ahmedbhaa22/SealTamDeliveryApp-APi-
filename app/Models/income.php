<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class income extends BaseModel
{
    public function order()
    {
        return $this->belongsTo('App\Order');
    }
    public function storeOrderProfit($order)
    {
        $this->setData($order);
        $this->type = 'order';
        $this->describtion = 'order #'.$order->id .' Profit';
        $this->order_id = $order->id;
        $this->date = date('Y-m-d');
        $this->save();
    }
    public function storeMiniDasboardProfit($request)
    {
        $this->amount = $request->amount;
        $this->user_id =auth()->user()->id;
        $this->type = 'MiniDashboard';
        $this->describtion = 'MiniDashboard #'.$request->mini_dashboard_id .' Paid ' . $this->amount . ' To Activate Affliate For '.$request->days.'days';
        $this->mini_dashboard_id = $request->mini_dashboard_id;
        $this->date = $request->date;
        $this->save();
    }


    public function setData($order)
    {
        $this->amount = $order->companyProfit;
        $this->user_id = $order->driver_id;
    }
}
