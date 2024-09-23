<?php declare(strict_types=1);

namespace Modules\Application\Services\Savers;

use Modules\Application\Contracts\Saver as SaverContract;

class Saver
{
    static public function create(string $authType, ?array $authConfig = []): ?SaverContract
    {
        switch($authType) {
            case 'oauth':
                return app(AnyOauthSaver::class);
            case 'oauth2':
                if(empty($authConfig))
                    return app(AnyOauthSaver::class);
                return app(Oauth2Saver::class);
            case 'standard_api':
                return app(StandardApiSaver::class);
            default:
                return null;
        }
    }
}
