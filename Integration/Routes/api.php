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

Route::group(['middleware' => 'auth:api,api-manager'], function() {
    Route::get('integrations/all', 'IntegrationController@indexJS')->name('integrations.all');
    Route::prefix('integrations/{integrationCode}')->group(function() {
        Route::post('/activate', 'IntegrationController@activateJS')->name('integrations.activate-js');
        Route::post('/deactivate', 'IntegrationController@deactivateJS')->name('integrations.deactivate-js');
        Route::put('/save-name', 'IntegrationController@saveName')->name('integrations.save-name');
        Route::get('/nodes-data', 'NodeController@nodesData')->name('integrations.nodes-data');
        Route::get('/nodes/validate', 'NodeController@nodesErrors')->name('integrations.nodes.validate');

        Route::prefix('/nodes/{nodeId}')->group(function() {
            Route::get('/data', 'NodeController@nodeData')->name('integrations.node-data');
            Route::put('/save-application', 'NodeController@saveApplication')->name('integrations.node.save-application');
            Route::put('/clear', 'NodeController@clearNode')->name('integrations.nodes.clear');
            Route::post('/fields/store-value', 'FieldController@storeValue')->name('integrations.nodes.fields.store-value');
            Route::post('/fields/store-boolean', 'FieldController@storeBoolean')->name('integrations.nodes.fields.store-boolean');
            Route::post('/fields/clear-value', 'FieldController@clearValue')->name('integrations.nodes.fields.clear-value');
            Route::post('/fields/available', 'FieldController@available')->name('integrations.node.fields.available');
            Route::post('/fields/refresh', 'FieldController@refreshFields')->name('integrations.node.fields.refresh');
            Route::post('/load-dropdown-values', 'FieldController@loadDropdownValues')->name('integrations.node.fields.load-dropdown-values');
            Route::post('/store-dropdown-value', 'FieldController@storeDropdownValue')->name('integrations.node.fields.store-dropdown-value');
            Route::put('{slug}/save-action', 'NodeController@saveAction')->name('integrations.node.save-action');
        });
    });

    Route::prefix('webhooks/{webhookCode}')->group(function() {
        Route::put('/open-for-sample', 'WebhookController@openForSample')->name('webhooks.open-for-sample');
        Route::put('/close-for-sample', 'WebhookController@closeForSample')->name('webhooks.close-for-sample');
        Route::post('/check-gate-availability', 'WebhookController@checkGateAvailability')->name('webhooks.check-gate-availability');
    });
});
