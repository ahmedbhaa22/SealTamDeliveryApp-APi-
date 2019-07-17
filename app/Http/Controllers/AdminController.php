<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as HttpClient;
use Response;
use DB;
use Validator;
use Hash;
use App\User;
use App\Admin;
use App\Http\ViewModel\ResultVM;
class AdminController extends Controller
{
	private $_client;
    private $_result;
    public function __construct()
    {
       $this->_client=Client::find(2);
       $this->_result=new ResultVM();
    }


		public function Create(Request $request)
    {

         $validation=Validator::make($request->all(),
         [  'name'=>'required|string|unique:users,name',
            'email'=>'required|string|unique:users,email',
            'password'=>'required|string|min:5',
         ]);
         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }

            $NewAdmin= new User();
            $NewAdmin->name=$request->name;
            $NewAdmin->email=$request->email;
            $NewAdmin->UserType='admin';
            $NewAdmin->Status=true;
            $NewAdmin->password=Hash::make($request->password);
            $NewAdmin->save();


             $this->_result->IsSuccess = true;
             $this->_result->Data = ['user'=>$NewAdmin];
             return Response::json($this->_result,200);

        }
 
            public function get_all_admin()
            {

	            $Admins= User::where('UserType','admin')->get();
	            $this->_result->IsSuccess = true;
	            $this->_result->Data = $Admins;
	            return Response::json($this->_result,200);
             }

           public function get_admin($id)
           {
	            $Admin= User::where('UserType','admin')->where('id',$id)->first();
	            $this->_result->IsSuccess = true;
	            $this->_result->Data = $Admin;
	            return Response::json($this->_result,200);
             }


	public function add_role(Request $request) 
 	    {
	 	    	$validation=Validator::make($request->all(),
	         [  
	         	'admin_Id'=>'required|numeric',
	         	'type'=>'required|in:manager,supervisor,chief',
	           
	         ]);

	         if($validation->fails())
	         {
	            $this->_result->IsSuccess = false;
	            $this->_result->FaildReason =  $validation->errors()->first();
	            return Response::json($this->_result,200);


	         }

	        $NewAdmin= new Admin();
            $NewAdmin->AdminType=$request->type;
            $NewAdmin->user_id=$request->admin_Id;
            $NewAdmin->save();


             $this->_result->IsSuccess = true;
             $this->_result->Data = ['user'=>$NewAdmin];
             return Response::json($this->_result,200);
 	    	

 	    }

 	    public function edit_role(Request $request) 
 	    {
	 	    	$validation=Validator::make($request->all(),
	         [  
	         	'admin_Id'=>'required|numeric',
	         	'type'=>'required|in:manager,supervisor,chief',
	           
	         ]);

	         if($validation->fails())
	         {
	            $this->_result->IsSuccess = false;
	            $this->_result->FaildReason =  $validation->errors()->first();
	            return Response::json($this->_result,200);


	         }

	
   			 $update =  DB::table('admins')
                  ->where('user_id', $request->admin_Id)
                  ->update(['AdminType' => $request->type]);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

 	    	

 	    }

	public function get_role($admin_id)
		{
			$update = DB::table('admins')->where('user_id', $admin_id)->select('AdminType')->get();
              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;

             return Response::json($this->_result,200);
		}


    public function unactive_admin($admin_id)
    	{

             $update =  DB::table('users')
                  ->where('UserType','admin')->where('id', $admin_id)
                  ->update(['Status' => false]);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

          }  
          
          public function active_admin($admin_id)
          {

            $update =  DB::table('users')
                  ->where('UserType','admin')->where('id', $admin_id)
                  ->update(['Status' => true]);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);
          } 
}

