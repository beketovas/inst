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
Route::prefix(LocalizationMiddleware::getLocale())->group(function() {
    Route::prefix('app/ways')->group(function() {
        Route::middleware('auth')->group(function() {
            Route::get('/', 'IntegrationController@index')->name('integrations');
            Route::post('/{integrationCode}/activate', 'IntegrationController@activate')->name('integrations.activate');
            Route::post('/{integrationCode}/deactivate', 'IntegrationController@deactivate')->name('integrations.deactivate');
            Route::post('/create', 'IntegrationController@create')->name('integrations.create');
            Route::post('/create-with-applications', 'IntegrationController@createWithApplications')->name('integrations.create-with-applications');
            Route::delete('/{code}/destroy', 'IntegrationController@destroy')->name('integrations.destroy');

            //Route::get('/{integrationCode}/nodes', 'NodeController@nodes')->name('integrations.nodes');
            Route::get('/{integrationCode}/nodes/{nodeId}/name', 'NodeController@name')->name('integrations.nodes.name');
        });

        //Route::get('/{integrationCode}/nodes', 'NodeController@nodes')->name('integrations.nodes');
        Route::get('/{integrationCode}/nodes', 'NodeController@nodes')->middleware('access:admin,user')->name('integrations.nodes');

    });
});



