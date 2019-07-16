<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as HttpClient;
use App\Http\ViewModel\ResultVM;
use App\User;
use Response;
use Validator;
use Hash;
use Route;
use Auth;
use Storage;
class UsersController extends Controller
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
         [ 'username'=>'required|string|unique:users,UserName',
            'email'=>'required|string|unique:users,Email',
            'password'=>'required|string|min:5',
            'type'=>'required|string',
         ]);
         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }

            $Newuser= new User();
            $Newuser->username=$request->username;
            $Newuser->email=$request->email;
            $Newuser->UserType=$request->type;
            $Newuser->Status=true;
            $Newuser->password=Hash::make($request->password);
            $Newuser->save();


             $this->_result->IsSuccess = true;
             $this->_result->Data = ['user'=>$Newuser];
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
            $this->_result->FaildReason = "wrong email ssssor password";
            return Response::json($this->_result,200);
        }
    }

    public function edit_pass(Request $request){
        $r=$request;
        $validation=Validator::make($request->all(),
       [
           'oldpassword'=> 'required|string',
           'password'=> 'required|string|min:5',
           'email'=>'required|exists:Users,email'
       ]);
       if($validation->fails())
        {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);
        }
       if(Auth::attempt(['email'=>$request->email,'password'=>$request->oldpassword]))
               {
                $form_params= [
                    'grant_type' => 'password',
                    'client_id' => $this->_client->id,
                    'client_secret' => $this->_client->secret,
                    'username' => $r->email,
                    'password'=>$r->oldpassword,
                    'scope' => '*',
                ];
                $r->request->add($form_params);

                $pro= Request::create('oauth/token','POST');
                $a=Route::dispatch($pro);

                $access= json_decode((string) $a->getContent() ,true);
                $refresh_token=$access['refresh_token'];
                                   $edit_pass = Auth::user();
                   $edit_pass->password=bcrypt($request->password);
                   $request->merge(['refresh_token' => $refresh_token]);
                   if($this->logut_from_all_devices($request))
                   {
                       $edit_pass->save();
                       $this->_result->IsSuccess = true;
                       return Response::json($this->_result,200);

                   }
                   else
                   {
                    $this->_result->IsSuccess = false;
                    $this->_result->FaildReason =  "wrong Refresh Token";
                    return Response::json($this->_result,200);
                   }
               }
               else
               {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason =  "Wrong Password";
                return Response::json($this->_result,200);
               }
           }


            public function get_all_admin(){
            $Admins= User::where('UserType','Admin')->get();
            $this->_result->IsSuccess = true;
            $this->_result->Data = $Admins;
            return Response::json($this->_result,200);
                    }
           public function get_admin($id){
            $Admin= User::where('UserType','Admin')->where('id',$id)->first();
            $this->_result->IsSuccess = true;
            $this->_result->Data = $Admin;
            return Response::json($this->_result,200);
                       }

//====================================================
   public function logut_from_all_devices(Request $r)
   {


             $form_params= [
               'grant_type' => 'refresh_token',
               'refresh_token' => $r->refresh_token,
                'client_id' => $this->_client->id,
                'client_secret' => $this->_client->secret,
                'scope' => '',
           ];
           $r->request->add($form_params);
           $pro= Request::create('oauth/token','POST');
           $a=Route::dispatch($pro);

           $access= json_decode((string) $a->getContent() ,true);
           if($a->status()==200)
           {
               return true;
           }
           else{
               return false;
           }




   }
}
