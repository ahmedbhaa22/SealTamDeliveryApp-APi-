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

Route::post('user/create', 'UsersController@Create')->middleware('cors');

Route::post('user/login', 'UsersController@login')->middleware('cors');

Route::middleware('auth:api')->post('user/update', 'UsersController@update_user')->middleware('cors');

Route::post('user/changepassword', 'UsersController@edit_pass')->middleware('cors');

Route::get('user/get_all_admin', 'UsersController@get_all_admin')->middleware('cors');
Route::get('user/get_admin/{id}', 'UsersController@get_admin')->middleware('cors');

//Admin Routes
/* =================================================================*/




//End Admin Routes
/* =================================================================*/


//Resturants Routes
/* =================================================================*/
Route::get('resturant/get_resturant/{id}', 'ResturantsController@get_resturant')->middleware('auth:api');
Route::get('resturant/delete-resturant/{id}', 'ResturantsController@destroy')->middleware('auth:api');
/*resturants api routes*/
Route::post('resturant/login', 'ResturantsController@login');
Route::post('resturant/change_resturant_password', 'ResturantsController@change_resturant_password');

Route::get('resturant/appVersion', 'ResturantsController@get_app_version');

//End Resturants Routes
/* =================================================================*/

//Drivers Routes
/* =================================================================*/
Route::get('driver/get_driver/{id}', 'DriverController@get_driver')->middleware('auth:api');
/*Drivers api routes*/
Route::post('driver/login', 'DriverController@login');
Route::post('driver/addLocation', 'DriverController@add_location');
Route::post('driver/addDeviceToken', 'DriverController@add_deviceToken');
Route::get('driver/MakeOnline/{id}', 'DriverController@make_online');
Route::get('driver/MakeOffline/{id}', 'DriverController@make_offline');
Route::get('driver/MakeOntrip/{id}', 'DriverController@make_ontrip');
Route::post('driver/changeAvailability', 'DriverController@change_availability');
Route::post('driver/changePassword', 'DriverController@change_driver_password');
Route::get('driver/appVersion', 'DriverController@get_app_version');
//Route::post('driver/changBusy','DriverController@change_busy_status');
Route::get('driver/getData/{id}', 'DriverController@get_driver_data');


//End Drivers Routes
/* =================================================================*/



//Start Adds Routes
/* =================================================================*/

Route::get('add/get', 'AddController@get_add')->middleware('auth:api');
Route::post('add/edit', 'AddController@save_add')->middleware('auth:api');

//End Adds Routes
/* =================================================================*/

//Start Home Routes
/* =================================================================*/






//End Home Routes
/* =================================================================*/

//Start Orders Routes
/* =================================================================*/

Route::post('order/create', 'ResturantApi\CurrentOrdersController@CreateNewOrder');
Route::post('order/Response', 'ResturantApi\CurrentOrdersController@OrderNotficationResponse')->middleware('cors');
Route::post('order/orderPlus', 'ResturantApi\CurrentOrdersController@order_plus');





//Route::get('orders/{driver_id}/{resturant_id}','OrderController@all_orders');
Route::get('orders', 'OrderController@all_orders');


/* order Apis */

Route::post('order/changeStatus', 'OrderController@change_order_status');
Route::get('order/current/{id}', 'OrderController@get_current_order');
Route::post('order/cancelorder', 'OrderController@cancel_order_status');
Route::post('order/history', 'OrderController@get_history');
Route::post('order/resturant/history', 'OrderController@get_history_resturants');

Route::get('orders/DriverRate/{id}', 'OrderController@get_driver_rate');
Route::get('orders/ResturantRate/{id}', 'OrderController@get_resturant_rate');
Route::post('order/RateDriver', 'OrderController@rate_driver');
Route::post('resturant/RateDriver', 'OrderController@rate_driver');


Route::post('order/RateResturant', 'OrderController@rate_resturant');


//End Orders Routes
/* =================================================================*/


//Start Setting Routes
/* =================================================================*/
Route::post('setting/add', 'SettingController@add_setting');
Route::post('setting/edit', 'SettingController@edit_setting');


//end Setting Routes
/* =================================================================*/

Route::get('/test/{moduleName}/{type}', 'test@get')->middleware('cors');



