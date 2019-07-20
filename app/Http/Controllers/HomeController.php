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

 		 $this->_result->IsSuccess = true;
         $this->_result->Data = ['admins'=>$admins, 'drivers'=>$drivers,'resturants'=>$resturants];
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
