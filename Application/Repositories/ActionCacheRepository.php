<?php

namespace Modules\Application\Repositories;

use App\Traits\CacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Modules\Application\Entities\Application;

class ActionCacheRepository
{
    use CacheBuilder;

    protected Application $application;

    protected Model $actionEntity;

    public function __construct(Model $actionEntity, Application $application)
    {
        $this->actionEntity = $actionEntity;
        $this->application = $application;

    }

    public function getTriggers()
    {
        return $this->cacheRemember(['application_'.$this->application->type, 'triggers'], function() {
            return $this->actionEntity->newQuery()->where('for_trigger', true)->get();
        });
    }

    public function getActions()
    {
        return $this->cacheRemember(['application_'.$this->application->type, 'actions'], function() {
            return $this->actionEntity->newQuery()->where('for_action', true)->get();
        });
    }
}
