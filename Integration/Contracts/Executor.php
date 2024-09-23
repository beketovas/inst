<?php

namespace Modules\Integration\Contracts;

use Apiway\ServicesDataStorage\DataStorage;

interface Executor
{
    public function performAction(DataStorage $dataStorage);
}
