<?php

namespace App\Models\Dashboard;

use App\Http\Resources\MiniDashboardResource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\income;

class mini_dashboard extends Model
{
    public $resource_type;

    public function admins()
    {
        return $this->hasMany('App\Admin', 'mini_dashboard_id', 'id');
    }

    public function drivers()
    {
        return $this->hasMany('App\Driver', 'mini_dashboard_id', 'id');
    }

    public function resturants()
    {
        return $this->hasMany('App\Resturant', 'mini_dashboard_id', 'id');
    }

    public static function getAuthorizedOnly()
    {
        if (request()->dashboardId==0) {
            return self::all();
        } else {
            return self::where('id', request()->dashboardId)->get();
        }
    }

    public function orders()
    {
        return $this->hasManyThrough(
            'App\Order',
            'App\Driver',
            'mini_dashboard_id', // Foreign key on users table...
            'driver_id', // Foreign key on posts table...
            'id', // Local key on countries table...
            'user_id' // Local key on users table...
        );
    }

    public function onlineDrivers()
    {
        return $this->drivers()
                ->join('users', 'users.id', '=', 'drivers.user_id')
                ->select('users.name', 'drivers.lat', 'drivers.lng', 'users.id', 'drivers.busy', 'drivers.availability')
                ->whereNotNull('drivers.deviceToken')
                ->where('drivers.busy', 0)
                ->where('drivers.availability', 'on');
    }

    public function offlineDrivers()
    {
        return $this->drivers()
        ->join('users', 'users.id', '=', 'drivers.user_id')
        ->select('users.name', 'drivers.lat', 'drivers.lng', 'users.id', 'drivers.busy', 'drivers.availability')
        ->whereNotNull('drivers.deviceToken')
        ->where('drivers.busy', 1)
        ->where('drivers.availability', 'on');
    }

    public function getListPage()
    {
        $this->resource_type = 'listpage';
        return  MiniDashboardResource::collection($this->all(), 'listpage');
    }

    public function Create(Request $request)
    {
        $this->setData($request);
        $this->save();
    }

    public function Edit(Request $request)
    {
        $this->setData($request);
        $this->active = $request->active;
        $this->save();
    }

    private function setData(Request $request)
    {
        $this->name = $request->name;
        $this->monthly_cost = $request->monthly_cost;
        $this->earning_ratio = $request->earning_ratio;
        $this->number_of_drivers =$request->number_of_drivers;
        $this->days_left =$request->days_left;
    }

    public function reactivate($request)
    {
        (new income())->storeMiniDasboardProfit($request);
        $this->days_left +=$request->days;
        $this->save();
    }
}
