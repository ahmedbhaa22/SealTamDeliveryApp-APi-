<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('user/create','UsersController@Create')->middleware('cors');

Route::post('user/login','UsersController@login')->middleware('cors');

Route::middleware('auth:api')->post('user/update','UsersController@update_user')->middleware('cors');

Route::post('user/changepassword','UsersController@edit_pass')->middleware('cors');

Route::get('user/get_all_admin','UsersController@get_all_admin')->middleware('cors');
Route::get('user/get_admin/{id}','UsersController@get_admin')->middleware('cors');


Route::get('/test', function (Request $request) {
    return Response::json('sda');
})->middleware('cors');
