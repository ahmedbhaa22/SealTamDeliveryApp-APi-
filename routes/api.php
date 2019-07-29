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

Route::post('admin/create','AdminController@Create')->middleware('cors','auth:api');
Route::get('admin/unactivateAdmin/{id}','AdminController@unactive_admin')->middleware('cors','auth:api');
Route::get('admin/activateAdmin/{id}','AdminController@active_admin')->middleware('cors','auth:api');
Route::post('admin/addRole','AdminController@add_role')->middleware('cors','auth:api');
Route::post('admin/editRole','AdminController@edit_role')->middleware('cors','auth:api');
Route::get('admin/showRole/{id}','AdminController@get_role')->middleware('cors','auth:api');
Route::get('admin/get_all_admin','AdminController@get_all_admin')->middleware('cors','auth:api');
Route::get('admin/get_admin/{id}','AdminController@get_admin')->middleware('cors','auth:api');
Route::post('admin/login','AdminController@login')->middleware('cors');
Route::post('admin/changepassword','UsersController@edit_pass')->middleware('cors');


//End Admin Routes
/* =================================================================*/


//Resturants Routes
/* =================================================================*/
Route::post('resturant/create','ResturantsController@Create')->middleware('cors','auth:api');
Route::post('resturant/edit/{id}','ResturantsController@Edit_Resturant')->middleware('cors','auth:api');
Route::get('resturant/all_resturants','ResturantsController@get_all_resturants')->middleware('cors','auth:api');
Route::get('resturant/get_resturant/{id}','ResturantsController@get_resturant')->middleware('cors','auth:api');
Route::get('resturant/delete-resturant/{id}','ResturantsController@destroy')->middleware('cors','auth:api');
/*resturants api routes*/
Route::post('resturant/login','ResturantsController@login');


//End Resturants Routes
/* =================================================================*/

//Drivers Routes
/* =================================================================*/
Route::post('driver/create','DriverController@createDriver')->middleware('cors','auth:api');
Route::post('driver/edit/{id}','DriverController@Edit_Driver')->middleware('cors','auth:api');
Route::get('driver/delete-driver/{id}','DriverController@destroy')->middleware('cors','auth:api');
Route::get('driver/all_drivers','DriverController@get_all_drivers')->middleware('cors','auth:api');
Route::get('driver/get_driver/{id}','DriverController@get_driver')->middleware('cors','auth:api');
/*Drivers api routes*/
Route::post('driver/login','DriverController@login');
Route::post('driver/addLocation','DriverController@add_location');
Route::post('driver/addDeviceToken','DriverController@add_deviceToken');
Route::get('driver/MakeOnline/{id}','DriverController@make_online');
Route::get('driver/MakeOffline/{id}','DriverController@make_offline');
Route::get('driver/MakeOntrip/{id}','DriverController@make_ontrip');
Route::post('driver/changeAvailability','DriverController@change_availability');


//End Drivers Routes
/* =================================================================*/



//Start Adds Routes
/* =================================================================*/

Route::get('add/get','AddController@get_add')->middleware('cors','auth:api');
Route::post('add/edit','AddController@save_add')->middleware('cors','auth:api');

//End Adds Routes
/* =================================================================*/

//Start Home Routes
/* =================================================================*/

Route::get('home/home','HomeController@get_items_count')->middleware('cors','auth:api');
Route::get('home/all_resturants','HomeController@get_all_resturants')->middleware('cors','auth:api');

//End Home Routes
/* =================================================================*/

//Start Orders Routes
/* =================================================================*/

Route::post('order/create','ResturantApi\CurrentOrdersController@CreateNewOrder')->middleware('cors');
Route::post('order/Response','ResturantApi\CurrentOrdersController@OrderNotficationResponse')->middleware('cors');

/* order Apis */

Route::post('order/changeStatus','OrderController@change_order_status');
Route::get('order/current/{id}','OrderController@get_current_order');
Route::post('order/cancelorder','OrderController@cancel_order_status');
Route::post('order/history','OrderController@get_history');

Route::get('orders/DriverRate/{id}','OrderController@get_driver_rate');
Route::get('orders/ResturantRate/{id}','OrderController@get_resturant_rate');
Route::post('order/RateDriver','OrderController@rate_driver');
Route::post('order/RateResturant','OrderController@rate_resturant');


//End Orders Routes
/* =================================================================*/

Route::get('/test', function (Request $request) {
    return Response::json('sda');
})->middleware('cors');
