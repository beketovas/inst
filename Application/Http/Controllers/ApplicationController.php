<?php

namespace Modules\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Application\Http\Resources\UserApplicationResource;
use Modules\Application\Repositories\ApplicationRepository;
use Modules\Billing\Helpers\DataTransfersHelper;

class ApplicationController extends Controller
{

    /**
     * @var ApplicationRepository
     */
    protected $applicationRepository;

    /**
     * ApplicationController constructor.
     *
     * @param ApplicationRepository $applicationRepository
     */
    public function __construct(ApplicationRepository $applicationRepository)
    {
        $this->applicationRepository = $applicationRepository;
    }

    /**
     * Index applications page
     *
     * @return Factory|View
     */
    public function index()
    {
        $applications = $this->applicationRepository->getActiveWithAccountByUserId(auth()->user()->id);
        $sShowIntegration = DataTransfersHelper::showIntegrationByUser(auth()->user());
        $aDataTransfers = DataTransfersHelper::getDataTransfersByUser(auth()->user());

        return view(config('app.theme').'.front.applications.applications', compact('applications', 'aDataTransfers', 'sShowIntegration'));
    }

    /**
     * User selected applications
     * @return JsonResponse
     */
    public function userApplications()
    {
        $userApplications = $this->applicationRepository->getActiveWithAccountByUserId(auth()->user()->id);
        $applications = UserApplicationResource::collection($userApplications);
        return response()->json(['userApplications' => $applications]);
    }

    /**
     * User selected applications
     * @return JsonResponse
     */
    public function notConnectedApplications()
    {
        $userId = auth()->user()->id;

        $userApplications = $this->applicationRepository->getActiveByUserId($userId);
        $ids = $userApplications->pluck('id')->toArray();
        $notConnectedApplications = $this->applicationRepository->getNotConnectedApplication($ids, $userId);
        $applications = UserApplicationResource::collection($notConnectedApplications);

        return response()->json(['applications' => $applications]);
    }

    public function applications()
    {
        $userId = auth()->user()->id;

        $applicationsWithAccount = $this->applicationRepository->getActiveWithAccountByUserId(auth()->user()->id);
        $userApplications = UserApplicationResource::collection($applicationsWithAccount);
        $ids = $applicationsWithAccount->pluck('id')->toArray();
        $notConnectedApplications = $this->applicationRepository->getNotConnectedApplication($ids, $userId);
        $notConnectedApplicationsResource = UserApplicationResource::collection($notConnectedApplications);
        return response()->json(['userApplications' => $userApplications, 'applications' => $notConnectedApplicationsResource]);
    }

    public function actions($applicationId)
    {
        $application = $this->applicationRepository->getById($applicationId);
        return response()->json(['actions' => $application->actions()->get()]);
    }


}
