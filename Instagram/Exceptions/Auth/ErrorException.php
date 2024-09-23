<?php

namespace Modules\Instagram\Exceptions\Auth;

use Exception;
use Illuminate\Support\Facades\Log;

class ErrorException extends Exception
{
    public function report()
    {
    }
}
