<?php
namespace Modules\Integration\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Integration\Contracts\Executor;

class ExecutorProvider extends ServiceProvider
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
        // Create an integration api service
        $this->app->bind(Executor::class, function($app, $parameters) {
            if(empty($parameters['applicationType'])) {
                throw new \InvalidArgumentException("Application type is entity necessary to create proper executor.");
            }

            $class = "Modules\\" . studly_case($parameters['applicationType']) . "\\Helpers\\Executor";
            //$executor = $app->make($class);

            return $class;

        });
    }
}
