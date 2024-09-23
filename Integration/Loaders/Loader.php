<?php

namespace Modules\Integration\Loaders;

use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Modules\Integration\Contracts\BaseNode;
use Modules\Integration\Facades\NodeManagerFacade;
use Modules\Integration\Loaders\Fields\FieldsLoader;
use Modules\Integration\Loaders\Fields\TriggerFieldsLoader;

class Loader
{
    use ConfigHelper;

    public function create(BaseNode $baseNode)
    {
        $appNode = $baseNode->applicationNode;
        $fieldsConfig = $this->getApiConfig($baseNode->application->type, 'fields_list');
        if($baseNode->isTrigger())
            if(!isset($fieldsConfig['trigger']))
                $fieldsLoader  = NodeManagerFacade::load($baseNode)->fieldsLoader($appNode->action->type);
            else
                $fieldsLoader = app(TriggerFieldsLoader::class, ['node' => $baseNode]);
        else {
            if(!isset($fieldsConfig))
                $fieldsLoader  = NodeManagerFacade::load($baseNode)->fieldsLoader($appNode->action->type);
            else
                $fieldsLoader = app(FieldsLoader::class, ['node' => $baseNode]);
        }
        return $fieldsLoader;
    }
}
