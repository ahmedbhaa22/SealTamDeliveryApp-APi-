<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as HttpClient;
use Response;
use DB;
use Validator;
use Hash;
use Config;
use App\Add;
use App\Http\ViewModel\ResultVM;

class AddController extends Controller
{
	private $_client;
    private $_result;
    public function __construct()
    {
       $this->_client=Client::find(2);
       $this->_result=new ResultVM();
    }


    public function get_add()
    {

		   $add = Add::orderBy('id','desc')->first();


	        $this->_result->IsSuccess = true;
             $this->_result->Data = ['add'=>$add];
             return Response::json($this->_result,200);


    }

     public function save_add(Add $add , Request $request)
    {
    	 $validation=Validator::make($request->all(),
         [  'name'=>'required|string',
            'image' => 'image',
            'status'=>'required|numeric|in:0,1',
         ]);
         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }


          if ($request->image) {
          	 if ($add->first()->image != 'default.jpg') {
                 Storage::disk('public_uploads')->delete('/adds/' . $add->first()->image);
                }

                  $file = request()->file('image');
                  $file->store('adds', ['disk' => 'public_uploads']);

                  $k_image = $file->hashName();
                  Config::set('k_image', $k_image);
                  $newAdd = 	Add::orderBy('id', 'desc')->update(['name'=>$request->name,'status'=>$request->status,'image'=>Config::get('k_image')]);


            }
            else{
                $newAdd = 	Add::orderBy('id', 'desc')->update(['name'=>$request->name,'status'=>$request->status]);

            }




   			 $this->_result->IsSuccess = true;
             $this->_result->Data = ['add'=>$newAdd];
             return Response::json($this->_result,200);

    	// $NewDriver=   User::where('id', $id)->update(['name'=>$request->name,'Status'=>$request->status]);
     }

}
