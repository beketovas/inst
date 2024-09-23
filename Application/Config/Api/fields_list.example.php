<?php

return [
    'trigger' => [
        'new_product' => [
            'url' => 'https://{{account_data_json__shop}}/admin/api/2021-01/product.json/product%s',
            'auth' => [
                'insert_credentials' => 'header',
                'settings' => [
                    'key' => 'Authorization',
                    'value' => '{%access_token%}',
                ]
            ],
            'method' => 'get',
            'path_to_error_message' => ['errors'],
            'prepare_params' => function(\Illuminate\Support\Collection $fields) {
                $params = [];
                foreach($fields as $field) {
                    if($field->identifier == 'only_additional_data')
                        $params = array_merge($params, json_decode($field->additional_data, true));
                    else if($field->identifier == 'anything')
                        $params[] = $field->value_factual;
                }
                return $params;
            },
            'fetch' => function(object $item) {
                $product = $item->product;
                return $product[0];
            },
            'condition' => function(object $fields, \Illuminate\Support\Collection $settings) {
                return $fields->email == $settings[0]['value'];
            }
        ],
        'new_customer' => [
            'url' => 'https://{{account_data_json__shop}}/admin/api/2021-01/customers.json',
            'auth' => [
                'insert_credentials' => 'header',
                'settings' => [
                    'key' => 'Authorization',
                    'value' => '{%access_token%}',
                ]
            ],
            'method' => 'get',
            'path_to_error_message' => ['errors'],
            'fetch' => function(object $item) {
                $customers = $item->customers;
                return $customers[0];
            },
            'send' => function(object $item) {
                return $item;
            }
        ],
    ],
    'action' => [
        'create_ticket' => [
            'url' => 'https://{{account_data_json__shop}}/admin/api/2021-01/ticket.json',
            'auth' => [
                'insert_credentials' => 'header',
                'settings' => [
                    'key' => 'Authorization',
                    'value' => '{%access_token%}',
                ]
            ],
            'method' => 'get',
            'path_to_error_message' => ['errors'],
            'fetch' => function(object $item) {
                return $item;
            },
            'send' => function(object $item) {
                return $item;
            }
        ],
    ],
    'dependent' => [
        'additional_data_about_ticket' => [
            'url' => 'https://{{account_data_json__shop}}/admin/api/2021-01/ticket.json',
            'auth' => [
                'insert_credentials' => 'header',
                'settings' => [
                    'key' => 'Authorization',
                    'value' => '{%access_token%}',
                ]
            ],
            'method' => 'get',
            'path_to_error_message' => ['errors'],
            'fetch' => function(object $field) {
                $elements = collect();
                $element = new stdClass();
                $element->identifier = $field->name;
                $element->title = $field->label;
                $element->description = $field->description;
                $element->custom_field = true;
                switch ($field->field_type) {
                    case 'date':
                        $element->description = $field->description . ' Use Unix timestamp in milliseconds.';
                        break;
                    case 'booleancheckbox':
                        $element->type = 'boolean';
                        break;
                    case 'checkbox':
                    case 'radio':
                    case 'select':
                        $options = [];
                        $j = 0;
                        foreach ($field->options as $option) {
                            $options[$j]['sample'] = $j + 1;
                            $options[$j]['label'] = $option->label;
                            $options[$j]['value'] = $option->value;
                            ++$j;
                        }
                        $element->type = 'dropdown';
                        if (empty($options))
                            if (isset($field->referencedObjectType))
                                $element->dynamic = true;
                            else
                                $element->type = 'string';
                        else
                            $element->dropdown_source = json_encode($options);
                        break;
                    case 'textarea':
                        $element->type = 'text';
                        break;
                    default:
                        $element->type = 'string';
                        break;
                }
                $elements->add($element);
                return $elements;
            },
        ],
    ]
];
