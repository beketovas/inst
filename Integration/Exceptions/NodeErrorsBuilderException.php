<?php

namespace Modules\Integration\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class NodeErrorsBuilderException extends Exception
{
    public function report()
    {
        Log::error($this->getMessage());
    }
}