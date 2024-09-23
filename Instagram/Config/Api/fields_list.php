<?php

$auth = [
    'insert_credentials' => 'header',
    'settings' => [
        'key' => 'Authorization',
        'value' => 'Bearer {%access_token%}'
    ]
];

return [
    'trigger' => [
        'new_media_posted_in_my_account' => [
            'url' => 'https://graph.instagram.com/me/media',
            'auth' => $auth,
            'method' => 'get',
            'type_params' => 'query',
            'path_to_error_message' => ['error', 'error_user_msg'],
            'default_params' => [
                'fields' => 'id,caption,media_type,media_url,permalink,timestamp,username',
                'limit' => '1'
            ],
            'fetch' => function(object $items) {
                return $items->data[0];
            }
        ],
    ]
];
