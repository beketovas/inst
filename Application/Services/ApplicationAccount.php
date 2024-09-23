<?php declare(strict_types=1);

namespace Modules\Application\Services;

use Apiway\Auth\Contracts\BaseAuth;
use Apiway\Auth\Oauth2\Oauth2Auth;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Application\Contracts\AccountRepository as AccountRepositoryContract;
use Modules\Application\Repositories\AccountRepository;

class ApplicationAccount
{
    use ConfigHelper;

    public function getApplicationAccountRepository(string $type): ?AccountRepositoryContract
    {
        try {
            $repository = null;
            if(class_exists("Modules\\" . studly_case($type) . "\\Entities\\Account"))
                $repository = app()->make("Modules\\" . studly_case($type) . "\\Repositories\\AccountRepository");
            else
                $repository = app()->make(AccountRepository::class);
            return $repository;
        } catch (BindingResolutionException $e) {
            return null;
        }
    }

    public function getAuthConfig(string $type): object
    {
        $configAlias = preg_replace('/[^\p{L}\p{N}\s]/u', '', $type);
        $authConfig = config($configAlias.'.auth');
        if(!isset($authConfig['type']))
             $authConfig['type'] = 'default';
        if(!isset($authConfig['settings']))
            $authConfig['settings'] = null;

        return (object)$authConfig;
    }

    public function getApplicationAuthService(string $type): ?BaseAuth
    {
        if(!isset($this->getConfig($type, 'auth')['type']) || $this->getConfig($type, 'auth')['type'] == 'default')
            return null;
        try {
            if(class_exists("Modules\\" . studly_case($type) . "\\Services\\AuthService"))
                return app()->make("Modules\\" . studly_case($type) . "\\Services\\AuthService");
            else
                return new Oauth2Auth($this->getApiConfig($type, 'auth'));
        } catch (BindingResolutionException $e) {
            return null;
        }
    }
}
