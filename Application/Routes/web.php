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
// Only authenticated users
Route::group(['prefix' => LocalizationMiddleware::getLocale()], function() {
    Route::middleware('auth')->prefix('app')->group(function() {
        Route::get('/applications', 'ApplicationController@index')->name('applications');
        Route::prefix('/applications-attached')->group(function() {
            Route::get   ('/{slug}/{id}/edit', 'ConnectionController@edit')->name('application.edit');
            Route::put   ('/{slug}/{id}', 'ConnectionController@update')->name('application.update');
            Route::post('/{slug}/select-action/', 'ConnectionController@selectAction')->name('application.selectAction');
            Route::get   ('/{slug}/create','ConnectionController@create')->name('application.create');
            Route::post  ('/{slug}', 'ConnectionController@store')->name('application.store');
            Route::delete  ('/{slug}/{id}', 'ConnectionController@disconnect')->name('application.disconnect.delete');
            Route::get  ('/{slug}/{id}/test', 'ConnectionController@testConnection')->name('application.test');
        });
    });
    Route::get('/app/{slug}/login-from-soft', 'ConnectionController@redirectToApplicationEdit')->name('application.login');
});
Route::prefix('app/applications-attached')->group(function() {
    Route::get('/{slug}/process-authorization', 'CallbackController@receive')->name('application.process-authorization');
    Route::delete('/{slug}/process-authorization', 'CallbackController@uninstall')->name('application.uninstall.delete');
    Route::get('/{slug}/disconnect', 'DisconnectController@disconnect')->name('application.disconnect');
    Route::post('/{slug}/process-authorization', 'CallbackController@uninstall')->name('application.uninstall');
});


