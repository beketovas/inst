<?php declare(strict_types=1);

namespace Modules\Application\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Application\Entities\Application;
use Modules\Application\Facades\ApplicationAccount;
use Modules\Application\Facades\ApplicationRepository;
use Nwidart\Modules\Routing\Controller;
use Exception;
use TypeError;

class DisconnectController extends Controller
{
    protected Application $application;

    public function __construct()
    {
        $this->middleware(function(Request $request, $next) {
            $slug = $request->route()->parameter('slug');

            try {
                $this->application = ApplicationRepository::getBySlug($slug);
            } catch(TypeError $e) {
                abort(404);
            }

            return $next($request);
        });
    }

    public function disconnect(Request $request)
    {
        if (!auth()->guard()->check()) {
            return redirect(route('login'))
                ->cookie('redirect_to_disconnect', json_encode(['application' => $this->application->slug]), config('app.cookie.applications_auth'))
                ->send();
        }

        if ((isset($_COOKIE['redirect_to_disconnect']) && json_decode($_COOKIE['redirect_to_disconnect'], true)['application'] == $this->application->slug)
            || config(str_replace('_', '', $this->application->type) . '.app_url') == $request->header('referer')
        ) {
            $userId = auth()->user()->id;
            $accountRepository = ApplicationAccount::getApplicationAccountRepository($this->application->type);
            $account = $accountRepository->getByTypeAndUserId($this->application->type, $userId);

            try {
                $accountRepository->destroy($account);
                $account->user->flushCache();
                Log::channel('auth')->info('App uninstalled in ' . $this->application->name . ', so we delete it too.');
            } catch (Exception $e) {
                Log::channel('auth')->info('App uninstalled in ' . $this->application->name . ', but we could not uninstall it because of exception.');
            }

            return redirect()->route('applications');
        } else {
            abort(404);
        }
    }
}
