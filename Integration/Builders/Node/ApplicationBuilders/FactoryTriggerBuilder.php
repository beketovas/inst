<?php

namespace Modules\Integration\Builders\Node\ApplicationBuilders;

use Modules\Application\Facades\ApplicationRepository;
use Modules\Integration\Builders\Node\ApplicationBuilders\TriggersBuilders\{TriggerBuilder, WebhookTriggerBuilder};
use Modules\Integration\Models\Node as NodeModel;

class FactoryTriggerBuilder
{
    static public function getInstance(NodeModel $nodeModel)
    {
        $account = ApplicationRepository::getApplicationAccount($nodeModel->application, $nodeModel->user->id);
        if($account)
            return app()->makeWith(TriggerBuilder::class, ['nodeModel' => $nodeModel]);
        else
            return app()->makeWith(WebhookTriggerBuilder::class, ['nodeModel' => $nodeModel]);
    }
}
