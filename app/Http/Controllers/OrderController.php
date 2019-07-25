<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Validator;
use Response;
use App\User;
use App\Order;
use App\Http\ViewModel\ResultVM;

class OrderController extends Controller
{

    private $_result;
    public function __construct()
    {
    
       $this->_result=new ResultVM();
    }
    

     public function change_order_status(Request $request) {

            $validation=Validator::make($request->all(),
             [

                'driver_id'     =>'required|numeric',
                'status'   =>'required|in:-2,-1,0,1,2,3,4,5',

             ]);

             if($validation->fails())
             {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason =  $validation->errors()->first();
                return Response::json($this->_result,200);
             }


              $update =  DB::table('orders')
                  ->where('driver_id', $request->driver_id)
                  ->update(['status' => $request->status]);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

         } // end change_order_status


          public function get_current_order($driver_id) {

			$currentOrder =  DB::table('orders')
                  ->where('driver_id', $driver_id)->whereIn('status', ['0','1', '2', '3'])
                  ->first();
                  

              $this->_result->IsSuccess = true;
              $this->_result->Data = $currentOrder;
             return Response::json($this->_result,200);



         } // end get_current_order
}
