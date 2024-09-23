<?php

namespace Modules\Integration\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Integration\Events\ActionChanged;
use Modules\Integration\Events\BeforeApplicationChange;
use Modules\Integration\Events\AfterApplicationChange;
use Modules\Integration\Events\IntegrationActivated;
use Modules\Integration\Events\SynchronizationCompleted;
use Modules\Integration\Events\WebhookFailed;
use Modules\Integration\Listeners\AfterSynchronization;
use Modules\Integration\Listeners\CleanActionDependentData;
use Modules\Integration\Listeners\CleanApplicationDependentData;
use Modules\Integration\Listeners\AddApplicationNode;
use Modules\Integration\Listeners\DeactivateIntegrationAfterFail;
use Modules\Integration\Listeners\PrepareApplicationNode;
use Modules\Integration\Listeners\SyncPollingDataAfterActivation;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        BeforeApplicationChange::class => [
            CleanApplicationDependentData::class,
        ],
        AfterApplicationChange::class => [
            AddApplicationNode::class,
            PrepareApplicationNode::class
        ],
        ActionChanged::class => [
            CleanActionDependentData::class,
        ],
        SynchronizationCompleted::class => [
            AfterSynchronization::class,
        ],
        IntegrationActivated::class => [
            SyncPollingDataAfterActivation::class
        ],
        WebhookFailed::class => [
            DeactivateIntegrationAfterFail::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
