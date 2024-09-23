<?php declare(strict_types=1);

namespace Modules\Integration\Traits;

use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Application\Facades\ApplicationRepository;

trait ApplicationAuthorization
{
    use ConfigHelper;

    public function processUnauthenticated(array $params, string $app): RedirectResponse
    {
        if(!$params)
            return redirect(route('login'))->send();
        $authCookie = [
            'params' => $params,
            'application' => $app
        ];

        return redirect(route('login'))->cookie('auth_in_process', json_encode($authCookie), config('app.cookie.applications_auth'))->send();
    }

    public function processAuthUrl(Request $request): ?string
    {
        $authInProcess = $_COOKIE['auth_in_process'] ?? null;
        if(!$authInProcess)
            return null;

        $authInProcessDecoded = json_decode($authInProcess, true);
        $params = http_build_query($authInProcessDecoded['params']);
        $slug = Str::slug($authInProcessDecoded['application']);
        $url = "/app/applications-attached/{$slug}/process-authorization";
        return $url."?{$params}";
    }

    public function processAuthFromSoft(Request $request): ?string
    {
        $authFromSoft = $_COOKIE['auth_from_soft'] ?? null;
        if(!$authFromSoft)
            return null;

        $authInProcessDecoded = json_decode($authFromSoft, true);
        $application = ApplicationRepository::getBySlug($authInProcessDecoded['application']);
        $account = ApplicationRepository::getApplicationAccount($application, auth()->user()->id);
        return $account ? route('application.edit', ['slug' => $authInProcessDecoded['application'], 'id' => $account->id])
            : route('application.create', ['slug' => $authInProcessDecoded['application']]);
    }

    public function processAuthCommunityUrl(): ?string
    {
        if(isset($_COOKIE['redirect_to_community']))
            return $_COOKIE['redirect_to_community'];
        return null;
    }

    public function getUrlAfterAuth(Request $request, ?string $defaultUrl): ?string
    {
        $redirectToCommunity = $this->processAuthCommunityUrl();
        if(!is_null($redirectToCommunity))
            return $redirectToCommunity;
        $disconnectSlug = $this->getDisconnectSlug();
        if ($disconnectSlug)
            return route('application.disconnect', [$disconnectSlug]);

        $processAuthUrl = $this->processAuthUrl($request);
        $authFromSoft = $this->processAuthFromSoft($request);
        $redirectUrlAfterAuth = $processAuthUrl ?? $authFromSoft;

        return $redirectUrlAfterAuth ?? $defaultUrl;
    }

    protected function getDisconnectSlug(): ?string
    {
        if (isset($_COOKIE['redirect_to_disconnect']))
            return json_decode($_COOKIE['redirect_to_disconnect'], true)['application'];

        return null;
    }
}
