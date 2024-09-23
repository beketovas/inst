<?php

namespace Modules\Integration\Http\Controllers;

use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use App\Traits\CacheBuilder;
use Exception;
use Apiway\InputsDesigner\Dropdown\ValuesLoader;
use Apiway\InputsDesigner\Http\Resources\Dropdown\ValueResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Integration\Exceptions\NodeException;
use Modules\Integration\Facades\Loader;
use Modules\Integration\Facades\NodeManagerFacade;
use Modules\Integration\Loaders\Fields\DependentLoader;
use Nwidart\Modules\Routing\Controller;
use Modules\Integration\Repositories\IntegrationRepository;
use Modules\Integration\Repositories\NodeRepository;

class FieldController extends Controller
{
    use CacheBuilder, ConfigHelper;

    /**
     * @var IntegrationRepository
     */
    protected $integrationRepository;

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * FieldController constructor.
     *
     * @param IntegrationRepository $integrationRepository
     * @param NodeRepository $nodeRepository
     */
    public function __construct(
        IntegrationRepository $integrationRepository,
        NodeRepository $nodeRepository)
    {
        $this->integrationRepository = $integrationRepository;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * Store value
     *
     * @param Request $request
     * @param string $integrationCode
     * @param int $nodeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function storeValue(Request $request, string $integrationCode, int $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }

        $this->authorize('manage', $integration);
        if($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        $fieldId = (int) $request->get('field_id');
        $value = $request->get('value', null);
        $marks = $request->get('marks');
        if(empty($marks))
            $marks = null;
        $entity = $request->get('entity');

        $baseNode = $this->nodeRepository->getById($nodeId);
        if(!$baseNode)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        $this->cacheForget(['node_'.$baseNode->id, 'settings'], ['node_'.$baseNode->id, 'settings_with_values'],['node_'.$baseNode->id, 'fields'], ['node_'.$baseNode->id, 'fields_with_example'], ['node_storage_'.$baseNode->id, 'node_fields_with_values_only']);

        try {
            $appNode = $baseNode->applicationNode;
        } catch (NodeException $e) {
            return response()->json(['errorMessage' => __('integration::node.does_not_exist')], 404);
        }

        $fieldValueRepository  = NodeManagerFacade::load($baseNode)->fieldValueRepository($entity);

        /*
        // If value is empty delete value from db
        if(empty($value)) {
            $fieldValueRepository->deleteByNodeAndFieldId($appNode->id, $fieldId);
            return response()->json([]);
        }
        */

        $fieldValue = $fieldValueRepository->findByNodeAndFieldId($appNode->id, $fieldId);
        try {
            if ($fieldValue) {
                $fieldValueRepository->update(['value' => $value, 'marks' => $marks], $fieldValue);
            } else {
                $fieldValueRepository->store([
                    'node_id' => $appNode->id,
                    'field_id' => $fieldId,
                    'value' => $value,
                    'marks' => $marks
                ]);
            }
        } catch (Exception $e) {
            // data is too long
        }

        return response()->json(['hasValue' => !is_null($value) ? true : false]);
    }

    /**
     * Store value
     *
     * @param Request $request
     * @param string $integrationCode
     * @param int $nodeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function storeBoolean(Request $request, string $integrationCode, int $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }

        $this->authorize('manage', $integration);
        if($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        $fieldId = (int) $request->get('field_id');
        $value = $request->get('value', null);
        $entity = $request->get('entity');

        $baseNode = $this->nodeRepository->getById($nodeId);
        if(!$baseNode)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        $this->cacheForget(['node_'.$nodeId, 'settings'], ['node_'.$nodeId, 'settings_with_values'], ['node_'.$nodeId, 'fields'], ['node_'.$nodeId, 'fields_with_example']);

        $fieldValueRepository  = NodeManagerFacade::load($baseNode)->fieldValueRepository($entity);
        $appNode = $baseNode->applicationNode;

        $fieldValue = $fieldValueRepository->findByNodeAndFieldId($appNode->id, $fieldId);
        try {
            if ($fieldValue) {
                $fieldValueRepository->update(['value' => $value], $fieldValue);
            } else {
                $fieldValueRepository->store([
                    'node_id' => $appNode->id,
                    'field_id' => $fieldId,
                    'value' => $value
                ]);
            }
        } catch (Exception $e) {
            // data is too long
        }

        return response()->json([
            'value' => $value,
            'hasValue' => !is_null($value) ? true : false
        ]);
    }

    /**
     * @param Request $request
     * @param string $integrationCode
     * @param int $nodeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function loadDropdownValues(Request $request, string $integrationCode, int $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }

        $this->authorize('manage', $integration);

        $baseNode = $this->nodeRepository->getById($nodeId);
        if(!$baseNode)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        $account = $baseNode->account;
        try {
            $appNode = $baseNode->applicationNode;
        } catch (NodeException $e) {
            return response()->json(['errorMessage' => __('integration::node.does_not_exist')], 404);
        }

        $fieldId = $request->get('field_id');
        $entity = $request->get('entity');

        $fieldRepository  = NodeManagerFacade::load($baseNode)->fieldRepository($entity);
        $field = $fieldRepository->getById($fieldId);

        if(!$field)
            return response()->json(['errorMessage' => __('integration::node.Field does not exist. Please reload page.')]);

        $valuesLoader = new ValuesLoader($baseNode, $account, $field, $entity);
        try {
            $loaderResponse = $valuesLoader->load();
        } catch (\TypeError $e) {
            return response()->json(['errorMessage' => __('integration::node.Field does not exist. Please reload page.')]);
        }

        if($error = $loaderResponse->getError()) {
            return response()->json(['errorMessage' => $error]);
        }

        $items = $loaderResponse->getItems();
        if(empty($items)) {
            return response()->json(['errorMessage' => __('validation.no_items_loaded_for_identifier', ['identifier' => $field->identifier])]);
        }

        return response()->json(['items' => ValueResource::collection($items)]);
    }

    /**
     * Store dropdown value
     *
     * @param Request $request
     * @param string $integrationCode
     * @param int $nodeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function storeDropdownValue(Request $request, string $integrationCode, int $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }

        $this->authorize('manage', $integration);
        if($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        $fieldId = $request->get('field_id');
        $entity = $request->get('entity');
        $dropdownValue = $request->get('field_value');
        $dropdownLabel = $request->get('field_label');
        $dropdownAdditionalData = $request->get('field_additional_data');

        $baseNode = $this->nodeRepository->getById($nodeId);
        if(!$baseNode)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        $this->cacheForget(['node_'.$nodeId, 'settings'], ['node_'.$nodeId, 'settings_with_values'], ['node_'.$nodeId, 'fields'], ['node_'.$nodeId, 'fields_with_example']);

        // Store field value
        $fieldValueRepository  = NodeManagerFacade::load($baseNode)->fieldValueRepository($entity);
        $appNode = $baseNode->applicationNode;
        $fieldValue = $fieldValueRepository->findByNodeAndFieldId($appNode->id, $fieldId);
        try {
            if ($fieldValue) {
                $fieldValue = $fieldValueRepository->update([
                    'value_json' => ['value' => $dropdownValue, 'label' => $dropdownLabel],
                    'additional_data' => $dropdownAdditionalData
                ], $fieldValue);
            } else {
                $fieldValue = $fieldValueRepository->store([
                    'node_id' => $appNode->id,
                    'field_id' => $fieldId,
                    'value_json' => ['value' => $dropdownValue, 'label' => $dropdownLabel],
                    'additional_data' => $dropdownAdditionalData
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['errorMessage' => __('integration::node.Field does not exist. Please reload page.')]);
        }

        // If field has loader, use this loader
        $fieldRepository = NodeManagerFacade::load($baseNode)->fieldRepository($entity);
        $field = $fieldRepository->getById($fieldId);
        if(!empty($field->loader)) {
            // Delete children
            $fieldRepository->deleteFieldsByFilter(['appNodeId' => $appNode->id, 'parentId' => $fieldId]);

            $fieldsConfig = $this->getApiConfig($baseNode->application->type, 'fields_list');
            if (!isset($fieldsConfig))
                $fieldsLoader = NodeManagerFacade::load($baseNode)->fieldsLoader($field->loader);
            else
                $fieldsLoader = app(DependentLoader::class, ['type' => $field->loader, 'node' => $baseNode, 'fieldValue' => $fieldValue]);

            $loaderResponse = $fieldsLoader->load();
            if(empty($loaderResponse->getError())) {
                $dataStorage = $loaderResponse->getDataStorage();
                if ($dataStorage)
                    $fieldRepository->saveDataElements($dataStorage->getElements(), $appNode->id);
            }
        }

        return response()->json([
            'value' => $fieldValue->value_json['value'],
            'label' => $fieldValue->value_json['label'],
            'hasValue' => !is_null($dropdownValue) ? true : false
        ]);
    }

    /**
     * Clear value
     *
     * @param Request $request
     * @param string $integrationCode
     * @param int $nodeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function clearValue(Request $request, string $integrationCode, int $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }

        $this->authorize('manage', $integration);

        $fieldId = (int) $request->get('field_id');
        $entity = $request->get('entity');

        $baseNode = $this->nodeRepository->getById($nodeId);
        if(!$baseNode)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        $this->cacheForget(['node_'.$nodeId, 'settings'], ['node_'.$nodeId, 'settings_with_values'], ['node_'.$nodeId, 'fields'], ['node_'.$nodeId, 'fields_with_example']);

        $fieldValueRepository  = NodeManagerFacade::load($baseNode)->fieldValueRepository($entity);
        $appNode = $baseNode->applicationNode;
        $fieldValueRepository->deleteByNodeAndFieldId($appNode->id, $fieldId);

        // Delete children
        $fieldRepository = NodeManagerFacade::load($baseNode)->fieldRepository($entity);
        $field = $fieldRepository->getById($fieldId);
        if(!empty($field->loader)) {
            $fieldRepository->deleteFieldsByFilter(['appNodeId' => $appNode->id, 'parentId' => $fieldId]);
        }

        return response()->json([]);
    }

    /**
     * Get available related fields
     *
     * @param Request $request
     * @param string $integrationCode
     * @param int $nodeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function available(Request $request, string $integrationCode, int $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }

        $this->authorize('manage', $integration);
        if($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        $triggerNode = $integration->triggerNode();

        $triggerFieldRepository  = NodeManagerFacade::load($triggerNode)->fieldRepository('NodeField');
        $availableFields = $triggerFieldRepository->getAvailable($triggerNode->applicationNode);

        return response()->json(['fields' => $availableFields]);

    }

    /**
     * Refresh fields completely for the node
     *
     * @param Request $request
     * @param string $integrationCode
     * @param int $nodeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Modules\Integration\Exceptions\NodeException
     */
    public function refreshFields(Request $request, string $integrationCode, int $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }

        $this->authorize('manage', $integration);
        if($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        $this->cacheForget(['node_'.$nodeId, 'settings'], ['node_'.$nodeId, 'settings_with_values'], ['node_'.$nodeId, 'fields'], ['node_'.$nodeId, 'fields_with_example']);

        $baseNode = $this->nodeRepository->getById($nodeId);

        $appNode = $baseNode->applicationNode;
        if(!$appNode->action_id) {
            return response()->json(['errorMessage' => __('integration::node.select_action_first')]);
        }

        // Values loader
        $fieldsLoader = Loader::create($baseNode);
        $loaderResponse = $fieldsLoader->load();
        $fieldRepository  = NodeManagerFacade::load($baseNode)->fieldRepository('NodeField');
        $fieldRepository->deleteFieldsByFilter(['appNodeId' => $appNode->id]);
        if($error = $loaderResponse->getError()) {
            return response()->json(['errorMessage' => $error]);
        }
        // Delete related values from action node
        if($baseNode->isTrigger()) {
            $actionNode = $baseNode->nextNode();
            $this->cacheForget(['node_'.$actionNode->id, 'settings'], ['node_'.$actionNode->id, 'settings_with_values'], ['node_'.$actionNode->id, 'fields'], ['node_'.$actionNode->id, 'fields_with_example']);
            // Only if action node has selected application
            if($actionNode->application_type) {
                $actionNodeFieldRepository = NodeManagerFacade::load($actionNode)->fieldRepository('NodeField');
                $actionNodeFieldRepository->deleteFieldsByFilter(['appNodeId' => $actionNode->applicationNode->id]);
            }
        }

        $dataStorage = $loaderResponse->getDataStorage();
        if(!$dataStorage)
            return response()->json([]);

        $fieldRepository->saveDataElements($dataStorage->getElements(), $appNode->id);

        return response()->json();
    }

}
