<?php

return [
    'create_item' => [
        'url' => 'https://api.application.com/item',
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'type_params' => 'json',
        'auth' => [
            'insert_credentials' => 'header',
            'settings' => [
                'key' => 'Authorization',
                'value' => 'Bearer ',
                'encode' => [
                    'function' => 'base64_encode',
                    'value' => '{%access_token%}'
                ]
            ]
        ],
        'paging' => [
            'check_result_in' => ['data'],
            'page' => [
                'name' => 'page',
                'start' => 0,
                'next' => ['pagination', 'next', 'after']
            ],
            'limit' => [
                'name' => 'limit',
                'value' => 100
            ]
        ],
        'default_params' => [
            'id' => 'url:li:person:{{account_data_json__id}}:{{account_data_json__portalId}}'
        ],
        'method' => 'post',
        'path_to_error_message' => [
            'exception' => ['message'],
            'success' => [
                ['error_message'],
                ['error', 'message']
            ]
        ],
        'prepare_data' => function(array $fields, \Apiway\ApiRequest\Client $client) {
            foreach($fields as $field) {
                if($field->custom_field)
                    $customFields[$field->identifier] = $field->value;
                else {
                    if($field->type == 'file') {
                        $dirFile = dirname($field->value->name);
                        $newName = $dirFile.'/'.$field->value['file_name'];
                        rename($field->value->name, $newName);
                        $field->value->name = $newName;
                        $data[$field->identifier] = $field->value;
                    }
                    else if ($field->identifier == 'image')
                        $data[$field->identifier] = ['src' => $field->value];
                    else if ($field->identifier == 'title')
                        $data['option1'] = $field->value;
                    else {
                        $data[$field->identifier] = $field->value;
                    }
                }
            }
            if(!empty($customFields))
                $data['fields'] = $customFields;

            if($data['type'] == 'delete') {
                return $client->call('delete_item', $data);//return ServiceResponse and abort the current execution.
            }

            if(is_null($data))
                throw new \Apiway\ApiRequest\Exceptions\UserError('Some error occurred');

            return [(int)$data['product_id'], ['variant' => $data]];
        }
    ]
];
