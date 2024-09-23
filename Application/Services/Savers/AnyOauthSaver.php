<?php declare(strict_types=1);

namespace Modules\Application\Services\Savers;

use Modules\Application\Builders\RequestUrlBuilder;
use Modules\Application\Contracts\Saver;

class AnyOauthSaver implements Saver
{
    protected RequestUrlBuilder $requestUrlBuilder;

    public function __construct(RequestUrlBuilder $requestUrlBuilder)
    {
        $this->requestUrlBuilder = $requestUrlBuilder;
    }

    public function save(string $slug, ?array $fields = []): RequestUrlBuilder
    {
        $this->requestUrlBuilder->addUrl(route('application.'.$slug.'.attach', $fields));
        return $this->requestUrlBuilder;
    }
}
