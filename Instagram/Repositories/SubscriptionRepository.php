<?php

namespace Modules\Instagram\Repositories;

use App\Traits\CacheBuilder;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Instagram\Entities\Subscription;
use Apiway\Hooks\Contracts\HookRepository;
use Modules\Integration\Entities\Integration;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository implements HookRepository
{
    use CacheBuilder;

    /**
     * @var Subscription
     */
    protected $model;

    /**
     * WebhookRepository constructor.
     *
     * @param Subscription $webhook
     */
    public function __construct(Subscription $webhook)
    {
        $this->model = $webhook;
    }

    /**
     * @param Integration $integration
     * @return mixed
     */
    public function getCachedSubscriptionByIntegration(Integration $integration): Subscription
    {
        $cacheKey = 'instagram_subscription_' . $integration->id;
        return $this->cacheRemember(
            [$cacheKey, "value"],
            function() use ($integration) {
                return $this->model
                    ->where('integration_id', '=', $integration->id)->first();
            }
        );
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return Subscription
     */
    public function getById(int $id): Subscription
    {
        $webhook = $this->model->find($id);
        return $webhook;
    }

    /**
     * Get by code
     *
     * @param string $code
     * @return Subscription
     */
    public function getByCode(string $code): Subscription
    {
        $webhook = $this->model->where('code', $code)->first();
        return $webhook;
    }

    /**
     * Save webhook data to DB
     *
     * @param Subscription $webhook
     * @param array $data
     * @return Subscription
     */
    public function saveWebhook(Subscription $webhook, array $data): Subscription
    {
        $webhook->integration_id = $data['integration_id'];
        $webhook->node_id = $data['node_id'];
        $webhook->event = $data['event'];

        $webhook->save();

        return $webhook;
    }

    /**
     * Create a new webhook
     *
     * @param array $data
     * @return Subscription
     */
    public function store(array $data): Subscription
    {
        $webhook = $this->saveWebhook(new $this->model, $data);

        return $webhook;
    }

    /**
     * Update webhook
     *
     * @param array $data
     * @param Subscription $webhook
     * @return Subscription
     */
    public function update($data, Subscription $webhook): Subscription
    {
        $webhook = $this->saveWebhook($webhook, $data);

        return $webhook;
    }

    /**
     * Delete webhook
     *
     * @param Subscription $webhook
     * @throws Exception
     */
    public function delete(Subscription $webhook)
    {
        $webhook->delete();
    }
}
