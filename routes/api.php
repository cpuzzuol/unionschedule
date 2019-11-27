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

//Route::post('login', 'api\UserController@login');
//Route::group(['middleware' => 'auth:api'], function(){
//    Route::post('details', 'api\UserController@details');
//});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// How to use api tokens: https://laravel.com/docs/5.8/api-authentication
Route::apiResource('vacationrequests', 'api\VacationRequestController');
Route::apiResource('users', 'api\UserController');
Route::apiResource('restricteddates', 'api\RestrictedDateController');
//Route::apiResource('actionlogs', 'api\ActionLogController');
Route::post('users/register', 'api\UserController@register');
Route::get('vacationrequestsbydate/{date}', 'api\VacationRequestController@requestsByDate');
Route::get('requestsbyuser/{user}', 'api\VacationRequestController@requestsByUser');
Route::get('actionlogsbyuser/{user}', 'api\UserController@actionLogsByUser');
Route::get('userdaysleft/{user}', 'api\UserController@userDaysLeft');

Route::put('vacationrequeststatus', 'api\VacationRequestController@updateRequestStatus');
Route::get('admin/homedata', 'api\AdminController@getHomeData');
