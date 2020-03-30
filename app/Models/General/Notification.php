<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Notification extends BaseModel
{
    public function sendToUser($request)
    {
        $this->setData($request);
        $this->user_id = $request->user_id;
        $this->save();
    }

    public function sendToDashboard($request)
    {
        $this->setData($request);
        $this->dashboard_id = request()->dashboardId==0 ? $request->minidashboardId :0;
        $this->save();
        if ($this->dashboard_id==0) {
            $minidash = \App\Models\Dashboard\mini_dashboard::find($request->dashboardId);
           
            $minidash->last_requested_receipt = now();
            $minidash->save();
        }
    }

    public static function getCurrentDashboardNotification()
    {
        return self::where('dashboard_id', request()->dashboardId)->orderBy('id', 'DESC')->get();
    }

    public function markAsRead()
    {
        $this->is_read =true;
        $this->save();
    }

    public static function getNotreadCount()
    {
        return self::where('dashboard_id', request()->dashboardId)->where('is_read', 0)->count();
    }

    public function setData($request)
    {
        $this->title = $request->title;
        $this->body = $request->body;
        $this->link = $request->link;
        $this->sender_id = auth()->user()->id;
    }
}
