<?php

namespace Modules\Integration\Builders\Node\ApplicationBuilders\ErrorsBuilders;

use App\Traits\CacheBuilder;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Illuminate\Support\Facades\Log;
use Modules\Application\Facades\ApplicationAccount;
use Modules\Application\Facades\ApplicationRepository;
use Modules\Integration\Contracts\ApplicationNode;
use Modules\Integration\Facades\NodeManagerFacade;

class ErrorsBuilder extends AbstractErrorsBuilder
{
    use CacheBuilder, ConfigHelper;

    /**
     * Build errors for node
     */
    public function build()
    {
        $nodeType = $this->baseNode->isTrigger() ? 'trigger' : 'action';

        try {
            $this->checkConnection($nodeType);

            $applicationNode = $this->cacheRemember(['node_' . $this->baseNode->id, $nodeType . '_errors_app_node'],
                function () {
                    return $this->baseNode->applicationNode;
                }
            );

            if (!isset($applicationNode)) {
                $this->addError('critical', 'Application node does not exist.');
                Log::channel('integrations')->info('Integration '.$this->baseNode->integration_id.' ( '.$nodeType.' ). Error: Application node does not exist.');
                return;
            }

            if ($this->baseNode->isTrigger()) {
                $this->checkTrigger($applicationNode);
            } else {
                $this->checkAction($applicationNode);
            }
        }
        catch (\Throwable $e) {
            Log::channel('integrations')->info('Integration '.$this->baseNode->integration_id.' ( '.$nodeType.' ). Error: '. $e->getMessage());
            $this->addError('critical', 'Fatal Error');
        }
    }

    protected function checkConnection(string $nodeType): void
    {
        $application = $this->baseNode->application;

        $account = $this->cacheRemember(['node_'.$this->baseNode->id, $nodeType.'_errors_account'],
            function() use ($application) {
                return ApplicationRepository::getApplicationAccountByUserId($application->type, $this->baseNode->integration->user_id);
            });

        if($account) {
            $authService = ApplicationAccount::getApplicationAuthService($application->type);
            // Check if authorized
            if (!$authService->testConnection($account)) {
                $this->addError('application', __('validation.check_application_settings'));
            }
        }
    }

    protected function checkTrigger(ApplicationNode $applicationNode): void
    {
        $nodeFieldRepository = app()->make('Modules\\'.studly_case($this->baseNode->application->type).'\\Repositories\\NodeFieldRepository');
        $fieldRepository = NodeManagerFacade::load($this->baseNode)->fieldRepository();
        $settings = $this->cacheRemember(['node_'.$this->baseNode->id, 'settings'] ,function() use ($fieldRepository, $applicationNode) {
            return $fieldRepository->getAll(['actionId' => $applicationNode->action_id]);
        });

        $settingWithValues = $this->cacheRemember(['node_'.$this->baseNode->id, 'settings_with_values'], function() use ($applicationNode, $fieldRepository) {
            return $fieldRepository->getWithValuesOnly(['appNodeId' => $applicationNode->id,'actionId' => $applicationNode->action_id]);
        });

        $fields = $this->cacheRemember(['node_'.$this->baseNode->id, 'fields'],
            function() use ($applicationNode, $nodeFieldRepository) {
                return $nodeFieldRepository->getAll([
                    'appNodeId' => $applicationNode->id,
                ]);
            });
        $settingsErrors = [];
        foreach ($settings as $setting) {
            if ($setting->required)
                if (!count($settingWithValues->where('id', $setting->id)))
                    $settingsErrors[$setting->identifier] = __('validation.field_is_required', ['title' => $setting->title]);
        }
        if(count($settingsErrors))
            $this->addError('fields', $settingsErrors);

        $config = $this->getConfig($this->baseNode->application->type);
        if(!isset($config['ignore_trigger_fields_for_testing']))
            $config['ignore_trigger_fields_for_testing'] = false;

        if(!count($fields) && !$config['ignore_trigger_fields_for_testing'])
            $this->addError('fields_are_empty', __('validation.no_available_fields'));
    }

    protected function checkAction(ApplicationNode $applicationNode): void
    {
        $nodeFieldRepository = app()->make('Modules\\'.studly_case($this->baseNode->application->type).'\\Repositories\\NodeFieldRepository');
        $fields = $this->cacheRemember(['node_'.$this->baseNode->id, 'fields'],
            function() use ($applicationNode, $nodeFieldRepository) {
                return $nodeFieldRepository->getFieldsByNodeWithValues($this->baseNode->applicationNode);
            });

        if(!count($fields)) {
            $this->addError('fields_are_empty', __('validation.no_available_fields'));
            return;
        }

        $fieldsErrors = [];
        foreach ($fields as $field) {
            if ($field->required && is_null($field->value_factual))
                $fieldsErrors[$field->identifier] = __('validation.field_is_required', ['title' => $field->title]);
        }
        if (count($fieldsErrors)) {
            $this->addError('fields', $fieldsErrors);
        }
    }
}
