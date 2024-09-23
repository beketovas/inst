<?php

use Carbon\Carbon;

$auth = [
    'insert_credentials' => 'header',
    'settings' => [
        'key' => 'Authorization',
        'value' => 'Bearer {%access_token%}'
    ]
];

$paging = [
    'check_result_in' => ['data'],
    'page' => [
        'name' => 'after',
        'next_if_exists' => ['paging', 'next'],
        'next' => ['paging', 'cursors', 'after'],
    ]
];

return [
    'new_media_posted_in_my_account' => [
        'url' => 'https://graph.instagram.com/me/media',
        'auth' => $auth,
        'method' => 'get',
        'paging' => $paging,
        'type_params' => 'query',
        'path_to_error_message' => ['error', 'error_user_msg'],
        'default_params' => [
            'fields' => 'id,caption,media_type,media_url,permalink,timestamp,username'
        ],
        'check' => function($response) {
            $events = [];

            foreach ($response as $event) {
                $carbonUpdatedTime = new Carbon($event->timestamp);
                $diffUpdated = $carbonUpdatedTime->floatDiffInRealMinutes(now());

                if (abs($diffUpdated) <= config('app.polling_cron_interval')) {
                    $events[] = $event;
                }
            }

            return $events;
        }
    ],
];
