<?php declare(strict_types=1);

namespace Modules\Instagram\Services;

use App\Traits\CacheBuilder;
use Modules\Application\Contracts\IntegrationServiceContract as AppIntegrationServiceContract;
use Modules\Integration\Entities\Node;
use Modules\Instagram\Repositories\FieldRepository;
use Modules\Instagram\Repositories\SubscriptionRepository;
use Modules\Integration\Repositories\NodePollingRepository;
use Illuminate\Support\Facades\Log;

class IntegrationService implements AppIntegrationServiceContract
{
    use CacheBuilder;

    protected Node $node;

    protected NodePollingRepository $pollingDataRepository;

    protected SubscriptionRepository $subscriptionRepository;

    protected FieldRepository $fieldRepository;

    public function __construct(
        Node $node,
        NodePollingRepository $pollingDataRepository,
        SubscriptionRepository $subscriptionRepository,
        FieldRepository $fieldRepository)
    {
        $this->node = $node;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->pollingDataRepository = $pollingDataRepository;
        $this->fieldRepository = $fieldRepository;
    }

    public function activate(): bool
    {
        $appNode = $this->node->applicationNode;
        $action = $appNode->action->task;
        $account = $this->node->account;
        $integration = $this->node->integration;

        $triggerPollConfig = api_config('instagram.triggers_polling');

        if (isset($triggerPollConfig) && isset($triggerPollConfig[$appNode->action->type])) {
            $this->subscriptionRepository->store([
                'node_id' => $appNode->id,
                'integration_id' => $this->node->integration_id,
                'event' => $action,
            ]);

            $this->cacheFlush(['instagram_subscription_', $integration->id]);

            $this->pollingDataRepository->create([
                'application_type' => config('instagram.type'),
                'node_id' => $appNode->node->id,
                'trigger_type' => $appNode->action->type,
                'user_id' => $account->user_id,
            ]);
        } else {
            Log::channel('integrations')->info('Webhook to Instagram cannot be added, because trigger type: ' . $appNode->action->type . ' not found in settings. Integration id: ' . $this->node->integration_id);
            return false;
        }

        Log::channel('integrations')->info('Webhook to Instagram added. Integration id: ' . $this->node->integration_id);

        return true;
    }

    public function deactivate(): bool
    {
        $integration = $this->node->integration;

        $subscription = $this->subscriptionRepository->getCachedSubscriptionByIntegration($integration);
        if (empty($subscription)) {
            return true;
        }

        $this->subscriptionRepository->delete($subscription);

        $this->cacheFlush(['instagram_subscription_', $integration->id]);
        $integration->flushCache();

        return true;
    }
}
