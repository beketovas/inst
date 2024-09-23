<?php

namespace Modules\Integration\Loaders\Fields;

use Apiway\ApiRequest\Client;
use Apiway\ApiRequest\Exceptions\TooFewArgumentException;
use Apiway\ArrayManipulator\Arr;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Apiway\InputsDesigner\Contracts\Loader\FieldsLoader;
use Apiway\InputsDesigner\Loader\FieldsLoaderResponse;
use Apiway\ServicesDataStorage\DataStorage;
use App\Helpers\StringHelper;
use Modules\Integration\Builders\DataElementBuilder;
use Modules\Integration\Entities\Node;
use Modules\Integration\Facades\NodeManagerFacade;

class TriggerFieldsLoader implements FieldsLoader
{
    use ConfigHelper;

    /**
     * @var Node
     */
    protected $node;

    protected $fieldRepository;

    /**
     * Constructor.
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
        $this->fieldRepository = NodeManagerFacade::load($node)->fieldRepository();
    }

    /**
     * @return FieldsLoaderResponse
     */
    public function load() : FieldsLoaderResponse
    {
        $loaderResponse = new FieldsLoaderResponse();

        $applicationNode = $this->node->applicationNode;
        if(!$applicationNode) {
            $loaderResponse->setError('There is no service node');
            return  $loaderResponse;
        }

        $action = $applicationNode->action;
        $config = $this->getApiConfig($this->node->application->type, 'fields_list');
        if(!isset($config['trigger'][$applicationNode->action->type])) {
            $loaderResponse->setError('There is no config');
            return $loaderResponse;
        }

        $account = $this->node->account;
        if(!$account) {
            $loaderResponse->setError(__('application::site.check_service_settings'));
            return $loaderResponse;
        }

        $dependentFields = $this->fieldRepository->getWithValues(['appNodeId' => $applicationNode->id, 'actionId' => $action->id]);
        foreach ($dependentFields as $field) {
            if ($field->required && is_null($field->value_factual)) {
                $loaderResponse->setError('Fill in required fields.');
                return $loaderResponse;
            }
        }

        $params = [$action->type];
        $endpointConfig = $this->getApiConfig($this->node->application->type, 'api_endpoints');
        $endpointClient = new Client($endpointConfig, $account);
        try {
            if (isset($config['trigger'][$action->type]['prepare_params']))
                $params = array_merge($params, $config['trigger'][$action->type]['prepare_params']($dependentFields, $endpointClient));
        } catch (\Exception $e) {
            $loaderResponse->setError($e->getMessage());
            return $loaderResponse;
        }

        $client = new Client($config['trigger'], $account);
        try {
            $res = call_user_func_array([$client, "call"], $params);
        } catch (TooFewArgumentException $e) {
            $loaderResponse->setError($e->getMessage());
            return $loaderResponse;
        }

        if ($res->getError()) {
            $loaderResponse->setError($res->getError());
            return $loaderResponse;
        }

        $fields = null;
        $apiEndpointsConfig = $this->getApiConfig($this->node->application->type, 'api_endpoints');
        try {
            $fields = $config['trigger'][$action->type]['fetch'](
                $res->getResponse(),
                $params,
                new Client($apiEndpointsConfig, $account),
                [
                    'integration_id' => $this->node->integration_id,
                    'fields' => $dependentFields
                ]
            );
        } catch (\Exception $e) {
            $loaderResponse->setError('No data.');
            return $loaderResponse;
        }

        if (is_null($fields)) {
            $loaderResponse->setError('No data.');
            return $loaderResponse;
        }

        $dataStorage = new DataStorage();
        $fields = Arr::objectFlatten($fields);
        foreach ($fields as $identifier => $value) {
            $elementBuilder = new DataElementBuilder();

            $elementBuilder->addIdentifier($identifier);
            $elementBuilder->addTitle(StringHelper::titleFromString($identifier));
            if($value)
                $elementBuilder->addValue($value);

            $dataStorage->addElement($elementBuilder->getElement());
        }

        $loaderResponse->setDataStorage($dataStorage);

        return $loaderResponse;
    }
}
