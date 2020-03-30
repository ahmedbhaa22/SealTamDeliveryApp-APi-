<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Dashboard\mini_dashboard;

class income extends BaseModel
{
    public function order()
    {
        return $this->belongsTo('App\Order');
    }
    public function storeOrderProfit($order, $mini_dashboard)
    {
        $this->setData($order);
        $this->type = 'order';
        $this->describtion = 'order #'.$order->id .' Profit';
        $this->currency_id= $mini_dashboard != null ?$mini_dashboard->currency_id:1;

        $this->order_id = $order->id;
        $this->date = date('Y-m-d');
        $this->save();
    }
    public function storeMiniDasboardProfit($request)
    {
        $mini_dashboard =mini_dashboard::find($request->mini_dashboard_id);
        $this->amount = $request->amount;
        $this->user_id =auth()->user()->id;
        $this->type = 'MiniDashboard';
        $this->describtion = 'MiniDashboard #'.$request->mini_dashboard_id .' Paid ' . $this->amount . ' To Activate Affliate For '.$request->days.'days';
        $this->mini_dashboard_id = $request->mini_dashboard_id;
        $this->date = $request->date;
        $this->currency_id= $mini_dashboard->currency_id;
        $this->save();
    }


    public function setData($order)
    {
        $this->amount = $order->companyProfit;
        $this->user_id = $order->driver_id;
    }
}
