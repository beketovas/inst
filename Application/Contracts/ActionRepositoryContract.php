<?php declare(strict_types=1);

namespace Modules\Application\Contracts;

interface ActionRepositoryContract
{
    public function actionsCount(): int;

    public function triggersCount(): int;
}
