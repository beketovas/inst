<?php

namespace Modules\Integration\Builders\Node\ApplicationBuilders;

use Modules\Integration\Builders\Node\ApplicationBuilders\ErrorsBuilders\{ErrorsBuilder, WebhookErrorsBuilder};
use Modules\Application\Facades\ApplicationRepository;
use Modules\Integration\Entities\Node as NodeModel;

class FactoryErrorBuilder
{
    static public function getInstance(NodeModel $nodeModel)
    {
        $account = ApplicationRepository::getApplicationAccount($nodeModel->application, $nodeModel->integration->user_id);
        if($account)
            return app()->makeWith(ErrorsBuilder::class, ['baseNode' => $nodeModel]);
        else
            return app()->makeWith(WebhookErrorsBuilder::class, ['baseNode' => $nodeModel]);
    }
}
