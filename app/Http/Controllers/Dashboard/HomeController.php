<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\BaseController;
use App\Http\Resources\HomeResource;
use App\Models\Dashboard\mini_dashboard;

class HomeController extends BaseController
{
    public function index()
    {
        $mini_dashboard  = mini_dashboard::find(request()->dashboardId);
        return $this->Response(true, new HomeResource($mini_dashboard));
    }
}
