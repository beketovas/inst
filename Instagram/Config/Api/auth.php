<?php

$me = [
    'url' => 'https://graph.instagram.com/me',
    'auth_type' => 'bearer',
    'method' => 'get'
];

$base64UrlDecode = function ($input) {
    return base64_decode(strtr($input, '-_', '+/'));
};

return [
    'authorization' => [
        'url' => 'https://www.instagram.com/oauth/authorize',
        'params' => [
            'default_params' => [
                'client_id' => config('instagram.service.client_id'),
                'redirect_uri' => config('instagram.service.redirect_uri'),
                'response_type' => 'code',
                'enable_fb_login' => '0',
                'force_authentication' => '1',
                'scope' => 'business_basic,business_manage_messages,business_manage_comments,business_content_publish'
            ]
        ]
    ],

    'access_token' => [
        'url' => 'https://api.instagram.com/oauth/access_token',
        'params' => [
            'default_params' => [
                'client_id' => config('instagram.service.client_id'),
                'redirect_uri' => config('instagram.service.redirect_uri'),
                'client_secret'=> config('instagram.service.client_secret'),
                'grant_type' => 'authorization_code'
            ],
        ],
    ],

    'third_party_install' => [
        'url' => 'https://graph.instagram.com/access_token',
        'method' => 'get',
        'params' => [
            'type_params' => 'query',
            'default_params' => [
                'client_secret' => config('instagram.service.client_secret'),
                'grant_type' => 'ig_exchange_token'
            ],
            'custom_params' => [
                'access_token'
            ]
        ],
    ],

    'test' => $me,
    'account_additional_data' => $me,

    'refresh_token' => [
        'url' => 'https://graph.instagram.com/refresh_access_token',
        'params' => [
            'type_params' => 'query',
            'default_params' => [
                'grant_type' => 'ig_refresh_token',
            ],
            'custom_params' => [
                'access_token'
            ]
        ],
        'method' => 'get'
    ],
    
    'third_party_uninstall' => [
        'path_to_identification_data_in_db' => 'user_id',

        'path_to_identification_data_in_request' => [
            'signed_request'
        ],

        'parse_function' => function ($signedRequest) use ($base64UrlDecode) {
            list($encodedSig, $payload) = explode('.', $signedRequest, 2);

            $secret = config('instagram.service.client_secret');
            $sig = $base64UrlDecode($encodedSig);
            $data = json_decode($base64UrlDecode($payload), true);

            $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
            if ($sig !== $expected_sig) {
                return null;
            }

            return (int) $data['user_id'];
        }
    ],
];
