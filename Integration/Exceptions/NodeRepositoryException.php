<?php

namespace Modules\Integration\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class NodeRepositoryException extends Exception
{
    public function report()
    {
        Log::error($this->getMessage());
    }
}