<?php declare(strict_types=1);

namespace Modules\Application\Services\Savers;

use Modules\Application\Builders\RequestUrlBuilder;
use Modules\Application\Contracts\Saver;
use Modules\Application\Facades\ApplicationAccount;
use Modules\Application\Facades\ApplicationRepository;
use Symfony\Component\HttpFoundation\Cookie;

class Oauth2Saver implements Saver
{
    protected RequestUrlBuilder $requestUrlBuilder;

    public function __construct(RequestUrlBuilder $requestUrlBuilder)
    {
        $this->requestUrlBuilder = $requestUrlBuilder;
    }

    public function save(string $slug, ?array $fields = []): RequestUrlBuilder
    {
        $application = ApplicationRepository::getBySlug($slug);
        $authClient = ApplicationAccount::getApplicationAuthService($application->type);
        $this->requestUrlBuilder->addUrl($authClient->sendAuthorizeRequest($fields)->getTargetUrl());
        if($fields) {
            $cookie = Cookie::create('fields', json_encode($fields));
            $this->requestUrlBuilder->addCookie($cookie);
        }
        return $this->requestUrlBuilder;
    }
}
