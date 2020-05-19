<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('register','PassportController@register');
Route::post('login','PassportController@login');

Route::middleware('auth:api')->group(function (){

    Route::get('users','UserController@users');
    Route::post('user/add-money','UserController@addMoney');
    Route::post('invite/{user}','UserController@invite');
    Route::post('accept-bet/{invite}','UserController@acceptBet');
    Route::post('reject-bet/{invite}','UserController@rejectBet');
    Route::get('my-bets','UserController@myBets');
});
