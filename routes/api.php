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

//Admin Routes
/* =================================================================*/

Route::post('admin/create','AdminController@Create')->middleware('cors');
Route::get('admin/unactivateAdmin/{id}','AdminController@unactive_admin')->middleware('cors');
Route::get('admin/activateAdmin/{id}','AdminController@active_admin')->middleware('cors');
Route::post('admin/addRole','AdminController@add_role')->middleware('cors');
Route::post('admin/editRole','AdminController@edit_role')->middleware('cors');
Route::get('admin/showRole/{id}','AdminController@get_role')->middleware('cors');
Route::get('admin/get_all_admin','AdminController@get_all_admin')->middleware('cors');
Route::get('admin/get_admin/{id}','AdminController@get_admin')->middleware('cors');
Route::post('admin/login','AdminController@login')->middleware('cors');


//End Admin Routes
/* =================================================================*/


//Resturants Routes
/* =================================================================*/
Route::post('resturant/create','ResturantsController@Create')->middleware('cors');
Route::post('resturant/edit/{id}','ResturantsController@Edit_Resturant')->middleware('cors');
Route::get('resturant/all_resturants','ResturantsController@get_all_resturants')->middleware('cors');
Route::get('resturant/get_resturant/{id}','ResturantsController@get_resturant')->middleware('cors');
Route::get('resturant/delete-resturant/{id}','ResturantsController@destroy')->middleware('cors');


//End Resturants Routes
/* =================================================================*/

Route::get('/test', function (Request $request) {
    return Response::json('sda');
})->middleware('cors');
