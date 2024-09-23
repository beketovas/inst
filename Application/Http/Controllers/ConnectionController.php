<?php

namespace Modules\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Apiway\Auth\Contracts\BaseAuth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Illuminate\Support\Facades\Log;
use Modules\Application\Contracts\AccountRepository;
use Modules\Application\Entities\Application;
use Modules\Application\Exceptions\ExistsInNodesException;
use Modules\Application\Facades\ApplicationAccount;
use Modules\Application\Facades\ApplicationRepository;
use Modules\Application\Http\Requests\StoreRequest;
use Modules\Application\Services\Savers\Saver;
use TypeError;

class ConnectionController extends Controller
{
    use ConfigHelper;

    protected Application $application;

    protected ?AccountRepository $accountRepository;

    protected object $auth;

    protected ?BaseAuth $authClient;

    public function __construct()
    {
        $this->middleware(function(Request $request, $next) {
            $slug = $request->route()->parameter('slug');
            try {
                $this->application = ApplicationRepository::getBySlug($slug);
            } catch(TypeError $e) {
                abort(404);
            }
            $this->accountRepository = ApplicationAccount::getApplicationAccountRepository($this->application->type);
            $this->auth = ApplicationAccount::getAuthConfig($this->application->type);
            $this->authClient = ApplicationAccount::getApplicationAuthService($this->application->type);
            return $next($request);
        });
    }

    public function create(string $slug)
    {
        $this->authorize('create', $this->application);
        switch ($this->auth->type) {
            case 'default':
                return view('application::connection.types.default')->with(['slug' => $slug, 'settings' => $this->auth->settings, 'application' => $this->application]);
            default:
                return view('application::connection.types.auth_form')->with(['slug' => $slug, 'settings' => $this->auth->settings, 'application' => $this->application]);
        }
    }

    public function selectAction(StoreRequest $request, string $slug): RedirectResponse
    {
        $action = $request->post('action_type');
        $id = $request->get('id');
        switch ($action) {
            case 'connect':
                return $this->store($request, $slug);
            case 'reconnect':
                if($this->auth->type != 'standard_api')
                    return $this->store($request, $slug);
                return $this->update($request, $slug, $id);
            case 'disconnect':
                return $this->disconnect($request, $slug, $id);
            default:
                return redirect()->back();
        }
    }

    public function store(StoreRequest $request, string $slug): RedirectResponse
    {
        auth()->user()->flushCache();
        $fields = $request->input('fields');

        if (is_array($fields)) {
            $basicAuthToken = $this->getBasicAuthToken($fields);
            if (!empty($basicAuthToken)) {
                $fields['basic_auth_token'] = $basicAuthToken;
            }
        }

        $saver = Saver::create($this->auth->type, $this->getApiConfig($this->application->type, 'auth'));
        if(is_null($saver))
            return redirect()->route('application.attached');

        $urlRequest = $saver->save($slug, $fields);
        return redirect($urlRequest->getUrl())->withCookies($urlRequest->getCookie())->send();
    }

    public function edit(string $slug, int $id)
    {
        $account = $this->accountRepository->getById($id);
        $this->authorize('change', [$account, $this->application]);

        switch ($this->auth->type) {
            case 'default':
                return view('application::connection.types.default')->with(['slug' => $slug, 'account' => $account, 'settings' => $this->auth->settings,  'authType' => $this->auth->type, 'application' => $this->application]);
            default:
                return view('application::connection.types.auth_form')->with(['slug' => $slug, 'account' => $account, 'settings' => $this->auth->settings,  'authType' => $this->auth->type, 'application' => $this->application]);
        }
    }

    public function update(StoreRequest $request, string $slug, int $id): RedirectResponse
    {
        $account = $this->accountRepository->getById($id);

        $this->authorize('change', [$account, $this->application]);

        $fieldsWithParams = $request->input('fields');

        $basicAuthToken = $this->getBasicAuthToken($fieldsWithParams);
        if (!empty($basicAuthToken)) {
            $fieldsWithParams['basic_auth_token'] = $basicAuthToken;
        }

        $fieldsWithParams['account_data_json'] = json_encode($fieldsWithParams);

        $this->accountRepository->update($fieldsWithParams, $account);

        // Flush user's cache
        $account->user->flushCache();

        return redirect()->route('application.edit', [$slug, $account->id])->with('form-status', __('application::site.updated', ['app_name' => $this->application->name]));
    }

