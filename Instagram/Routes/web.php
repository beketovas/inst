<?php

Route::prefix('webhooks')->group(function() {
    Route::post('/catch/{webhookCode}/instagram', 'WebhookController@catchWebhook')->name('webhooks.catch.instagram');
    Route::get('/catch/{webhookCode}/instagram', 'WebhookController@catchWebhook')->name('webhooks.catch.instagram.get');
});