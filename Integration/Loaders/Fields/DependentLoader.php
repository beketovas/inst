<?php

namespace Modules\Integration\Loaders\Fields;

use Apiway\ApiRequest\Client;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Apiway\InputsDesigner\Contracts\Loader\FieldsLoader;
use Apiway\InputsDesigner\Contracts\Repository\FieldWithValues as FieldWithValuesRepository;
use Apiway\InputsDesigner\Loader\FieldsLoaderResponse;
use Apiway\ServicesDataStorage\DataStorage;
use Illuminate\Support\Collection;
use Modules\Integration\Builders\DataElementBuilder;
use Modules\Integration\Entities\Node;
use Modules\Integration\Exceptions\NodeException;

class DependentLoader implements FieldsLoader
{
    use ConfigHelper;

    /**
     * @var Node
     */
    protected $node;

    /**
     * @var FieldWithValuesRepository
     */
    protected $fieldRepository;

    /**
     * @var DataStorage
     */
    protected $dataStorage;

    protected $fieldValue;

    protected string $type;

    /**
     * Constructor.
     * @param Node $node
     * @param string $type
     * @param $fieldValue
     */
    public function __construct(Node $node, string $type, $fieldValue)
    {
        $this->node = $node;
        $this->fieldRepository = app('Modules\\'.studly_case($node->application->type).'\\Repositories\\NodeFieldRepository');
        $this->dataStorage = new DataStorage();
        $this->fieldValue = $fieldValue;
        $this->type = $type;
    }

    public function prepareData()
    {
        $params[0] = $this->type;
        $bodyParams = [];
        foreach($this->fieldValue->additional_data as $key => $val) {
            if(is_int($key))
                $params[] = $val;
            else
                $bodyParams[$key] = $val;
        }
        $params[] = $bodyParams;
        return $params;
    }

    /**
     * @return FieldsLoaderResponse
     * @throws NodeException
     * @throws \Apiway\ApiRequest\Exceptions\UndefinedMethod
     */
    public function load() : FieldsLoaderResponse
    {
        $loaderResponse = new FieldsLoaderResponse();
        $applicationNode = $this->node->applicationNode;
        if (!$applicationNode) {
            $loaderResponse->setError('There is no service node');
            return $loaderResponse;
        }

        $account = $this->node->account;
        if (!$account) {
            $loaderResponse->setError(__('application::site.check_service_settings'));
            return $loaderResponse;
        }

        $config = $this->getApiConfig($this->node->application->type, 'fields_list');
        if (!isset($config['dependent'][$this->type])) {
            $loaderResponse->setError(__('application::site.check_service_settings'));
            return $loaderResponse;
        }

        $parentId = $this->fieldValue->field_id;
        if (isset($config['dependent'][$this->type]['url'])) {
            $params = [];
            if (isset($this->fieldValue['additional_data']))
                $params = $this->prepareData();

            $client = new Client($config['dependent'], $account);
            $res = call_user_func_array(array($client, 'call'), $params);
            if ($res->getError()) {
                $loaderResponse->setError(__('application::site.check_service_settings'));
                return $loaderResponse;
            }

            $val = $res->getResponse();
            try {
                $items = $config['dependent'][$this->type]['fetch']($val, $params, $parentId);
            } catch (\Exception $e) {
                $loaderResponse->setError(__('application::site.check_service_settings'));
                return $loaderResponse;
            }
        } else {
            $items = $config['dependent'][$this->type]['load']([$this->fieldValue->value_json['value']], $parentId);
        }

        $this->addElementsToDataStorage($items);
        $loaderResponse->setDataStorage($this->dataStorage);

        return $loaderResponse;
    }

    protected function addElementsToDataStorage(Collection $items): void
    {
        foreach ($items as $item) {
            $elementBuilder = new DataElementBuilder();
            $elementBuilder->addIdentifier($item->identifier);
            $elementBuilder->addTitle($item->title);
            $elementBuilder->addType($item->type);

            if (isset($item->ordering))
                $elementBuilder->addOrdering($item->ordering);

            if (isset($item->required))
                $elementBuilder->addRequired($item->required);

            if (isset($item->dropdown_source))
                $elementBuilder->addDropdownSource($item->dropdown_source);

            if (isset($item->description))
                $elementBuilder->addDescription($item->description);

            if (isset($item->parent_id))
                $elementBuilder->addParentId($item->parent_id);

            if (isset($item->uses_fields))
                $elementBuilder->addUsesFields($item->uses_fields);

            if (isset($item->dynamic))
                $elementBuilder->addDynamic($item->dynamic);

            if (isset($item->custom_field)) {
                $elementBuilder->addCustomField($item->custom_field);
                $elementBuilder->addIsCustomField($item->custom_field);
            }

            $this->dataStorage->addElement($elementBuilder->getElement());
        }
    }
}
