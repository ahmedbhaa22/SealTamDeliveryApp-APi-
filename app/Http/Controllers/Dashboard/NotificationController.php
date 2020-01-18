<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Dashboard\mini_dashboard;
use App\Http\Controllers\Shared\BaseController;
use Validator;
use App\Models\General\Notification;

class NotificationController extends BaseController
{
    public function getNotficationList(Request $request)
    {
        return $this->Response(true, Notification::getCurrentDashboardNotification());
    }

    public function sendToDashboard(Request $request)
    {
        (new Notification())->sendToDashboard($request);
        return $this->Response(true, null);
    }

    public function markAsRead($id)
    {
        Notification::find($id)->markAsRead();
        return $this->Response(true, null);
    }

    public function getNotReadCount(Request $request)
    {
        return $this->Response(true, Notification::getNotreadCount());
    }

    public function delete($id)
    {
        Notification::find($id)->delete();
        return $this->Response(true, null);
    }
}
