<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as HttpClient;
use Response;
use DB;
use Validator;
use Hash;
use App\User;
use App\Http\ViewModel\ResultVM;
use Carbon\Carbon;

class HomeController extends Controller
{
    private $_client;
    private $_result;
    public function __construct()
    {
       $this->_client=Client::find(2);
       $this->_result=new ResultVM();
    }

	public function get_items_count()
	 {

	 	 $admins = DB::table('users')->where('UserType','admin')->count();
	 	 $drivers = DB::table('users')->where('UserType','driver')->count();
	 	 $resturants = DB::table('users')->where('UserType','resturant')->count();


        // $start = Carbon::now()->startOfMonth();
        // $end   = Carbon::now();
        // $totalCost = DB::table('orders')->where('status','4')->whereBetween('created_at',[$start,$end])->sum('orders.deliveryCost');

         $totalCost = DB::table('orders')->where('status','4')->whereYear('created_at',  Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->where('resturant_id','<>',1454)->sum('orders.companyProfit');
        
         $totalCostall = DB::table('orders')->where('status','4')->where('resturant_id','<>',1454)->sum('orders.companyProfit');
        
         $incomeReport = DB::table('orders')->select(DB::raw("SUM(`companyProfit`) as income"),DB::raw("MONTH(`created_at`) as month"))->groupBy("month")->where('status','4')->whereYear('created_at',  Carbon::now()->year)->where('orders.resturant_id','<>','1454')->get();
        
         $orderReportThisYear = DB::table('orders')->select('status',DB::raw("count(`status`) as count"))->groupBy("status")->whereYear('created_at',  Carbon::now()->year)->where('resturant_id','<>','1454')->get();
           $orderReportThismonth = DB::table('orders')->select('status',DB::raw("count(`status`) as count"))->whereNotIn('resturant_id', [1454])->groupBy("status")->whereMonth('created_at',  Carbon::now()->month)->whereYear('created_at',  Carbon::now()->year)->get();
        
 		 $this->_result->IsSuccess = true;
         $this->_result->Data = ['admins'=>$admins, 'drivers'=>$drivers,'resturants'=>$resturants,'profitThisMonth'=>$totalCost,"profitoverall"=>$totalCostall,'income'=>$incomeReport,'orderReportThisYear'=>$orderReportThisYear,'orderReportThismonth'=>$orderReportThismonth];
         return Response::json($this->_result,200);

	 }

	  public function get_all_resturants()
            {

	            $all_resturants = DB::table('users')
        		->join('resturants', 'users.id', '=', 'resturants.user_id')
        		->where('users.UserType','resturant')
        	    ->orderBy('users.id','ASC')
                ->get();

	            $this->_result->IsSuccess = true;
	            $this->_result->Data = $all_resturants;
	            return Response::json($this->_result,200);
             }





}
