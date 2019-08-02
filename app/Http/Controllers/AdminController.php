<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
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
use Auth;
use Route;
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
         [  'name'=>'required|string',
            'email'=>'required|email|unique:users,email',
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


            $NewAdminDetails= new Admin();

                $NewAdminDetails->AdminType='supervisor';
                $NewAdminDetails->user_id=$NewAdmin->id;
                $NewAdminDetails->hidden=false;
                $NewAdminDetails->save();


             $this->_result->IsSuccess = true;
             $this->_result->Data = ['user'=>$NewAdmin];
             return Response::json($this->_result,200);

        }

            public function get_all_admin()
            {

	         //   $Admins= User::where('UserType','admin')->get();
                  $Admins=  DB::table('users')
                  ->join('admins','users.id', '=', 'admins.user_id')
                  ->where('users.UserType', 'admin')
                  ->where('admins.hidden', false)
                  ->select('users.id','users.name','users.email','users.Status')
                  ->get();

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
       
      
                $NewAdmin =  DB::table('admins')
                ->where('user_id', $request->admin_Id)
                ->update(['AdminType' => $request->type]);
             
           



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
            if(count($update) >0 ){
             return Response::json($this->_result,200);
            }
            else{
                $this->_result->IsSuccess = false;
                $this->_result->FailedReason = 'not-found';
                return Response::json($this->_result,200);
            }
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

    public function login(Request $r)
    {
       $validation=Validator::make($r->all(),
        [

            'email'=>'required',
            'password'=>'required|string',
        ]);
        if($validation->fails())
        {
          $this->_result->IsSuccess = false;
         $this->_result->FaildReason =  $validation->errors()->first();
         return Response::json($this->_result,200);


        }

          $user=User::where('email',$r->email)->first();
             if($user ==null)
             {
                 $this->_result->IsSuccess = false;
                 $this->_result->FaildReason = "wrong email or password";
                 return Response::json($this->_result,200);

             }

             if($user->Status==false)
             {
                 $this->_result->IsSuccess = false;
                 $this->_result->FaildReason = "user not active";
                 return Response::json($this->_result,200);
             }


             if(Auth::attempt(['email'=>$r->email,'password'=>$r->password]))
             {

                  $form_params= [
                     'grant_type' => 'password',
                     'client_id' => $this->_client->id,
                     'client_secret' => $this->_client->secret,
                     'username' => $r->email,
                     'password' => $r->password,
                     'scope' => '*',
                 ];
                 $r->request->add($form_params);
                 $pro= Request::create('oauth/token','POST');
                 $a=Route::dispatch($pro);
                 $access= json_decode((string) $a->getContent() ,true);
                 $this->_result->IsSuccess = true;
                 $this->_result->Data = ['access_token'=>$access['access_token'],'refresh_token'=>$access['refresh_token'],'user'=>$user];
                 return Response::json($this->_result,200);
             }
             else
             {
                 $this->_result->IsSuccess = false;
                 $this->_result->FaildReason = "wrong email or password";
                 return Response::json($this->_result,200);
             }
         }
}

