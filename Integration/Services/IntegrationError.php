<?php

namespace Modules\Integration\Services;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Mails\IntegrationDeactivatedDueToErrors;
use Modules\User\Facades\UserError;

class IntegrationError
{
    protected Connection $connection;

    public function __construct()
    {
        $this->connection = Redis::connection('integration_errors');
    }

    public function saveError(Integration $integration): bool
    {
        $this->connection->transaction(function ($redis) use ($integration) {
            $numberOfErrors = $this->connection->get($integration->id);
            if($numberOfErrors == 0)
                $redis->set($integration->id, 1);
            else if($numberOfErrors == config('app.way_of_errors_count')-1)
                $this->deactivateIntegration($integration);
            else
                $redis->incr($integration->id);
        });
        return true;
    }

    public function resetErrors(int $integrationId): void
    {
        $this->connection->del($integrationId);
    }

    protected function deactivateIntegration(Integration $integration)
    {
        $this->connection->del($integration->id);
        $integrationService = app(IntegrationService::class, ['integration' => $integration]);
        $integrationService->deactivateBy('api', '', 'Too many errors');
        if(config('mail.send_all_messages')) {
            Mail::to($integration->user->email)
                ->send(new IntegrationDeactivatedDueToErrors($integration));
        }
        UserError::store([
            'user_id' => $integration->user_id,
            'application_type' => $integration->triggerNode()->application->type,
            'account_id' => isset($integration->triggerNode()->account) ? $integration->triggerNode()->account->id : null,
            'method' => 'Integration',
            'error_message' => 'Integration '.$integration->name.' has been deactivated due to a lot of errors. Please make some changes and activate again.',
            'response' => json_encode(['message' => 'Integration '.$integration->name.' has been deactivated due to a lot of errors. Please make some changes and activate again.']),
        ]);
        Log::channel('integrations')->info('Integration (id:'.$integration->id.') has been deactivated due to a lot of errors.');
    }
}
