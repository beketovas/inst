<?php

namespace Modules\Integration\Http\Controllers;

use Apiway\IntegrationLog\Events\IntegrationChanged;
use App\Traits\CacheBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Modules\Application\Entities\Application;
use Modules\Application\Facades\ApplicationRepository;
use Modules\Integration\Events\AfterApplicationChange;
use Modules\Integration\Exceptions\IntegrationException;
use Modules\Integration\Facades\IntegrationStorage;
use Modules\Integration\Http\Resources\SearchIntegrationResource;
use Modules\Integration\Services\NodeCreator;
use Modules\User\Repositories\UserRepository;
use Nwidart\Modules\Routing\Controller;
use Modules\Integration\Repositories\IntegrationRepository;
use Modules\Integration\Repositories\NodeRepository;
use Modules\Integration\Contracts\IntegrationServiceContract;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Helpers\DataTransfersHelper;

class IntegrationController extends Controller
{
    use CacheBuilder;

    /**
     * @var IntegrationRepository
     */
    protected $integrationRepository;

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * IntegrationController constructor.
     *
     * @param IntegrationRepository $integrationRepository
     * @param NodeRepository $nodeRepository
     */
    public function __construct(
        IntegrationRepository $integrationRepository,
        NodeRepository $nodeRepository,
        UserRepository $userRepository
    )
    {
        $this->integrationRepository = $integrationRepository;
        $this->nodeRepository = $nodeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $page = $request->get('page');
        if(!isset($page))
            $page = 1;
        // billing helper - get user data transfers by user id
        $aDataTransfers = DataTransfersHelper::getDataTransfersByUser(auth()->user());
        $sShowIntegration = DataTransfersHelper::showIntegrationByUser(auth()->user());
        $integrations = $this->integrationRepository->getIntegrationsByUser(auth()->user()->id, $page, config ("app.nbrPages.front.integrations"));
        $content = view('integration::index', compact('integrations', 'aDataTransfers', 'sShowIntegration'))->render();
        $response = new Response($content);
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        return $response;
    }

    public function indexJS()
    {
        $user = auth()->user();
        if(method_exists($user, 'flushCache')) {
            $user->flushCache();
        }
        $integrations = $this->integrationRepository->getByUserId($user->id);
        return response()->json(['data' => SearchIntegrationResource::collection($integrations)])->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Create an integration.
     *
     * @param \Illuminate\Http\Request $request
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request)
    {
        // Store integration
        $attributes = $request->all();
        $attributes['code'] = uniqid();
        $integration = $this->integrationRepository->store($attributes, $request->user()->id);
        $this->authorize('manage', $integration);
        // Save trigger node
        $this->nodeRepository->store([
            'integration_id' => $integration->getAttribute('id'),
            'ordering' => 1
        ]);
        // Save action node
        $this->nodeRepository->store([
            'integration_id' => $integration->getAttribute('id'),
            'ordering' => 2
        ]);

        $integration->flushCache();
        return redirect()->route('integrations.nodes', [$integration->getAttribute('code')]);
    }

    public function createWithApplications(Request $request): RedirectResponse
    {
        $user = $request->user();

        $apps = $request->validate([
            'trigger' => 'required|string',
            'action' => 'required|string'
        ], $request->all());
        $trigger = ApplicationRepository::getByType($apps['trigger']);
        $action = ApplicationRepository::getByType($apps['action']);

        // Store integration
        $attributes = $request->all();
        $attributes['code'] = uniqid();
        $integration = $this->integrationRepository->store($attributes, $user->id);
        $this->authorize('manage', $integration);

        $nodeCreator = app(NodeCreator::class, ['user' => $user, 'integration' => $integration]);

        $triggerNode = $nodeCreator->createNodeWithApplication(1, $trigger);
        //if trigger node was created with an application then create action node with an app.
        if($triggerNode == 1) {
            $actionNode = $nodeCreator->createNodeWithApplication(2, $action);
        }
        else {//else create empty action node
            $this->nodeRepository->store([
                'integration_id' => $integration->getAttribute('id'),
                'ordering' => 2
            ]);
        }

        $integration->flushCache();
        return redirect()->route('integrations.nodes', [$integration->getAttribute('code')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param string $code
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Request $request, string $code)
    {
        $integration = $this->integrationRepository->getByCode($code);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $code);
            return back()->with('status',__('integration::site.integration_has_errors'));
        }

        $this->authorize('manage', $integration);

        $integrationStorage = IntegrationStorage::load($integration);

        $this->integrationRepository->destroy($integration);
        $integration->flushCache();
        $this->cacheForget(['applications', 'applications_user_'.$request->user()->id]);

        event(new IntegrationChanged($integrationStorage, 'deleted', 'user'));

        return back()->with('status',__('integration::site.integration_deleted'));
    }

