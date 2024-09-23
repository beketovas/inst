<?php


namespace Modules\Integration\Services;


use App\Traits\CacheBuilder;

class CacheService
{
    use CacheBuilder;

    public function getIfExistOrAddDataToCache(array $key, $data)
    {
        return $this->cacheRemember($key, function() use ($data) { return $data; });
    }
}
