<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//
Route::get('/', function () {
    return view('welcome');
});

/**
 * Admins control user registration. So use all the other Auth functions BESIDES registration
 * https://pawelmysior.com/how-to-remove-the-registration-feature-in-laravel-authentication
 */
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/vacation-request', 'VacationRequestController@create')->name('requestDashboard');
Route::get('/admin', 'AdminController@index')->name('adminIndex');
Route::get('/admin/users', 'AdminController@users')->name('userMgmtIndex');
