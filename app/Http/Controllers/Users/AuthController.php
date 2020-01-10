<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\BaseController;
use Laravel\Passport\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as HttpClient;
use DB;
use Validator;
use Hash;
use App\User;
use App\Admin;
use Route;

class AuthController extends BaseController
{
    private $_client;
    public function __construct()
    {
        $this->_client=Client::find(2);
        parent::__construct();
    }

    public function login(Request $request)
    {
        $userToken = $this->getUserToken($request);
        if (isset($userToken['error'])) {
            return $this->Response(false, null, $userToken['message'], false);
        } else {
            $user =User::where('email', $request->email)->first();
            $user->access_token = $userToken['access_token'];
            $user->refresh_token = $userToken['refresh_token'];
            if ($user->Status &&$user->UserType ==$request->currentApplicationUser) {
                return $this->Response(
                    true,
                    [
                        "user"=>$user->getAuthUser($request->email),
                    ]
                );
            } else {
                return $this->Response(false, null, "messages.Auth.NotActive", true);
            }
        }
    }
    public function getAuthUser()
    {
        return $this->Response(
            true,
            [
            "user"=>auth()->user()->getAuthUser(),
        ]
        );
    }
    
    public function getUserToken(Request $request)
    {
        $form_params= [
            'grant_type' => 'password',
            'client_id' => $this->_client->id,
            'client_secret' => $this->_client->secret,
            'username' => $request->email,
            'password' => $request->password,
            "Status"=>1,
            'scope' => '*',
        ];
        $request->request->add($form_params);
        $pro= Request::create('oauth/token', 'POST');
        $a=Route::dispatch($pro);
        $access= json_decode((string) $a->getContent(), true);
        return $access;
    }
}
