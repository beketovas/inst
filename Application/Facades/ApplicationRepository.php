<?php

namespace Modules\Application\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Application\Contracts\AccountModelContract;
use Modules\Application\Entities\Application;

/**
 * @method static Application getById(int $id)
 * @method static Application getBySlug(string $slug)
 * @method static Application getByType(string $type)
 * @method static Application getApplicationSoft(Application $application)
 * @method static AccountModelContract getApplicationAccount(Application $application, int $userId)
 * @method static AccountModelContract getApplicationAccountByUserId(string $type, int $userId)
 */
class ApplicationRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'application_repository';
    }
}
