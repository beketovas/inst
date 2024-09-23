<?php

namespace Modules\Application\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class AccountExistsException extends Exception
{
    public function report()
    {
    }
}
