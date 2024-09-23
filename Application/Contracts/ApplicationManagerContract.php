<?php

namespace Modules\Application\Contracts;

interface ApplicationManagerContract
{
    public function actionRepository(string $appType): ?ActionRepositoryContract;
}
