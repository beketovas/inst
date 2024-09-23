<?php

namespace Modules\Instagram\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class FieldServiceException extends Exception
{
    public function report()
    {
        Log::error($this->getMessage());
    }
}