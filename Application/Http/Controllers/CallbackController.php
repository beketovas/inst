<?php declare(strict_types=1);

namespace Modules\Application\Http\Controllers;

use Exception;
use Apiway\Auth\Oauth2\Contracts\ProcessAuthorization;
use Apiway\Auth\Oauth2\Contracts\Verifiable;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Application\Facades\ApplicationAccount;
use Modules\Application\Facades\ApplicationRepository;

class CallbackController extends Controller
{
    use ConfigHelper;

    public function receive(Request $request, string $slug)
    {
        $application = ApplicationRepository::getBySlug($slug);
        if(is_null($application))
            abort(404);
        $user = $request->user();
        $response = app(ProcessAuthorization::class)->processAuthorization($request->all(), $application, $user);
        $redirect = redirect();
        if($res = $response->getError())
            $redirect = redirect($res->url)
                ->with('form-error', __('application::site.not_connected_because_of_message', ['message' => $res->message]));

        else if($res = $response->getResponse())
                $redirect = redirect($res->url)
                    ->with('form-status', __('application::site.connected_successfully'));

        else if($res = $response->getResponseAfterAuth())
            $redirect = redirect($res->url);

        $cookies = $res->cookies ?? [];
        foreach ($cookies as $key => $val)
            $redirect = $redirect->cookie($key, $val, 5);

        return $redirect->send();
    }

    public function uninstall(Request $request, string $slug): JsonResponse
    {
        $application = ApplicationRepository::getBySlug($slug);
        $accountRepository = ApplicationAccount::getApplicationAccountRepository($application->type);
        $verificationClass = app(Verifiable::class, ['app_type' => $application->type]);
        $verified = $verificationClass ? $verificationClass->verify($request->all()) : true;
        if (!$verified) {
            return response()->json(['data' => 'Not verified']);
        }

        $config = $this->getApiConfig($application->type, 'auth');
        if (!isset($config['third_party_uninstall'])) {
            return response()->json(['data' => 'error']);
        }

        if (!isset($config['third_party_uninstall']['path_to_identification_data_in_request'])) {
            return response()->json(['data' => 'error']);
        }

        $requestData = $request->all();
        $identificationDataInRequest = $requestData;
        $path = $config['third_party_uninstall']['path_to_identification_data_in_request'];

        if (isset($config['third_party_uninstall']['parse_function'])) {
            foreach ($path as $step) {
                $identificationDataInRequest = $config['third_party_uninstall']['parse_function']($identificationDataInRequest[$step]);
            }
        } else {
            foreach ($path as $step) {
                $identificationDataInRequest = $identificationDataInRequest[$step];
            }
        }

        if (!isset($config['third_party_uninstall']['path_to_identification_data_in_db'])) {
            return response()->json(['data' => 'error']);
        }

        $pathToData = $config['third_party_uninstall']['path_to_identification_data_in_db'];
        $account = $accountRepository->getByDataInJsonField($pathToData, $identificationDataInRequest)->first();

        try {
            $accountRepository->destroy($account);
            $account->user->flushCache();
            Log::channel('auth')->info('App uninstalled in '.$application->name.', so we delete it too.');
            return response()->json(['data' => 'success']);
        } catch (Exception $e) {
            Log::channel('auth')->info('App uninstalled in '.$application->name.', but we could not uninstall it because of exception.');
            return response()->json(['data' => 'error']);
        }
    }
}
