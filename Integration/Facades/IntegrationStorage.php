<?php

namespace Modules\Integration\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Integration\Entities\Integration as IntegrationEntity;
use Modules\Integration\Storage\Integration;

class IntegrationStorage extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'integration_storage.singleton';
    }

    /**
     * Load helper with integration
     *
     * @param IntegrationEntity $integration
     * @return Integration
     */
    public static function load(IntegrationEntity $integration) {
        $syncHelper = app()->makeWith(Integration::class, ['integration' => $integration]);

        return $syncHelper;
    }
}