    public function disconnect(Request $request, string $slug, int $id): RedirectResponse
    {
        $account = $this->accountRepository->getById($id);
        try {
            $this->authorize('change', [$account, $this->application]);
        } catch (AuthorizationException $e) {
        }
        if(!isset($this->authClient))
            return redirect()
                ->back()
                ->with('form-error','Oops... Error occurred.');

        try {
            $this->authClient->disconnect($account);
            $this->cacheForget(['applications', 'applications_user_'.auth()->user()->id], ['user_data_'.auth()->user()->id, 'application_account_'.$this->application->type]);
            return redirect()
                ->route('applications')
                ->with('form-status', __(
                    'application::site.disconnected_successfully'
                ));
        } catch (ExistsInNodesException $e) {
            return redirect()
                ->back()
                ->with('form-error', __(
                    'application::site.you_have_integrations_with_this_account_delete_them'
                ));
        }
        catch (TypeError $e) {
            $this->cacheForget(['applications', 'applications_user_'.auth()->user()->id], ['user_data_'.auth()->user()->id, 'application_account_'.$this->application->type]);
            Log::channel($this->application->type)->warning('Account: '. $id. ' not found during disconnect.');
            return redirect()
                ->route('applications')
                ->with('form-status', __(
                    'application::site.disconnected_successfully'
                ));
        }
    }

    public function testConnection(string $slug, int $id): RedirectResponse
    {
        if (!isset($this->authClient))
            return redirect()
                ->back()
                ->with('form-error', 'Oops... Error occurred.');

        $account = $this->accountRepository->getById($id);
        if (!$account)
            abort(404);

        if ($this->authClient->testConnection($account)) {
            return redirect()->route('application.edit', [$slug, $account->id])->with('form-status', __('applications.test_for_connection_passed_successfully'));
        }
        return redirect()->route('application.edit', [$slug, $account->id])->with('form-error', __('applications.test_for_connection_failed_check_settings'));
    }

    public function testConnectionJS(string $slug, int $id): JsonResponse
    {
        if(!isset($this->authClient))
            return response()->json(['test_connection' => false]);

        $account = ApplicationRepository::getApplicationAccount($this->application, auth()->user()->id);
        try {
            if ($this->authClient->testConnection($account)) {
                return response()->json(['test_connection' => true]);
            }
        }
        catch (TypeError $e) {
            $this->cacheForget(['applications', 'applications_user_'.auth()->user()->id], ['user_data_'.auth()->user()->id, 'application_account_'.$this->application->type]);
            Log::channel($this->application->type)->warning('Account: '. $id. ' not found during testing.');
            return response()->json(['test_connection' => false]);
        }
        return response()->json(['test_connection' => false]);
    }

    public function disconnectJS(Request $request, string $slug, int $id): JsonResponse
    {
        $account = $this->accountRepository->getById($id);

        try {
            $this->authorize('change', [$account, $this->application]);
        } catch (AuthorizationException $e) {
        }

        if(!isset($this->authClient))
            return response()->json(['disconnect' => 'Oops... Error occurred.']);

        try {
            $this->authClient->disconnect($account);
            $this->cacheForget(['applications', 'applications_user_'.auth()->user()->id], ['user_data_'.auth()->user()->id, 'application_account_'.$this->application->type]);
            return response()->json(['disconnect' => true]);
        } catch (ExistsInNodesException $e) {
            return response()->json(['disconnect' => __(
                'application::site.you_have_integrations_with_this_account_delete_them')]);
        }
        catch (TypeError $e) {
            $this->cacheForget(['applications', 'applications_user_'.auth()->user()->id], ['user_data_'.auth()->user()->id, 'application_account_'.$this->application->type]);
            Log::channel($this->application->type)->warning('Account: '. $id. ' not found during disconnect.');
            return response()->json(['disconnect' => true]);
        }
    }

    public function redirectToApplicationEdit(Request $request): RedirectResponse
    {
        $user = $request->user();
        if(!$user)
            abort(401);
        if(!$this->application->hasAccount())
            return redirect()->route('application.create', $this->application->slug);
        if($account = $this->application->account($user->id))
            return redirect()->route('application.edit', ['slug' => $this->application->slug, 'id' => $account->id]);
        return redirect()->route('application.create', $this->application->slug);
    }

    protected function getBasicAuthToken(array $fieldsWithParams): string
    {
        $authSettingsArray = (array) $this->auth;
        $basicAuthToken = '';

        if (isset($authSettingsArray['basic_auth_token'])) {
            $basicAuthToken = $authSettingsArray['basic_auth_token'];

            foreach ($fieldsWithParams as $key => $val) {
                $basicAuthToken = str_replace('{{' . $key . '}}', $val, $basicAuthToken);
            }
        }

        return base64_encode($basicAuthToken);
    }
}
