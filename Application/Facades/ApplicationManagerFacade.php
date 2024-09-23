<?php

namespace Modules\Application\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Application\Contracts\ActionRepositoryContract;

/**
 * @method static ActionRepositoryContract actionRepository(string $appType)
 */
class ApplicationManagerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'application_manager';
    }
}
