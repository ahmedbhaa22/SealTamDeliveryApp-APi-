<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Response;
use App\Http\ViewModel\ResultVM;
use Illuminate\Database\Eloquent\Model;
use Validator;

class BaseController extends Controller
{
    private $_result;
    const UNAUTHORIZED = 401;
    const SUCCESS = 200;
    const VALIDATIONERROR = 403;
    public $validationRule=[];
    public function __construct()
    {
        $this->_result=new ResultVM();
    }

    public function Response($isSucess, $data, $message='', $translate = true)
    {
        $this->_result->IsSuccess = $isSucess;
        $this->_result->Data = $data;
        if ($message) {
            $this->_result->FaildReason= $translate? trans($message):$message;
        }

        return Response::json($this->_result, Self::SUCCESS);
    }

    public function unauthorizedResponse()
    {
        return Response::json(null, Self::UNAUTHORIZED);
    }

    public function executeValidation()
    {
        $validation=Validator::make(request()->all(), $this->validationRule);
        if ($validation->fails()) {
            return $this->Response(false, null, $validation->errors()->first());
        }
    }

    public function hasAction($model, $method)
    {
        if ((((request()->dashboardId==0)
        || request()->mini_dashboard_id ==request()->dashboardId)
            && auth()->user()->havePermision("Can ".$method." ".$model))) {
            return true;
        }
        return false;
    }
}
