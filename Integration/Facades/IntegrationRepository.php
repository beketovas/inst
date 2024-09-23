<?php declare(strict_types=1);

/**
 * @author Stanislav Taranovskyi <staranovskyi@gmail.com>
 */

namespace Modules\Integration\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Modules\Integration\Entities\Integration;

/**
 * @method static Integration getById(int $id)
 * @method static Collection getAll(array $parameters, $nbrPages = null)
 * @method static Collection getInactive()
 * @method static void destroy(Integration $integration)
 * @method static Collection getByAppAndAccount(int $applicationId, int $accountId)
 * @method static Collection getByApplicationType(string $applicationType)
 * @method static Collection deleteByApplicationType(string $applicationType)
 */
class IntegrationRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'integration_repository';
    }
}
