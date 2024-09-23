<?php

namespace Modules\Integration\Loaders;

use Apiway\ApiRequest\Client;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Apiway\InputsDesigner\Contracts\Dropdown\ValuesLoader;
use Apiway\InputsDesigner\Dropdown\AbstractValuesLoader;
use Apiway\InputsDesigner\Dropdown\Item;
use Apiway\InputsDesigner\Dropdown\ValuesLoaderResponse;
use Modules\Application\Entities\Application;

class DynamicFieldsLoader extends AbstractValuesLoader implements ValuesLoader
{
    use ConfigHelper;

    protected Application $application;
    protected string $identifier;

    public function __construct(Application $application, ValuesLoaderResponse $valuesLoaderResponse, string $identifier)
    {
        parent::__construct($valuesLoaderResponse);
        $this->application = $application;
        $this->identifier = $identifier;
    }

    /**
     * @return ValuesLoaderResponse
     * @throws \Apiway\ApiRequest\Exceptions\UndefinedMethod
     */
    public function load(): ValuesLoaderResponse
    {
        $fieldsConfig = $this->getApiConfig($this->application->type, 'fields_dynamic');
        if (!isset($fieldsConfig[$this->identifier])) {
            $this->loaderResponse->setError('Oops... error occurred. Type 1');
            return $this->loaderResponse;
        }

        $params = [];
        $params['type'] = $this->identifier;
        $additionalData = [];
        foreach ($this->fields as $field) {
            if (is_null($field->value_factual)) {
                $this->loaderResponse->setError("Didn't select a ". $field->title);
                return $this->loaderResponse;
            }

            $params[] = $field->value_factual;
            $additionalData = array_merge($additionalData, json_decode($field->additional_data, true));
        }

        if (isset($additionalData[1])) {
            $params[] = $additionalData[1];
        }

        $client = new Client($fieldsConfig, $this->account);

        if (isset($fieldsConfig[$this->identifier]['url'])) {
            $list = call_user_func_array([$client, "call"], $params);

            if ($list->getError()) {
                $this->loaderResponse->setError('No values');
                return $this->loaderResponse;
            }

            if (empty($list->getResponse())) {
                $this->loaderResponse->setError('No data.');
                return $this->loaderResponse;
            }
        }

        try {
            if (isset($fieldsConfig[$this->identifier]['url'])) {
                $preparedList = $fieldsConfig[$this->identifier]['fetch']($list->getResponse(), $additionalData, $client);
            } else {
                $preparedList = $fieldsConfig[$this->identifier]['fetch']($client);
            }

        } catch (\Exception $e) {
            $this->loaderResponse->setError('Oops... error occurred. Type 2');
            return $this->loaderResponse;
        }

        if ($preparedList->count() == 0) {
            $this->loaderResponse->setError('No data.');
            return $this->loaderResponse;
        }

        foreach ($preparedList as $item) {
            $dropdownItem = new Item();
            $dropdownItem->setValue($item->getValue());
            $dropdownItem->setLabel($item->getLabel());
            $dropdownItem->setAdditionalData($item->getAdditionalData());
            $this->loaderResponse->addItem($dropdownItem);
        }

        return $this->loaderResponse;
    }
}
