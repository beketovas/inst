<?php

namespace Modules\Integration\Services;

use App\Traits\CacheBuilder;
use Illuminate\Support\Facades\Log;
use Modules\Integration\Contracts\BaseNode;
use Modules\Integration\Facades\ApplicationNodeManagerFacade;
use Modules\Integration\Facades\NodeManagerFacade;
use Modules\Synchronization\Exceptions\ExternalServerErrorException;

class TestIntegration
{
    use CacheBuilder;

    protected BaseNode $triggerNode;
    protected BaseNode $actionNode;

    public function __construct(BaseNode $triggerNode, BaseNode $actionNode)
    {
        $this->triggerNode = $triggerNode;
        $this->actionNode = $actionNode;

    }

    public function execute(): bool
    {
        if(!class_exists('Modules\\'.studly_case($this->actionNode->application->type).'\\Repositories\\NodeFieldRepository'))
            return true;
        $fieldRepository = $this->nodeFieldRepository = app('Modules\\'.studly_case($this->actionNode->application->type).'\\Repositories\\NodeFieldRepository');
        $nodeFieldRepository = NodeManagerFacade::load($this->triggerNode)->nodeFieldRepository();

        $fields = $this->cacheRemember(['node_'.$this->actionNode->id, 'fields'], function() use ($fieldRepository) {
            return $fieldRepository->getFieldsByNodeWithValues($this->actionNode->applicationNode);
        });

        if(!method_exists($nodeFieldRepository, 'getWithExampleValue'))
            return true;

        $dataForExecute = $this->cacheRemember(['node_'.$this->actionNode->id, 'fields_with_example'], function() use ($fields, $nodeFieldRepository) {
            return $nodeFieldRepository->getWithExampleValue($fields);
        });

        $actionNodeManager = ApplicationNodeManagerFacade::load($this->actionNode->applicationNode);
        $executor = $actionNodeManager->createExecutor($this->actionNode);

        Log::channel('synchronization')->info(__('site.log_section_separator'));

        try {
            $result = $executor->performAction($dataForExecute);
        } catch (\Throwable $e) {
            return false;
        }

        Log::channel('synchronization')->info(__('site.log_section_separator'));

        if(isset($result))
            return true;
        return false;
    }
}
