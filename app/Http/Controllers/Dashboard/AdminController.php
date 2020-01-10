<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\Client;

use Response;
use DB;
use Validator;
use App\User;
use App\Admin;
use Hash;
use App\Http\Controllers\Shared\BaseController;
use App\Http\Resources\adminResource;

class AdminController extends BaseController
{
    public $validationRule=
    [
        'name'=>'required|string',

    ];

    public function getCreatePage()
    {
        return $this->Response(true, new adminResource(new Admin()));
    }

    public function getEditPage($id)
    {
        $admin = Admin::where('user_id', $id)->first();
        if ($admin == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }
        return $this->Response(true, new adminResource($admin));
    }
    public function Create(Request $request)
    {
        $this->validationRule['email']= 'required|email|unique:users,email';
        $this->validationRule['password']= 'required|string|min:5';

        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }

        $admin = (new User())->saveAdmin();

        return $this->Response(true, ['user'=>$admin]);
    }

    public function Edit(Request $request, $id)
    {
        if (!$this->hasAction('admins', 'Edit')) {
            return $this->unauthorizedResponse();
        }
        $this->validationRule['email']= 'required|email|unique:users,email,'.$id;
        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }

        $user =User::find($id);
        if ($user == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }
        $user->saveAdmin();
        return $this->Response(true, ['user'=>$user]);
    }
    public function getListPage()
    {
        return $this->Response(true, adminResource::collection((new Admin())->mini_dashboard_admins()));
    }



    public function change_user_password(Request $request)
    {
        $user =User::find($request->user_id);
        if ($user == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }
        $user->resetPassword();
        return $this->Response(true, null);
    }
}
