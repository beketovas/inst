<?php

namespace Modules\Integration\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class SyncFailedException extends Exception
{
    public function report()
    {
    }
}
