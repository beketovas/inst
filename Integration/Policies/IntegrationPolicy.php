<?php declare(strict_types=1);

/**
 * @author Stanislav Taranovskyi <staranovskyi@gmail.com>
 */

namespace Modules\Integration\Policies;

use App\Contracts\UserEntity;
use Modules\Integration\Entities\Integration;
use Illuminate\Auth\Access\HandlesAuthorization;

class IntegrationPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {

    }

    /**
     * Determine whether the user can manage an integration.
     *
     * @param  UserEntity $user
     * @param  Integration $integrations
     * @return mixed
     */
    public function manage(UserEntity $user, Integration $integrations)
    {
        if($user->id === $integrations->user_id)
            return true;
    }

}
