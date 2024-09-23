<?php
return [
    'authorization' => [
        'url' => 'http://third-party-app.com/oauth/authorize',
        'params' => [
            'default_params' => [
                'client_id' => config('{app}.service.client_id'),
                'redirect_uri' => config('{app}.service.redirect_url'),
                'client_secret' => config('{app}.service.client_secret'),
                'scope' => 'account_info,user_info'
            ]
        ]
    ],

    'access_token' => [
        'url' => 'http://third-party-app.com/oauth/token',
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'params' => [
            'default_params' => [
                'redirect_uri' => config('{app}.service.redirect_url'),
                'grant_type' => 'authorization_code'
            ],
        ],
    ],

    'account_additional_data' => [
        'url' => 'https://api.third-party-app.com/v1/users/me',
        'auth_type' => 'bearer',
        'method' => 'get'
    ],

    'refresh_token' => [
        'url' => 'https://oauth.third-party-app.com/oauth/token',
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'params' => [
            'type_params' => 'form_params',
            'default_params' => [
                'grant_type' => 'refresh_token'
            ],
            'custom_params' => [
                'refresh_token'
            ]
        ],
        'method' => 'post'
    ],

    'revoke' => [
        'url' => 'https://oauth.third-party-app.com/oauth/revoke',
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'params' => [
            'type_params' => 'form_params',
            'custom_params' => [
                'token' => 'refresh_token',
            ],
            'default_params' => [
                'token_type_hint' => 'refresh_token'
            ],
        ],
        'method' => 'post'
    ],

    'test' => [
        'url' => 'https://api.third-party-app.com/v1/activityTypes',
        'auth_type' => 'bearer',
        'method' => 'get'
    ],

    'third_party_uninstall' => [
        'path_to_identification_data_in_request' => ['user_id'],
        'path_to_identification_data_in_db' => 'data->id'
    ]
];
