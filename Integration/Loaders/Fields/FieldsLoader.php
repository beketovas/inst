<?php

namespace Modules\Integration\Loaders\Fields;

use Apiway\ApiRequest\Client;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Apiway\InputsDesigner\Contracts\Loader\FieldsLoader as FieldsLoaderContract;
use Apiway\InputsDesigner\Loader\FieldsLoaderResponse;
use Apiway\ServicesDataStorage\DataStorage;
use Illuminate\Support\Collection;
use Modules\Application\Contracts\AccountModelContract;
use Modules\Integration\Builders\DataElementBuilder;
use Modules\Shopify\Repositories\FieldRepository;
use Modules\Integration\Entities\Node;
use Modules\Integration\Exceptions\NodeException;

class FieldsLoader implements FieldsLoaderContract
{
    use ConfigHelper;

    /**
     * @var Node
     */
    protected $node;

    /**
     * @var FieldRepository
     */
    protected $fieldRepository;

    /**
     * @var FieldsLoaderResponse
     */
    protected FieldsLoaderResponse $loaderResponse;

    /**
     * Constructor.
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
        $this->fieldRepository = app('Modules\\'.studly_case($node->application->type).'\\Repositories\\FieldRepository');
        $this->loaderResponse = new FieldsLoaderResponse();
    }

    protected function addToElementBuilder(Collection $fields): DataStorage
    {
        $dataStorage = new DataStorage();
        foreach ($fields as $field) {
            $elementBuilder = new DataElementBuilder();

            $elementBuilder->addIdentifier($field->identifier);
            $elementBuilder->addTitle($field->title);
            $elementBuilder->addType($field->type);
            if(isset($field->required))
                $elementBuilder->addRequired($field->required);
            if(isset($field->dynamic))
            $elementBuilder->addDynamic($field->dynamic);
            if(isset($field->uses_fields))
                $elementBuilder->addUsesFields($field->uses_fields);
            if(isset($field->dropdown_source))
                $elementBuilder->addDropdownSource($field->dropdown_source);
            if(isset($field->description))
                $elementBuilder->addDescription($field->description);
            if(isset($field->loader))
                $elementBuilder->addLoader($field->loader);
            if(isset($field->ordering))
            $elementBuilder->addOrdering($field->ordering);

            $dataStorage->addElement($elementBuilder->getElement());
        }
        return $dataStorage;
    }

    protected function staticField($action): DataStorage
    {
        $dataStorage = new DataStorage();
        $fields = $this->fieldRepository->getByActionId($action->id);
        if($fields->isEmpty()) {
            return $dataStorage;
        }

        $dataStorage = $this->addToElementBuilder($fields);

        return $dataStorage;
    }

    protected function dynamicField(AccountModelContract $account, $action): DataStorage
    {
        $dataStorage = new DataStorage();
        $fieldsConfig = $this->getApiConfig($this->node->application->type, 'fields_list');
        if(!isset($fieldsConfig['action'][$action->type]))
            return $dataStorage;
        $client = new Client($fieldsConfig['action'], $account);
        $res = $client->call($action->type);
        try {
            $fields = $fieldsConfig['action'][$action->type]['fetch']($res->getResponse());
        } catch(\Exception $e) {
            return $dataStorage;
        }
        $dataStorage = $this->addToElementBuilder($fields);
        return $dataStorage;
    }

    private function merge(DataStorage $staticDataStorage, DataStorage $dynamicDataStorage): DataStorage
    {
        $dataStorage = $staticDataStorage;

        $staticDataStorageElms = $staticDataStorage->getElements();
        $dynamicDataStorageElms = $dynamicDataStorage->getElements();
        if ($staticDataStorageElms->isNotEmpty() && $dynamicDataStorageElms->isNotEmpty()) {
            $mergedElements = $staticDataStorageElms->merge($dynamicDataStorageElms)->unique('identifier');
            $dataStorage->setElements($mergedElements);
        }

        return $dataStorage;
    }

    /**
     * @return FieldsLoaderResponse
     * @throws NodeException
     */
    public function load() : FieldsLoaderResponse
    {
        $applicationNode = $this->node->applicationNode;
        if(!$applicationNode) {
            $this->loaderResponse->setError('There is no service node');
            return  $this->loaderResponse;
        }

        $action = $applicationNode->action;
        if(!$action) {
            $this->loaderResponse->setError('Action is empty');
            return  $this->loaderResponse;
        }

        $account = $this->node->account;
        if(!$account) {
            $this->loaderResponse->setError(__('application::site.check_service_settings'));
            return $this->loaderResponse;
        }

        $staticDataStorage = $this->staticField($action);
        $dynamicDataStorage = $this->dynamicField($account, $action);

        $mergedDataStorage = $this->merge($staticDataStorage, $dynamicDataStorage);
        $this->loaderResponse->setDataStorage($mergedDataStorage);

        return $this->loaderResponse;
    }
}
