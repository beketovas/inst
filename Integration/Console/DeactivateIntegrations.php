<?php

namespace Modules\Integration\Console;

use Illuminate\Console\Command;
use Modules\Integration\Contracts\IntegrationServiceContract;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Repositories\IntegrationRepository;

class DeactivateIntegrations extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'integration:deactivate-integrations {applicationId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate integrations by application id.';

    /**
     * @var IntegrationRepository
     */
    protected $integrationRepository;

    /**
     * Create a new command instance.
     *
     * @param IntegrationRepository $integrationRepository
     */
    public function __construct(IntegrationRepository $integrationRepository)
    {
        parent::__construct();

        $this->integrationRepository = $integrationRepository;
    }

    private function deactivate(Integration $integration)
    {
        $integrationService = app()->makeWith(IntegrationServiceContract::class, [
            'integration' => $integration
        ]);
        $deactivated = $integrationService->deactivateBy('api', getCommandName());

        if($deactivated) {
            print("Integration {$integration->id} deactivated.\n");
        } else {
            print("Integration {$integration->id} is not deactivated.\n");
        }
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $integrations = $this->integrationRepository->getAll([
            'applicationId' => $this->argument('applicationId'),
            'active' => 1
        ]);
        if(!count($integrations))
            print("No integrations\n");

        foreach ($integrations as $integration) {
            $this->deactivate($integration);
        }
    }


}
