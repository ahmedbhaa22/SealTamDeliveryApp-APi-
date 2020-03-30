<?php

namespace App\Http\Resources;

use App\User;
use App\Order;
use App\Driver;
use App\Resturant;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use DB;
use App\Http\Resources\driverStatusResource;

class HomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($request->dashboardId == 0) {
            return $this->superDashboardHome();
        } else {
            return $this->miniDasboardHome($request);
        }
    }

    public function superDashboardHome()
    {
        return  [


        'admins'=>User::where('UserType', 'admin')->count(),

        'drivers'=>User::where('UserType', 'driver')->count(),

        'resturants'=>User::where('UserType', 'resturant')->count(),

        "all_resturants"=>Resturant::with('user')->get(),

        "driversonline"=> (new Driver())->online()->get(),

        "driversbusy"=> (new Driver())->offline()->get(),

        'profitThisMonth'=>Order::where('status', '4')
                                 ->whereYear('created_at', Carbon::now()->year)
                                 ->whereMonth('created_at', Carbon::now()->month)
                                 ->where('resturant_id', '<>', 1454)
                                 ->sum('orders.companyProfit'),

        "profitoverall"=>Order::where('status', '4')
                                ->where('resturant_id', '<>', 1454)
                                ->sum('orders.companyProfit'),

        'income'=>Order::select(DB::raw("SUM(`companyProfit`) as income"), DB::raw("MONTH(`created_at`) as month"))
                        ->groupBy("month")->where('status', '4')
                        ->whereYear('created_at', Carbon::now()->year)
                        ->where('orders.resturant_id', '<>', '1454')
                        ->get(),

        'orderReportThisYear'=>Order::select('status', DB::raw("count(`status`) as count"))
                                    ->groupBy("status")
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->where('resturant_id', '<>', '1454')
                                    ->get(),

        'orderReportThismonth'=>Order::select('status', DB::raw("count(`status`) as count"))
                                        ->whereNotIn('resturant_id', [1454])
                                        ->groupBy("status")
                                        ->whereMonth('created_at', Carbon::now()->month)
                                        ->whereYear('created_at', Carbon::now()->year)
                                        ->get()
        ];
    }

    public function miniDasboardHome($request)
    {
        return  [

            'admins'=>$this->admins->count(),

            'drivers'=>$this->drivers->count(),

            'resturants'=>$this->resturants->count(),

            "all_resturants"=>$this->resturants()->with('user')->get(),

            "driversonline"=>$this->onlineDrivers()->get(),


            "driversbusy"=>$this->offlineDrivers()->get(),


            'profitThisMonth'=>$this->orders()->where('status', '4')
                                     ->whereYear('orders.created_at', Carbon::now()->year)
                                     ->whereMonth('orders.created_at', Carbon::now()->month)
                                     ->where('resturant_id', '<>', 1454)
                                     ->sum('orders.companyProfit'),

            "profitoverall"=>$this->orders()->where('status', '4')
                                    ->where('resturant_id', '<>', 1454)
                                    ->sum('orders.companyProfit'),

            'income'=>$this->orders()->select(DB::raw("SUM(`companyProfit`) as income"), DB::raw("MONTH(`orders`.`created_at`) as month"))
                            ->groupBy("month")->where('status', '4')
                            ->whereYear('orders.created_at', Carbon::now()->year)
                            ->where('orders.resturant_id', '<>', '1454')
                            ->get(),

            'orderReportThisYear'=>$this->orders()->select('status', DB::raw("count(`status`) as count"))
                                        ->groupBy("status")
                                        ->whereYear('orders.created_at', Carbon::now()->year)
                                        ->where('resturant_id', '<>', '1454')
                                        ->get(),

            'orderReportThismonth'=>$this->orders()->select('status', DB::raw("count(`status`) as count"))
                                            ->whereNotIn('resturant_id', [1454])
                                            ->groupBy("status")
                                            ->whereMonth('orders.created_at', Carbon::now()->month)
                                            ->whereYear('orders.created_at', Carbon::now()->year)
                                            ->get(),
            'mini_dashboard_days_count'=>$this->days_left,
            'canSendNotification'=>strtotime($this->last_requested_receipt) < strtotime('today'),

            ];
    }
}
