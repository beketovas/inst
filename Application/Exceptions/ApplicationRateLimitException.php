<?php

namespace Modules\Application\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApplicationRateLimitException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if(empty($message))
            $message = "Try attempt again because of api rate limits.";
        parent::__construct($message, $code, $previous);
    }

    public function report()
    {
        Log::error($this->getMessage());
    }
}
