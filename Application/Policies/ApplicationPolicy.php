<?php

namespace Modules\Application\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Application\Contracts\AccountModelContract;
use Modules\Application\Entities\Application;
use Modules\Application\Facades\ApplicationRepository;
use Modules\User\Entities\User;

class ApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can change the active campaign.
     *
     * @param  User  $user
     * @param  AccountModelContract $application
     * @return mixed
     */
    public function change(User $user, AccountModelContract $account, Application $application)
    {
        return $user->id === $account->user_id &&
            ($application->active == true || $application->development == true);
    }

    public function create(User $user, Application $application)
    {
        return ApplicationRepository::getApplicationAccount($application, $user->id) === null
            && ($application->active == true || $application->development == true);
    }

}