Route::middleware('setApplication:admin')->prefix('admin')->group(function () {


    //==================================================================================
    //=========================category Group===========================================
    Route::middleware('auth:api', 'dashboardAccess')->prefix('category')->group(function () {
        Route::get('GetListPage', "Dashboard\CategoryController@GetListPage")->middleware('can:getList,App\Models\General\category');
        Route::post('Create', "Dashboard\CategoryController@store")->middleware('can:create,App\Models\General\category');
        Route::get('GetEditPage/{id}', "Dashboard\CategoryController@getEditPage")->middleware('can:getEdit,App\Models\General\category');
        Route::post('Edit/{id}', "Dashboard\CategoryController@Edit")->middleware('can:update,App\Models\General\category');
        Route::get('Delete/{id}', "Dashboard\CategoryController@delete")->middleware('can:update,App\Models\General\category');
    });

    //==================================================================================
    //=========================mini Dashboard Group=====================================

    Route::middleware('auth:api', 'dashboardAccess')->prefix('mini_dashboard')->group(function () {
        Route::get('GetListPage', "Dashboard\MiniDashBoardController@GetListPage")->middleware('can:getList,App\Models\Dashboard\mini_dashboard');
        Route::post('Create', "Dashboard\MiniDashBoardController@store")->middleware('can:create,App\Models\Dashboard\mini_dashboard');
        Route::get('GetEditPage/{id}', "Dashboard\MiniDashBoardController@getEditPage")->middleware('can:getEdit,App\Models\Dashboard\mini_dashboard');
        Route::post('Edit/{id}', "Dashboard\MiniDashBoardController@Edit")->middleware('can:update,App\Models\Dashboard\mini_dashboard');
    });
    //==================================================================================
    //=========================Roles Group=====================================

    Route::middleware('auth:api', 'dashboardAccess')->prefix('Roles')->group(function () {
        Route::get('GetListPage', "Dashboard\RolesAndPermisionsController@getRolesListPage")->middleware('can:getList,App\Models\Dashboard\roles');
        Route::get('GetCreatePage', "Dashboard\RolesAndPermisionsController@getRolesCreatePage")->middleware('can:getCreate,App\Models\Dashboard\roles');
        Route::get('GetEdit/{role_id}', "Dashboard\RolesAndPermisionsController@getRolesEditPage")->middleware('can:getEdit,App\Models\Dashboard\roles');
        Route::post('Create', "Dashboard\RolesAndPermisionsController@Create")->middleware('can:create,App\Models\Dashboard\roles');
        Route::post('Edit/{role_id}', "Dashboard\RolesAndPermisionsController@Edit")->middleware('can:update,App\Models\Dashboard\roles');
    });
    //==================================================================================
    //=========================AdminController Group=====================================

    Route::middleware('auth:api', 'dashboardAccess')->prefix('admin')->group(function () {
        Route::get('GetCreatePage', 'Dashboard\AdminController@getCreatePage')->middleware('can:getCreate,App\Admin');
        Route::get('GetEditPage/{id}', 'Dashboard\AdminController@getEditPage')->middleware('can:getEdit,App\Admin');
        Route::get('GetListPage', 'Dashboard\AdminController@getListPage')->middleware('can:getList,App\Admin');
        Route::post('create', 'Dashboard\AdminController@Create')->middleware('can:create,App\Admin');
        Route::post('Edit/{id}', "Dashboard\AdminController@Edit")->middleware('can:update,App\Admin');
        Route::post('change/userPassword', 'Dashboard\AdminController@change_user_password')->middleware('can:resetPassword,App\Admin');
    });

    //==================================================================================
    //===============================drivers=============================================
    Route::middleware('auth:api', 'dashboardAccess')->prefix('driver')->group(function () {
        Route::post('create', 'DriverController@createDriver')->middleware('can:create,App\Driver');
        Route::get('GetCreatePage', 'DriverController@getCreatePage')->middleware('can:getCreate,App\Driver');

        Route::post('edit/{id}', 'DriverController@Edit_Driver')->middleware('can:update,App\Driver');
        Route::get('delete-driver/{id}', 'DriverController@destroy')->middleware('can:update,App\Driver');
        Route::get('all_drivers', 'DriverController@get_all_drivers')->middleware('can:getList,App\Driver');
        Route::get('get_driver/{id}', 'DriverController@get_driver')->middleware('can:update,App\Driver');
        Route::get('resetBalance/{id}', 'DriverController@reset_balance')->middleware('can:resetBalanace,App\Driver');
    });
   
   
    //==================================================================================
    //===============================Others=============================================
    Route::post('login', 'Users\AuthController@login');
    Route::get('home', 'Dashboard\HomeController@index')->middleware('auth:api', 'dashboardAccess');
    Route::get('home/all_resturants', 'HomeController@get_all_resturants')->middleware('auth:api', 'dashboardAccess');
    Route::get('getAuthUser', 'Users\AuthController@getAuthUser')->middleware('auth:api', 'dashboardAccess');
    Route::post('changepassword', 'UsersController@edit_pass')->middleware('auth:api', 'dashboardAccess');

    Route::post('order/getlistpage', 'OrderController@GetListPage')->middleware('auth:api', 'dashboardAccess');
    Route::get('getStatusPage', 'DriverController@driver_status_page')->middleware('auth:api', 'dashboardAccess');
    Route::post('order', 'OrderController@GetDetailsPage')->middleware('auth:api', 'dashboardAccess');
    Route::post('resturant/create', 'ResturantsController@Create')->middleware('auth:api', 'dashboardAccess');
    Route::post('resturant/edit/{id}', 'ResturantsController@Edit_Resturant')->middleware('auth:api', 'dashboardAccess');
    Route::get('resturant/all_resturants', 'ResturantsController@get_all_resturants')->middleware('auth:api', 'dashboardAccess');
});
