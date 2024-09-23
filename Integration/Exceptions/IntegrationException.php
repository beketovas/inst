<?php

namespace Modules\Integration\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class IntegrationException extends Exception
{
    public function report()
    {
        Log::error($this->getMessage());
    }
}