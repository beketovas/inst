<?php

namespace Modules\Integration\Contracts;

use Apiway\ServicesDataStorage\DataStorage;

/**
 * Interface Sender
 */
interface Sender
{
    public function getData() : DataStorage;
}
