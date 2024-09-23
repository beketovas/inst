<?php

namespace Modules\Integration\Services;

use Modules\Application\Entities\Application;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Entities\Node;
use Modules\Integration\Events\AfterApplicationChange;
use Modules\Integration\Repositories\NodeRepository;
use Modules\User\Entities\User;
use Modules\User\Repositories\UserRepository;

class NodeCreator
{
    protected Integration $integration;

    protected User $user;

    protected UserRepository $userRepository;

    protected NodeRepository $nodeRepository;

    public function __construct(Integration $integration, User $user, UserRepository $userRepository, NodeRepository $nodeRepository)
    {
        $this->integration = $integration;
        $this->user = $user;
        $this->userRepository = $userRepository;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param int $ordering
     * @param Application $application
     * @return int Return status code ( 0 - empty node created , 1 - node created with the app )
     */
    public function createNodeWithApplication(int $ordering, Application $application): int
    {
        if($application->hasAccount()) {
            if (!($account = $application->account($this->user->id)))
                $application = null;
        }

        if (!is_null($application)) {
            if (!$this->user->hasApplication($application->id)) {
                $this->userRepository->addApplication($this->user, $application->id);
            }
        }

        $node = $this->nodeRepository->store([
            'integration_id' => $this->integration->id,
            'ordering' => $ordering,
            'application_id' => $application ? $application->id : null,
            'application_type' => $application ? $application->type : null,
            'account_id' => isset($account) ? $account->id : null
        ]);

        if(!is_null($application))
            event(new AfterApplicationChange($node));

        return (int)!is_null($application);
    }
}
