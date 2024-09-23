<?php declare(strict_types=1);

namespace Modules\Application\Managers;

use Modules\Application\Contracts\ActionRepositoryContract;
use Modules\Application\Contracts\ApplicationManagerContract;

class ApplicationManager implements ApplicationManagerContract
{
    /**
     * @param string $appType
     * @return ActionRepositoryContract|null
     */
    public function actionRepository(string $appType): ?ActionRepositoryContract
    {
        return app()->make(ActionRepositoryContract::class, ['app_type' => $appType]);
    }
}
