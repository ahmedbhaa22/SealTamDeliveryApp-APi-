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
use App\Http\Resources\DriverResource;
use Validator;
use Hash;
use Response;
use Config;
use DB;
use App\User;
use App\Driver;
use Auth;
use Carbon\Carbon;
use App\Models\General\category;
use Route;
use App\Http\ViewModel\ResultVM;
use  App\Events\drivers_status;
use App\Models\Dashboard\mini_dashboard;

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
        $validation=Validator::make(
            $request->all(),
            [
            'name'=>'required|string',
            'email'=>'required|string|unique:users,email',
            'password'=>'required|string|min:5',
            'telephone'     =>'required|numeric|min:5',
            'identity' => 'required|numeric|min:14|unique:drivers,identity',
            'image' => 'image|required',
            'frId' => 'image|required',
            'backId' => 'image|required',
            'category' => 'required|exists:categories,id',
         ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }

        $mini_dashboard = $request->dashboardId;
        if ($mini_dashboard != 0) {
            $mini_dashboard = mini_dashboard::find($mini_dashboard);
            if (count($mini_dashboard->drivers)  >= $mini_dashboard->number_of_drivers) {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason = trans('messages.Globale.maxNumberExeeded');
                return Response::json($this->_result, 200);
            }
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
        $DriverInfo->identity =$request->identity;
        $DriverInfo->category_id    = $request->category;
        $DriverInfo->mini_dashboard_id    = $request->dashboardId==0?$request->mini_dashboard : $request->dashboardId ;

        $DriverInfo->image     = $image ;
        $DriverInfo->backId    = $backId;
        $DriverInfo->frontId    = $frontId;
        $DriverInfo->save();



        $this->_result->IsSuccess = true;
        $this->_result->Data = ['driver'=>$NewDriver, 'info'=>$DriverInfo];
        return Response::json($this->_result, 200);
    }//end of create




    public function Edit_Driver(Request $request, Driver $driver, $id)
    {
        $validation=Validator::make(
            $request->all(),
            [  'name'          =>'required|string',
            'status'        =>'required|numeric',
            'telephone'     =>'sometimes|numeric|min:5',
            'identity'     =>'required|numeric|unique:drivers,identity,'.$id.',user_id',
            'canReceiveOrder'        =>'required|numeric',
            'category' => 'required|exists:categories,id',
         ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }

        $newDriverData  =[
            'telephone'=>$request->telephone,
            'identity'=>$request->identity,
            'canReceiveOrder'=>$request->canReceiveOrder,
            'category_id'    => $request->category,
            'mini_dashboard_id'  =>$request->dashboardId==0?$request->mini_dashboard : $request->dashboardId
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
        return Response::json($this->_result, 200);
    }




    public function get_all_drivers()
    {
        $all_drivers = DB::table('drivers')
        ->join('users', 'users.id', '=', 'drivers.user_id')
        ->join('categories', 'categories.id', '=', 'drivers.category_id')
        ->leftjoin('mini_dashboards', 'mini_dashboards.id', '=', 'drivers.mini_dashboard_id')
        ->select('users.name', 'mini_dashboards.name as mini_dashboard', 'users.email', 'users.Status', 'drivers.telephone', 'drivers.CurrentBalance', 'users.id', 'users.rate', 'categories.arabicname', 'categories.englishname')
        ->where('users.UserType', 'driver');
        if (request()->dashboardId!=0) {
            $all_drivers= $all_drivers->where('drivers.mini_dashboard_id', request()->dashboardId);
        }
        $all_drivers= $all_drivers
                        ->orderBy('users.id', 'ASC')
                        ->get();


        $this->_result->IsSuccess = true;
        $this->_result->Data = $all_drivers;
        return Response::json($this->_result, 200);
    }

    public function get_driver($id)
    {
        $Driver = DB::table('users')
                ->join('drivers', 'users.id', '=', 'drivers.user_id')
                ->where('users.UserType', 'driver')->where('drivers.user_id', $id)
                ->first();
        $response = [
            'driver'=>$Driver,
            'categories'=>category::getdriverCategories(),
            'mini_dashboards'=>mini_dashboard::getAuthorizedOnly()
        ];
        $this->_result->IsSuccess = true;
        $this->_result->Data = $response;
        return Response::json($this->_result, 200);
    }

    public function getCreatePage()
    {
        $response = [
            'categories'=>category::getdriverCategories(),
            'mini_dashboards'=>mini_dashboard::getAuthorizedOnly()
        ];
        $this->_result->IsSuccess = true;
        $this->_result->Data = $response;
        return Response::json($this->_result, 200);
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
        return Response::json($this->_result, 200);
    }



    public function login(Request $r)
    {
        $validation=Validator::make(
            $r->all(),
            [

            'email'=>'required',
            'password'=>'required|string',
        ]
        );
        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }

        $user=User::where('email', $r->email)->join('drivers', 'users.id', '=', 'drivers.user_id')->first();




        if ($user ==null) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason = "wrong email or password";
            return Response::json($this->_result, 200);
        }

        if ($user->Status==false) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason = "user not active";
            return Response::json($this->_result, 200);
        }


        if (Auth::attempt(['email'=>$r->email,'password'=>$r->password])) {
            $form_params= [
                     'grant_type' => 'password',
                     'client_id' => $this->_client->id,
                     'client_secret' => $this->_client->secret,
                     'username' => $r->email,
                     'password' => $r->password,
                     'scope' => '*',
                 ];
            $r->request->add($form_params);
            $pro= Request::create('oauth/token', 'POST');
            $a=Route::dispatch($pro);
            $access= json_decode((string) $a->getContent(), true);

            if ($user->UserType != 'driver') {
                $this->_result->IsSuccess = false;
                $this->_result->FaildReason = "wrong email or password";
                return Response::json($this->_result, 200);
            } else {
                $this->_result->IsSuccess = true;
                $this->_result->Data = [
                    'access_token'=>$access['access_token'],
                    'refresh_token'=>$access['refresh_token'],
                    'user'=> new DriverResource($user)
             ];
                return Response::json($this->_result, 200);
            }
        } else {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason = "wrong email or password";
            return Response::json($this->_result, 200);
        }
    } // end login



    public function make_online($id)
    {
        $update =  DB::table('drivers')
                  ->where('user_id', $id)
                  ->update(['availability' => 'on']);

        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    }// end make_online

    public function make_offline($id)
    {
        $update =  DB::table('drivers')
                  ->where('user_id', $id)
                  ->update(['availability' => 'off']);

        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    }// end make_offline

    public function make_ontrip($id)
    {
        $update =  DB::table('drivers')
                  ->where('user_id', $id)
                  ->update(['availability' => 'ontrip']);

        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    }// end make_ontrip


    public function add_location(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [
                'driver_id'     =>'required|numeric',
                'lng'           =>'required|string',
                'lat'           =>'required|string',

             ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }


        $update =  DB::table('drivers')
                  ->where('user_id', $request->driver_id)
                  ->update(['lat' => $request->lat,'lng' => $request->lng,'updated_at'=> Carbon::now()]);

        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    }// end addLocation


    public function add_deviceToken(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [

                'driver_id'     =>'required|numeric',
                'deviceToken'   =>'string',

             ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }


        $update =  DB::table('drivers')
                  ->where('user_id', $request->driver_id)
                  ->update(['deviceToken' => $request->deviceToken]);
        if ($update > 0 &&($request->deviceToken==''||$request->deviceToken==null)) {
            $update2 =  DB::table('drivers')
            ->where('user_id', $request->driver_id)
            ->update(['availability' => 'of']);

            if ($update2 > 0) {
                event(new drivers_status($request->driver_id));
            }
        }
        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    }// end add_deviceToken

    public function change_availability(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [

                'driver_id'     =>'required|numeric',
                'status'   =>'required|in:on,off,ontrip',

             ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }


        $update =  DB::table('drivers')
                  ->where('user_id', $request->driver_id)
                  ->update(['availability' => $request->status]);

        if ($update > 0) {
            event(new drivers_status($request->driver_id));
        }


        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    } // end change_availability


    public function reset_balance($driver_id)
    {
        $update =  DB::table('drivers')
                  ->where('user_id', $driver_id)
                  ->update(['CurrentBalance' => '0','canReceiveOrder' => '1']);
        $this->_result->IsSuccess = true;
        $this->_result->Data = $update;
        return Response::json($this->_result, 200);
    }//end reset_balance
    public function change_driver_password(Request $request)
    {
        $validation=Validator::make(
            $request->all(),
            [
            'driver_id'=>'required|numeric',
            'oldpassword'=>'required|string|min:5',
            'password'=>'required|min:5|different:oldpassword',
            'confirm-password' => 'required_with:password|same:password|min:5',
         //   'password' => 'nullable|required_with:password_confirmation|string|confirmed',

           ]
        );

        if ($validation->fails()) {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result, 200);
        }

        $user=User::where('id', $request->driver_id)->first();

        if (Hash::check($request->oldpassword, $user->password)) {
            $update =  DB::table('users')
                  ->where('id', $request->driver_id)
                  ->update(['password' => Hash::make($request->password) ]);

            $this->_result->IsSuccess = true;
            $this->_result->Data = $update;
            return Response::json($this->_result, 200);
        } else {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason = "wrong Old password";
            return Response::json($this->_result, 200);
        }
    }// end change_driver_password
    public function get_app_version()
    {
        $setting  = DB::table('settings')
                ->select('id', 'key', 'value')
                ->first();

        $this->_result->IsSuccess = true;
        $this->_result->Data = $setting;
        return Response::json($this->_result, 200);
    }// end get app version


    //  public function change_busy_status(Request $request) {

    //     $validation=Validator::make($request->all(),
    //      [

    //         'driver_id'     =>'required|numeric',
    //         'busy'   =>'required|numeric|in:1,0',

    //      ]);

    //      if($validation->fails())
    //      {
    //         $this->_result->IsSuccess = false;
    //         $this->_result->FaildReason =  $validation->errors()->first();
    //         return Response::json($this->_result,200);
    //      }


    //       $update =  DB::table('drivers')
    //           ->where('user_id', $request->driver_id)
    //           ->update(['busy' => $request->busy]);

    //       $this->_result->IsSuccess = true;
    //       $this->_result->Data = $update;
    //      return Response::json($this->_result,200);

    //  } // end change_busy_status


    public function get_driver_data($driver_id)
    {
        $driverData =  DB::table('drivers')
                  ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
                  ->select('users.name', 'users.email', 'users.rate', 'users.Status', 'drivers.telephone', 'drivers.image', 'drivers.CurrentBalance', 'drivers.canReceiveOrder', 'drivers.busy', 'drivers.availability', 'drivers.user_id as Driver_Id')
                  ->where('drivers.user_id', $driver_id)
                  ->get();


        if (count($driverData) > 0) {
            $this->_result->IsSuccess = true;
            $this->_result->Data =[$driverData->first()];
            return Response::json($this->_result, 200);
        } else {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason = "No Data For This ID";
            return Response::json($this->_result, 200);
        }
    } // end get_driver_data

    public function driver_status_page()
    {
        $driveronline = DB::table('users')
        ->join('drivers', 'users.id', '=', 'drivers.user_id')
        ->select('users.name', 'drivers.lat', 'drivers.lng', 'users.id', 'drivers.busy', 'drivers.availability')
        ->where('users.UserType', 'driver')->whereNotNull('drivers.deviceToken')->where('drivers.busy', 0)->where('drivers.availability', 'on')
        ->get();

        $driverobusy = DB::table('users')
        ->join('drivers', 'users.id', '=', 'drivers.user_id')
        ->select('users.name', 'drivers.lat', 'drivers.lng', 'users.id', 'drivers.busy', 'drivers.availability')
        ->where('users.UserType', 'driver')->whereNotNull('drivers.deviceToken')->where('drivers.busy', 1)->where('drivers.availability', 'on')
        ->get();

        $this->_result->IsSuccess = true;
        $this->_result->Data =['driverobusy'=>$driverobusy,'driveronline'=>$driveronline];
        return Response::json($this->_result, 200);
    }
}
