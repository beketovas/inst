<?php

namespace Modules\Instagram\Exceptions\Auth;

use Exception;
use Illuminate\Support\Facades\Log;

class TooManyAttemptsPerDayException extends Exception
{
    public function report()
    {
    }
}
