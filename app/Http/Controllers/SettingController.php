<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Setting;
use App\Http\ViewModel\ResultVM;
use Response;
use Validator;

class SettingController extends Controller
{

    private $_result;
    public function __construct()
    {
       $this->_result=new ResultVM();
    }

    public function add_setting(Request $request)
    {

         $validation=Validator::make($request->all(),
         [  'key'=>'required|string',
            'value'=>'required|string',
            
         ]);
         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);


         }

            $NewSetting= new Setting();
            $NewSetting->key=$request->key;
            $NewSetting->value=$request->value;
            $NewSetting->save();


             $this->_result->IsSuccess = true;
             $this->_result->Data = ['setting'=>$NewSetting];
             return Response::json($this->_result,200);

        }


 public function edit_setting(Request $request)
    {

         $validation=Validator::make($request->all(),
         [ 
            'setting_id'=>'required|numeric',
            'key'=>'required|string',
            'value'=>'required|string',
         
         ]);

         if($validation->fails())
         {
            $this->_result->IsSuccess = false;
            $this->_result->FaildReason =  $validation->errors()->first();
            return Response::json($this->_result,200);
         }


            $updateSetting=  Setting::where('id', $request->setting_id)->update(['key'=>$request->key,'value'=>$request->value]);
             $this->_result->IsSuccess = true;
             $this->_result->Data = ['setting'=>$updateSetting];
             return Response::json($this->_result,200);

        }

}
