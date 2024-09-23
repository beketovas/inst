<?php

use Illuminate\Support\Carbon;

return [
    'event_cancelled' => [
        'url' => 'https://www.api.applications.com/events/%s',
        'auth' => [
            'insert_credentials' => 'header',
            'settings' => [
                'key' => 'Authorization',
                'value' => 'Bearer {%access_token%}',
            ]
        ],
        'method' => 'get',
        'type_params' => 'query',
        'prepare_data' => function(array $settingAndSubData) {
            return [$settingAndSubData['calendar_id'], ['singleEvents' => 'true', 'showDeleted' => 'true']];
        },
        'paging' => [
            'check_result_in' => ['items'],
            'page' => [
                'name' => 'pageToken',
                'next' => ['nextPageToken']
            ],
            'limit' => [
                'name' => 'limit',
                'value' => 100
            ]
        ],
        'check' => function($response, \Illuminate\Support\Collection $settings)
        {
            $events = [];
            foreach ($response->items as $event) {
                $carbonUpdatedTime = new Carbon($event->updated);
                $diffUpdated = $carbonUpdatedTime->floatDiffInRealMinutes(now());
                $accuracy = config('googlecalendar.cron_interval')/2;
                if(abs($diffUpdated) <= $accuracy || abs($diffUpdated) <= $accuracy && $event->status == 'cancelled') {
                    $events[] = $event;
                }
            }
            return $events;
        }
    ],
    'event_started' => [
        'url' => 'https://www.api.applications.com/events/%s/%s',
        'auth' => [
            'insert_credentials' => 'header',
            'settings' => [
                'key' => 'Authorization',
                'value' => 'Bearer {%access_token%}',
            ]
        ],
        'method' => 'get',
        'type_params' => 'query',
        'paging' => [
            'check_result_in' => ['items'],
            'page' => [
                'name' => 'pageToken',
                'step' => 100,
            ],
            'limit' => [
                'name' => 'limit',
                'value' => 100
            ]
        ],
        'prepare_data' => function(array $settingAndSubData) {
            return [$settingAndSubData['calendar_id'], $settingAndSubData['account_id'], ['order_by' => 'created', $settingAndSubData['field']->value_factual]];
        },
        'check' => function($response, \Illuminate\Support\Collection $settings)
        {
            $events = [];
            foreach ($response->items as $event) {
                $carbonUpdatedTime = new Carbon($event->updated);
                $diffUpdated = $carbonUpdatedTime->floatDiffInRealMinutes(now());
                $accuracy = config('googlecalendar.cron_interval')/2;
                if(abs($diffUpdated) <= $accuracy || abs($diffUpdated) <= $accuracy && $event->status == 'cancelled') {
                    $events[] = $event;
                }
            }
            return $events;
        }
    ],
    'event_ended' => [
        'url' => 'https://www.api.applications.com/events',
        'auth' => [
            'insert_credentials' => 'header',
            'settings' => [
                'key' => 'Authorization',
                'value' => 'Bearer {%access_token%}',
            ]
        ],
        'method' => 'get',
        'type_params' => 'query',
        'check' => function($response, $lastFullPollingData, \Illuminate\Support\Collection $settings, \Apiway\ApiRequest\Client $client)
        {
            $events = [];
            $nextPageItem = $client->call('get_events', $response->items->last_page_url);
            $allEvents = array_merge($response->items, $nextPageItem->items);
            foreach ($allEvents as $event) {
                $carbonUpdatedTime = new Carbon($event->updated);
                $diffUpdated = $carbonUpdatedTime->floatDiffInRealMinutes(now());
                $accuracy = config('googlecalendar.cron_interval')/2;
                if(abs($diffUpdated) <= $accuracy || abs($diffUpdated) <= $accuracy && $event->status == 'cancelled') {
                    $events[] = $event;
                }
            }
            return ['filteredResult' => $events, 'fullResult' => $response];
        }
    ],
];
