<?php

namespace Modules\Integration\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Integration\Contracts\IntegrationServiceContract;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Events\WebhookFailed;
use Modules\Integration\Facades\IntegrationStorage;
use Modules\Integration\Mails\IntegrationDeactivatedDueToIncomingData;
use Modules\Integration\Mails\NotWorkingIntegration;
use Modules\User\Facades\UserError;

class DeactivateIntegrationAfterFail
{
    protected Integration $integration;

    protected string $reason;

    protected string $appType;

    protected int $accountId;

    protected string $method;

    public function handle(WebhookFailed $event)
    {
        $this->integration = $event->integration;
        $this->reason = $event->reason;
        $this->appType = $event->appType;
        $this->accountId = $event->accountId;
        $this->method = $event->method;

        $integrationService = app()->makeWith(IntegrationServiceContract::class, [
            'integration' => $this->integration
        ]);
        $integrationService->deactivateBy('api', '', 'Incorrect incoming data');

        $this->integration->flushCache();

        Log::channel('integrations')->info($this->appType. '. Integration has been deactivated. Reason: '. $this->reason);

        $integrationStorage = IntegrationStorage::load($this->integration);
        if(config('mail.send_all_messages')) {
            Mail::to($integrationStorage->getIntegration()->user->email)
                ->send(new IntegrationDeactivatedDueToIncomingData($integrationStorage));
        }

         UserError::store([
            'application_type' => $this->appType,
            'account_id' => $this->accountId,
            'user_id' => $this->integration->user_id,
            'method' => $this->method,
            'error_message' => $this->reason
        ]);
    }
}
