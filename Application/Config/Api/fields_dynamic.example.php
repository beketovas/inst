<?php

use Modules\Integration\Entities\DynamicField;

return [
    'product_id' => [
        'url' => 'https://{{account_data_json__shop}}/admin/api/2021-01/products.json',
        'auth' => [
            'insert_credentials' => 'header',
            'settings' => [
                'key' => 'X-Shopify-Access-Token',
                'value' => '{%access_token%}',
            ]
        ],
        'method' => 'get',
        'path_to_error_message' => ['errors'],
        'fetch' => function(object $items) {
            $fullResponse = collect();
            foreach($items->products as $item) {
                $response = new DynamicField();
                $response->setLabel($item->title);
                $response->setValue($item->id);
                $response->setAdditionalData([$item->id]);
                $fullResponse->add($response);
            }
            return $fullResponse;
        },
    ],
];
