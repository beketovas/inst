<?php

namespace Modules\Integration\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Integration\Builders\Node\ApplicationBuilders\FactoryErrorBuilder;
use Modules\Integration\Contracts\IntegrationServiceContract;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Services\TestIntegration;

class ActivateIntegration extends Command
{
    protected $signature = 'activate-integrations {?--emails=} {?--ids=}';

    private int $activated = 0;
    private int $notActivated = 0;

    public function handle()
    {
        $ids = $this->option('ids');
        if(isset($ids)) {
            $ids = explode(',', $ids);
            $this->searchByIds($ids);
        }

        $emails = $this->option('emails');
        if(isset($emails) && !isset($ids)) {
            $emails = explode(',', $emails);
            $this->searchByEmails($emails);
        }

        if(!isset($emails) && !isset($ids)) {
            $this->searchAll();
        }

        $this->info('Total integrations: ' . ($this->activated + $this->notActivated));
        $this->info('Activated: '. $this->activated);
        $this->warn('Not activated: '.$this->notActivated);
    }

    protected function searchByIds(array $ids)
    {
        $integrations = Integration::query()
            ->whereIn('id', $ids)
            ->where('integrations.active', false)
            ->get();

        foreach ($integrations as $integration) {
            $this->activate($integration);
        }
    }

    protected function searchByEmails(array $emails)
    {
        $integrations = Integration::query()
            ->select('integrations.*')
            ->join('users', 'users.id', '=', 'integrations.user_id')
            ->whereIn('users.email', $emails)
            ->where('integrations.active', false)
            ->get();
        foreach ($integrations as $integration) {
            $this->activate($integration);
        }
    }

    protected function searchAll()
    {
        $integrations = Integration::query()
            ->where('integrations.active', false)
            ->get();
        foreach ($integrations as $integration) {
            $this->activate($integration);
        }
    }

    protected function activate(Integration $integration)
    {
        try {
            if($this->validate($integration) == false) {
                $this->error('Integration '.$integration->id.' validation error!');
                $this->notActivated += 1;
                return;
            }

            $integrationService = app(IntegrationServiceContract::class, ['integration' => $integration]);
            $errors = $integrationService->hasErrors();
            if ($errors) {
                $integration->flushCache();
                Log::channel('integrations')->warning('Integration ' . $integration->id . ' has errors. Errors by nodes: ' . print_r($errors, true));
                $this->error('Integration ' . $integration->id . ' has errors. Errors by nodes: ' . print_r($errors, true));
                $this->notActivated += 1;
                return;
            }
            $integration->flushCache();
            $activated = $integrationService->activate(getCommandName());
            if ($activated) {
                Log::channel('integrations')->info('Integration activated.');
                $this->info('Integration ' . $integration->id . ' activated');
                $this->activated += 1;
            } else {
                Log::channel('integrations')->warning('Integration is not activated.');
                $this->warn('Integration '.$integration->id.' is not activated');
                $this->notActivated += 1;
            }
        } catch (\Throwable $e) {
            Log::channel('integrations')->warning('Error: ' . $e->getMessage());
            Log::channel('integrations')->warning('Integration is not activated.');
            $this->error('Integration ' . $integration->id . ' is not activated. Error:'.$e->getMessage());
            $this->notActivated += 1;
        }
    }

    protected function validate(Integration $integration): bool
    {
        $triggerNode = $integration->triggerNode();
        if(!$triggerNode->application) {
            $triggerNodeErrors['critical'] = true;
        }
        else {
            $triggerErrorsBuilder = FactoryErrorBuilder::getInstance($triggerNode);
            $triggerErrorsBuilder->build();
            $triggerNodeErrors = $triggerErrorsBuilder->getErrors();
        }

        $actionNode = $integration->actionNode();
        if(!$actionNode->application) {
            $actionNodeErrors['critical'] = true;
        }
        else {
            $actionErrorsBuilder = FactoryErrorBuilder::getInstance($actionNode);
            $actionErrorsBuilder->build();
            $actionNodeErrors = $actionErrorsBuilder->getErrors();
        }

        $integrationTestErrors = false;

        $readyForActivation = empty($triggerNodeErrors) && empty($actionNodeErrors);
        if ($readyForActivation) {
            $testIntegrationService = new TestIntegration($triggerNode, $actionNode);
            $integrationTestErrors = !$testIntegrationService->execute();
        }

        $criticalError = isset($triggerNodeErrors['critical']) || isset($actionNodeErrors['critical']);

        return empty($triggerNodeErrors) && empty($actionNodeErrors) && !$integrationTestErrors && empty($criticalError);
    }
}
