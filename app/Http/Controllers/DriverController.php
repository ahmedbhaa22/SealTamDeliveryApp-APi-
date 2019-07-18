<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
         [  'name'=>'required|string|unique:users,name',
            'email'=>'required|string|unique:users,email',
            'password'=>'required|string|min:5',
            'telephone'     =>'sometimes|numeric|min:5',
            'image' => 'image|sometimes|nullable',
            'frId' => 'image|sometimes|nullable',
            'backId' => 'image|sometimes|nullable',
         ]);

       if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }



        if ($request->frId) {

                $file = request()->file('frId');
                $file->store('FrontID', ['disk' => 'public_uploads']);

                $frontId = $file->hashName();


        }
        if ($request->image) {

                        $file2 = request()->file('image');
                        $file2->store('driver_images', ['disk' => 'public_uploads']);

                        $image = $file2->hashName();


                }

        if ($request->backId) {

                $file3 = request()->file('backId');
                $file3->store('backDriverId', ['disk' => 'public_uploads']);

                $backId = $file3->hashName();


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
         [  'name'          =>'required|string|unique:users,name',
            'status'        =>'required|numeric',
            'telephone'     =>'sometimes|numeric|min:5',
            'image' => 'image|sometimes|nullable',
            'frId' => 'image|sometimes|nullable',
            'backId' => 'image|sometimes|nullable',
         ]);

         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }

         if ($request->image) {

                 Storage::disk('public_uploads')->delete('/driver_images/' . $driver->first()->image);

                  $file2 = request()->file('image');
                  $file2->store('driver_images', ['disk' => 'public_uploads']);

                  $r_image = $file2->hashName();
                  Config::set('r_image', $r_image);

            }//end of inner if

        if ($request->frId) {

                Storage::disk('public_uploads')->delete('/FrontID/' . $driver->first()->frontId);

                $file = request()->file('frId');
                $file->store('FrontID', ['disk' => 'public_uploads']);

                $r_frontId = $file->hashName();
                 Config::set('r_frontId', $r_frontId);

            }//end of inner if

        if ($request->backId) {

                Storage::disk('public_uploads')->delete('/backDriverId/' . $driver->first()->backId);

                $file3 = request()->file('backId');
                $file3->store('backDriverId', ['disk' => 'public_uploads']);

                $r_backId = $file3->hashName();
                Config::set('r_backId', $r_backId);

            }//end of inner if




       $NewDriver=   User::where('id', $id)->update(['name'=>$request->name,'Status'=>$request->status]);

       $DriverInfo =    Driver::where('user_id', $id)->update([
                
               'telephone'=>$request->telephone,
               'image' =>  Config::get('r_image'),
                'frontId' => Config::get('r_frontId'),
                'backId' =>Config::get('r_backId')

            ]);


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
                ->where('users.UserType','driver')->where('users.id',$id)
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





}