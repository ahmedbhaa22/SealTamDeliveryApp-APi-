<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Validator;
use Hash;
use Response;
use Config;
use DB;
use App\User;
use App\Driver;
use Auth;
use Route;
use App\Http\ViewModel\ResultVM;


class DriverController extends Controller
{

    private $_client;
    private $_result;
    public function __construct()
    {
       $this->_client=Client::find(2);
       $this->_result=new ResultVM();
    }


    public function createDriver(Request $request)
    {



      $validation=Validator::make($request->all(),
         [
            'name'=>'required|string',
            'email'=>'required|string|unique:users,email',
            'password'=>'required|string|min:5',
            'telephone'     =>'required|numeric|min:5',

            'image' => 'image|required',
            'frId' => 'image|required',
            'backId' => 'image|required',
         ]);

       if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }



        if ($request->frId) {

                $file = request()->file('frId');
                $frontId =  $file->store('FrontID');




        }
        if ($request->image) {

                        $file2 = request()->file('image');
                        $image = $file2->store('driver_images');




                }

        if ($request->backId) {

                $file3 = request()->file('backId');
                $backId = $file3->store('backDriverId');




        }




            $NewDriver= new User();
            $NewDriver->name=$request->name;
            $NewDriver->email=$request->email;
            $NewDriver->UserType='driver';
            $NewDriver->Status=true;
            $NewDriver->password=Hash::make($request->password);
            $NewDriver->save();


            $DriverInfo = new Driver();
            $DriverInfo->user_id   = $NewDriver->id;
            $DriverInfo->telephone = $request->telephone;
            $DriverInfo->identity = time();
            $DriverInfo->image     = $image ;
            $DriverInfo->backId    = $backId;
            $DriverInfo->frontId    = $frontId;
            $DriverInfo->save();



             $this->_result->IsSuccess = true;
             $this->_result->Data = ['driver'=>$NewDriver, 'info'=>$DriverInfo];
             return Response::json($this->_result,200);

    }//end of create




    public function Edit_Driver(Request $request, Driver $driver, $id)
    {

         $validation=Validator::make($request->all(),
         [  'name'          =>'required|string',
            'status'        =>'required|numeric',
            'telephone'     =>'sometimes|numeric|min:5',
            'identity'     =>'required|numeric|min:14',
            'canReceiveOrder'        =>'required|numeric',
         ]);

         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);
         }

         $newDriverData  =[
            'telephone'=>$request->telephone,
            'identity'=>$request->identity,
            'canReceiveOrder'=>$request->canReceiveOrder,
         ];

         if ($request->image) {
                  Storage::delete($driver->first()->image);
                  $file2 = request()->file('image');
                  $r_image =  $file2->store('driver_images');
                  Config::set('r_image', $r_image);
                  $newDriverData['image'] =   $r_image ;
            }

        if ($request->frId) {

                Storage::delete($driver->first()->frontId);
                $file = request()->file('frId');
                $r_frontId =  $file->store('FrontID');
                 Config::set('r_frontId', $r_frontId);
                 $newDriverData['frontId'] =   $r_frontId ;
            }//end of inner if

        if ($request->backId) {
                Storage::delete($driver->first()->backId);
                $file3 = request()->file('backId');
                $r_backId =  $file3->store('backDriverId');
                 Config::set('r_backId', $r_backId);
                 $newDriverData['backId'] =   $r_backId ;
            }//end of inner if

            $NewDriver=  User::where('id', $id)->update(['name'=>$request->name,'Status'=>$request->status]);
            $DriverInfo =    Driver::where('user_id', $id)->update($newDriverData);
             $this->_result->IsSuccess = true;
             $this->_result->Data = ['driver'=>$NewDriver, 'info'=>$DriverInfo];
             return Response::json($this->_result,200);

        }




      public function get_all_drivers()
            {

                $all_resturants = DB::table('users')
                ->join('drivers', 'users.id', '=', 'drivers.user_id')
                ->where('users.UserType','driver')
                ->orderBy('users.id','ASC')
                ->get();

                $this->_result->IsSuccess = true;
                $this->_result->Data = $all_resturants;
                return Response::json($this->_result,200);
             }

           public function get_driver($id)
           {
            $resturant = DB::table('users')
                ->join('drivers','users.id', '=', 'drivers.user_id')
                ->where('users.UserType','driver')->where('drivers.id',$id)
                ->get();

                $this->_result->IsSuccess = true;
                $this->_result->Data = $resturant;
                return Response::json($this->_result,200);
             }


           public function destroy(Driver $driver, $id)
            {
               // dd($driver->first()->frontId);

                Storage::disk('public_uploads')->delete('/driver_images/' . $driver->first()->image);
                Storage::disk('public_uploads')->delete('/FrontID/' . $driver->first()->frontId);
                Storage::disk('public_uploads')->delete('/backDriverId/' . $driver->first()->backId);

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

          $user=User::where('email',$r->email)->join('drivers','users.id', '=', 'drivers.user_id')->first();




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

                 if($user->UserType != 'driver'){
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
         } // end login



     public function make_online($id)
        {

             $update =  DB::table('drivers')
                  ->where('user_id', $id)
                  ->update(['availability' => 'on']);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

          }// end make_online

    public function make_offline($id)
        {

             $update =  DB::table('drivers')
                  ->where('user_id', $id)
                  ->update(['availability' => 'off']);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

          }// end make_offline

     public function make_ontrip($id)
        {

             $update =  DB::table('drivers')
                  ->where('user_id', $id)
                  ->update(['availability' => 'ontrip']);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

          }// end make_ontrip


       public function add_location (Request $request)
         {

         $validation=Validator::make($request->all(),
             [
                'driver_id'     =>'required|numeric',
                'lng'           =>'required|string',
                'lat'           =>'required|string',

             ]);

             if($validation->fails())
             {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason =  $validation->errors()->first();
                return Response::json($this->_result,200);


             }


             $update =  DB::table('drivers')
                  ->where('user_id', $request->driver_id)
                  ->update(['lat' => $request->lat,'lng' => $request->lng]);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

         }// end addLocation


          public function add_deviceToken (Request $request)
         {

         $validation=Validator::make($request->all(),
             [

                'driver_id'     =>'required|numeric',
                'deviceToken'   =>'required|string',

             ]);

             if($validation->fails())
             {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason =  $validation->errors()->first();
                return Response::json($this->_result,200);
             }


             $update =  DB::table('drivers')
                  ->where('user_id', $request->driver_id)
                  ->update(['deviceToken' => $request->deviceToken]);

              $this->_result->IsSuccess = true;
              $this->_result->Data = $update;
             return Response::json($this->_result,200);

         }// end add_deviceToken





}
