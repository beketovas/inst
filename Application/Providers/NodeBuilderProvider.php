<?php

namespace Modules\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\Contracts\NodeModelContract;
use Modules\Integration\Builders\Node\ApplicationBuilders\ActionBuilder;
use Modules\Integration\Builders\Node\ApplicationBuilders\FactoryTriggerBuilder;

class NodeBuilderProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Create node builder
        $this->app->bind(NodeModelContract::class, function($app, $parameters) {
            $nodeModel = $parameters['nodeModel'];
            $application = $nodeModel->getAttribute('application');

            // if node is trigger, load trigger builder from proper module
            if($nodeModel->is_trigger) {
                $nodeBuilder = FactoryTriggerBuilder::getInstance($nodeModel);
            }
            else {
                $nodeBuilder = app()->makeWith(ActionBuilder::class, ['nodeModel' => $nodeModel]);
            }

            return $nodeBuilder;

        });
    }
}
