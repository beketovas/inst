<?php

namespace Modules\Integration\Facades;

use Modules\Integration\Contracts\BaseNode;
use Modules\Integration\Loaders\Loader as FieldLoader;

class Loader
{
    public static function create(BaseNode $baseNode)
    {
        $loader = app(FieldLoader::class);
        return $loader->create($baseNode);
    }
}
