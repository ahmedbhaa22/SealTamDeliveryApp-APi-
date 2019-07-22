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
use Auth;
use Route;
use App\User;
use App\Resturant;
use App\Http\ViewModel\ResultVM;

class ResturantsController extends Controller
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
         [  'name'			=>'required|string',
            'email'			=>'required|string|unique:users,email',
            'password'		=>'required|string|min:5',
            'lng'           =>'sometimes|nullable',
            'lat'           =>'sometimes|nullable',
            'location'      =>'sometimes|nullable',
            'telephone'     =>'sometimes|numeric|min:5',
         ]);
         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }

            $NewResturant= new User();
            $NewResturant->name=$request->name;
            $NewResturant->email=$request->email;
            $NewResturant->UserType='resturant';
            $NewResturant->Status=true;
            $NewResturant->password=Hash::make($request->password);
            $NewResturant->save();


            $ResturantInfo= new Resturant();
            $ResturantInfo->user_id   = $NewResturant->id;
            $ResturantInfo->lat 	  = $request->lat;
            $ResturantInfo->lng       = $request->lng;
            $ResturantInfo->telephone = $request->telephone;
            $ResturantInfo->location  = $request->location;
            $ResturantInfo->save();



             $this->_result->IsSuccess = true;
             $this->_result->Data = ['resturant'=>$NewResturant, 'info'=>$ResturantInfo];
             return Response::json($this->_result,200);

        }



  public function Edit_Resturant(Request $request, $id)
    {

         $validation=Validator::make($request->all(),
         [  'name'			=>'required|string',
            'status'        =>'required|numeric',
            'lng'           =>'sometimes|nullable',
            'lat'           =>'sometimes|nullable',
            'location'      =>'sometimes|nullable',
            'telephone'     =>'sometimes|numeric|min:5',
         ]);
         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }

       $NewResturant=   User::where('id', $id)->update(['name'=>$request->name,'Status'=>$request->status]);

       $ResturantInfo =    Resturant::where('user_id', $id)->update([
            	'lng'=>$request->lng,
            	'lat'=>$request->lat,
            	'location'=>$request->location,
            	'telephone'=>$request->telephone,
            ]);


             $this->_result->IsSuccess = true;
             $this->_result->Data = ['resturant'=>$NewResturant, 'info'=>$ResturantInfo];
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

           public function get_resturant($id)
           {
           	$resturant = DB::table('users')
        		->join('resturants','users.id', '=', 'resturants.user_id')
        		->where('users.UserType','resturant')->where('resturants.id',$id)
                ->first();

	            $this->_result->IsSuccess = true;
	            $this->_result->Data = $resturant;
	            return Response::json($this->_result,200);
             }


		   public function destroy($id)
		    {
		        $resturant = User::find($id);
		        $resturant->delete();
		        $this->_result->IsSuccess = true;
	            $this->_result->Data = $resturant;
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

          $user=User::where('email',$r->email)->join('resturants','users.id', '=', 'resturants.user_id')->first();




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

                 if($user->UserType != 'resturant'){
                     $this->_result->IsSuccess = false;
                     $this->_result->FaildReason = "wrong email or password";
                     return Response::json($this->_result,200);

                 } else {
                 $this->_result->IsSuccess = true;
                 $this->_result->Data = ['access_token'=>$access['access_token'],'refresh_token'=>$access['refresh_token'],'user'=>$user];
                 return Response::json($this->_result,200);
                 }
             }
             else
             {
                 $this->_result->IsSuccess = false;
                 $this->_result->FaildReason = "wrong email or password";
                 return Response::json($this->_result,200);
             }
         }


}
