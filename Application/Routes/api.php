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

Route::middleware('auth:api,api-manager')->get('/application', function (Request $request) {
    return $request->user();
});
Route::group(['middleware' => 'auth:api,api-manager'], function() {
    Route::middleware('auth')->prefix('app')->group(function() {
        Route::get('/applications', 'ApplicationController@applications')->name('applications.all');
        Route::post('/test-connection/{slug}/{id}', 'ConnectionController@testConnectionJS')->name('applications.test-connection');
        Route::post('/disconnect/{slug}/{id}', 'ConnectionController@disconnectJS')->name('applications.disconnect');
    });
});
