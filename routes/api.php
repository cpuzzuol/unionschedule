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
Route::post('users/register', 'api\UserController@register');
Route::get('vacationrequestsbydate/{date}', 'api\VacationRequestController@requestsByDate');

Route::put('vacationrequeststatus', 'api\VacationRequestController@updateRequestStatus');
Route::get('admin/homedata', 'api\AdminController@homedata');