    /**
     * Activate integration
     *
     * @param string $code
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Modules\Integration\Exceptions\IntegrationException
     */
    public function activateJS(string $code)
    {
        $integration = $this->integrationRepository->getByCode($code);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $code);
            return response()->json(['error' => __('integration::site.integration_has_errors')]);
        }

        $this->authorize('manage', $integration);

        Log::channel('integrations')->info(__('site.log_section_separator'));
        Log::channel('integrations')->info('User with id '.$integration->user_id.' tries to activate integration '.$integration->id.'.');
        $nodes = $integration->nodes;

        $user = $this->userRepository->getById($integration->user_id);
        $bIsActiveStripeStatus = $user->isActive();
        if(!$bIsActiveStripeStatus) {
            Log::channel('integrations')->warning('Attempt to activate way-integration in the absence of Data Transfer.');
            return back()->with(['error' => __('integration::site.try_activate_integration_with_no_transfer')]);
        }

        if(!count($nodes)) {
            Log::channel('integrations')->warning('It does not have nodes.');
            return response()->json(['error' => __('integration::site.integration_has_no_nodes'), 'lastIntegration' => $integration]);
        }

        $integrationService = app()->makeWith(IntegrationServiceContract::class, [
            'integration' => $integration
        ]);
        $errors = $integrationService->hasErrors();
        if($errors) {
            $integration->flushCache();
            Log::channel('integrations')->warning('Integration '.$integration->id.' has errors. Errors by nodes: '.print_r($errors, true));
            return response()->json(['error' => __('integration::site.integration_has_errors'), 'lastIntegration' => $integration]);
        }

        // Try to find a mirror integration
        if(config('integration.check_mirror')) {
            $mirrorIntegration = $this->integrationRepository->activeMirror($integration);
            if ($mirrorIntegration) {
                $integration->flushCache();
                Log::channel('integrations')->warning('Integration ' . $integration->id . ' already has mirror integration' . $mirrorIntegration->id);
                $mirrorIntegrationLink = route('integrations.nodes', ['integration_id' => $mirrorIntegration->id]);
                return response()->json(['error' => __('integration::site.integration_has_mirror_integration', ['link' => $mirrorIntegrationLink]), 'lastIntegration' => $integration]);
            }
        }

        // Try to find a similar integration
        if(config('integration.check_similar')) {
            $similarIntegration = $this->integrationRepository->activeSimilar($integration);
            if ($similarIntegration) {
                $integration->flushCache();
                Log::channel('integrations')->warning('Integration ' . $integration->id . ' already has similar integration ' . $similarIntegration->id);
                $similarIntegrationLink = route('integrations.nodes', ['integration_id' => $similarIntegration->id]);
                return response()->json(['error' => __('integration::site.integration_has_similar_integration', ['link' => $similarIntegrationLink]), 'lastIntegration' => $integration]);
            }
        }

        try {
            $integration->flushCache();
            $activated = $integrationService->activate();
            if($activated) {
                Log::channel('integrations')->info('Integration activated.');
                return response()->json(['active' => true, 'integration' => $integration]);
            } else {
                Log::channel('integrations')->warning('Integration is not activated. Watch webhooks log.');
                return response()->json(['error' => __('integration::site.integration_is_not_activated'), 'lastIntegration' => $integration]);
            }
        } catch (IntegrationException $e) {
            Log::channel('integrations')->warning('Error: '. $e->getMessage());
            Log::channel('integrations')->warning('Integration is not activated.');
            return response()->json(['error' => $e->getMessage(), 'lastIntegration' => $integration]);
        }
    }

    /**
     * Activate integration
     *
     * @param Request $request
     * @param string $code
     * @return RedirectResponse
     * @throws IntegrationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function activate(Request $request, string $code)
    {

        $integration = $this->integrationRepository->getByCode($code);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $code);
            return back()->with(['error' => __('integration::site.integration_has_errors')]);
        }

        $this->authorize('manage', $integration);

        Log::channel('integrations')->info(__('site.log_section_separator'));
        Log::channel('integrations')->info('User with id '.$integration->user_id.' tries to activate integration '.$integration->id.'.');
        $nodes = $integration->nodes;

        $user = $this->userRepository->getById($integration->user_id);
        $bIsActiveStripeStatus = $user->isActive();
        if(!$bIsActiveStripeStatus) {
            Log::channel('integrations')->warning('Attempt to activate way-integration in the absence of Data Transfer.');
            return back()->with(['error' => __('integration::site.try_activate_integration_with_no_transfer')]);
        }

        if(!count($nodes)) {
            Log::channel('integrations')->warning('It does not have nodes.');
            return back()->with(['error' => __('integration::site.integration_has_no_nodes'), 'lastIntegration' => $integration]);
        }

        $integrationService = app()->makeWith(IntegrationServiceContract::class, [
            'integration' => $integration
        ]);
        $errors = $integrationService->hasErrors();
        if($errors) {
            $integration->flushCache();
            Log::channel('integrations')->warning('Integration '.$integration->id.' has errors. Errors by nodes: '.print_r($errors, true));
            return back()->with(['error' => __('integration::site.integration_has_errors'), 'lastIntegration' => $integration]);
        }

        // Try to find a mirror integration
        if(config('integration.check_mirror')) {
            $mirrorIntegration = $this->integrationRepository->activeMirror($integration);
            if ($mirrorIntegration) {
                $integration->flushCache();
                Log::channel('integrations')->warning('Integration ' . $integration->id . ' already has mirror integration' . $mirrorIntegration->id);
                $mirrorIntegrationLink = route('integrations.nodes', ['integration_id' => $mirrorIntegration->id]);
                return back()->with(['error' => __('integration::site.integration_has_mirror_integration', ['link' => $mirrorIntegrationLink]), 'lastIntegration' => $integration]);
            }
        }

        // Try to find a similar integration
        if(config('integration.check_similar')) {
            $similarIntegration = $this->integrationRepository->activeSimilar($integration);
            if ($similarIntegration) {
                $integration->flushCache();
                Log::channel('integrations')->warning('Integration ' . $integration->id . ' already has similar integration ' . $similarIntegration->id);
                $similarIntegrationLink = route('integrations.nodes', ['integration_id' => $similarIntegration->id]);
                return back()->with(['error', __('integration::site.integration_has_similar_integration', ['link' => $similarIntegrationLink]), 'lastIntegration' => $integration]);
            }
        }

        try {
            $integration->flushCache();
            $activated = $integrationService->activate();
            if($activated) {
                Log::channel('integrations')->info('Integration activated.');
                return back()->with('status', __('integration::site.integration_activated'));
            } else {
                Log::channel('integrations')->warning('Integration is not activated.');
                return back()->with(['error', __('integration::site.integration_is_not_activated'), 'lastIntegration' => $integration]);
            }
        } catch (IntegrationException $e) {
            Log::channel('integrations')->warning('Error: '. $e->getMessage());
            Log::channel('integrations')->warning('Integration is not activated.');
            return back()->with(['error', __($e->getMessage()), 'lastIntegration' => $integration]);
        }
    }

    /**
     * Deactivate integration
     *
     * @param Request $request
     * @param string $code
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deactivate(Request $request, string $code)
    {
        $integration = $this->integrationRepository->getByCode($code);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $code);
            return back()->with(['error' => __('integration::site.integration_has_errors')]);
        }

        $this->authorize('manage', $integration);

        Log::channel('integrations')->info(__('site.log_section_separator'));
        Log::channel('integrations')->info('User with id '.$integration->user_id.' tries to deactivate integration '.$integration->id.'.');
        $nodes = $integration->nodes;
        if(!count($nodes)) {
            $this->integrationRepository->changeActive($integration, false);
            $integration->flushCache();
            Log::channel('integrations')->warning('It does not have nodes.');
            return back()->with('error',__('integration::site.integration_has_no_nodes'));
        }

        $integrationService = app()->makeWith(IntegrationServiceContract::class, [
            'integration' => $integration
        ]);
        $deactivated = $integrationService->deactivate();

        if($deactivated) {
            Log::channel('integrations')->info('Integration deactivated.');
            $integration->flushCache();
            return back()->with('status',__('integration::site.integration_deactivated'));
        } else {
            $integration->flushCache();
            Log::channel('integrations')->warning('Integration is not deactivated.');
            return back()->with('error', __('integration::site.integration_is_not_deactivated'));
        }
    }

    public function deactivateJS(Request $request, string $code)
    {
        $integration = $this->integrationRepository->getByCode($code);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $code);
            return response()->json(['error' => __('integration::site.integration_has_errors')]);
        }

        $this->authorize('manage', $integration);

        Log::channel('integrations')->info(__('site.log_section_separator'));
        Log::channel('integrations')->info('User with id '.$integration->user_id.' tries to deactivate integration '.$integration->id.'.');
        $nodes = $integration->nodes;
        if(!count($nodes)) {
            $this->integrationRepository->changeActive($integration, false);
            $integration->flushCache();
            Log::channel('integrations')->warning('It does not have nodes.');
            return response()->json(['error' => __('integration::site.integration_has_no_nodes')]);
        }

        $integrationService = app()->makeWith(IntegrationServiceContract::class, [
            'integration' => $integration
        ]);
        $deactivated = $integrationService->deactivate();

        if($deactivated) {
            Log::channel('integrations')->info('Integration deactivated.');
            $integration->flushCache();
            return response()->json(['active' => false, 'integration' => $integration]);
        } else {
            $integration->flushCache();
            Log::channel('integrations')->warning('Integration is not deactivated.');
            return response()->json(['error' => __('integration::site.integration_is_not_deactivated')]);
        }
    }

    /**
     * @param Request $request
     * @param string $code
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function saveName(Request $request, string $code)
    {
        $integration = $this->integrationRepository->getByCode($code);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $code);
            return response()->json(['error' => __('integration::site.integration_has_errors')]);
        }

        $this->authorize('manage', $integration);
        $data = [
            'name' => $request->input('name')
        ];
        $integration = $this->integrationRepository->update($data, $integration);
        $integration->flushCache();

        return response()->json(['integration' => $integration]);
    }


}
