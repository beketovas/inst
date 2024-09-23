<?php

namespace Modules\Application\Facades;

use Apiway\Auth\Contracts\BaseAuth;
use Illuminate\Support\Facades\Facade;
use Modules\Application\Contracts\AccountRepository;

/**
 * @method static AccountRepository getApplicationAccountRepository(string $type)
 * @method static object getAuthConfig(string $type)
 * @method static BaseAuth getApplicationAuthService(string $type)
 */
class ApplicationAccount extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'application_account';
    }
}
