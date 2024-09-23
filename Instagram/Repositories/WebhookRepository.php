<?php

namespace Modules\Instagram\Repositories;

use Exception;
use Apiway\Hooks\Contracts\Hook;
use Modules\Instagram\Entities\Webhook;
use Apiway\Hooks\Contracts\HookRepository;

class WebhookRepository implements HookRepository
{
    /**
     * @var Webhook
     */
    protected $model;

    /**
     * WebhookRepository constructor.
     *
     * @param Webhook $webhook
     */
    public function __construct(Webhook $webhook)
    {
        $this->model = $webhook;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return Webhook
     */
    public function getById($id)
    {
        $webhook = $this->model->find($id);
        return $webhook;
    }

    /**
     * Get by code
     *
     * @param string $code
     * @return Webhook
     */
    public function getByCode(string $code)
    {
        $webhook = $this->model->where('code', $code)->first();
        return $webhook;
    }

    /**
     * Save webhook data to DB
     *
     * @param Webhook $webhook
     * @param array $data
     * @return Webhook
     */
    public function saveWebhook(Webhook $webhook, $data)
    {
        $webhook->integration_id = $data['integration_id'];
        $webhook->node_id = $data['node_id'];
        if(isset($data['code']))
            $webhook->code = $data['code'];
        $webhook->opened_for_sample = $data['opened_for_sample'];
        $webhook->save();

        return $webhook;
    }

    /**
     * Create a new webhook
     *
     * @param array $data
     * @return Webhook
     */
    public function store($data)
    {
        $webhook = $this->saveWebhook(new $this->model, $data);

        return $webhook;
    }

    /**
     * Update webhook
     *
     * @param array $data
     * @param Webhook $webhook
     * @return Webhook
     */
    public function update($data, Webhook $webhook)
    {
        $webhook = $this->saveWebhook($webhook, $data);

        return $webhook;
    }

    /**
     * @param Hook $webhook
     * @return Hook
     */
    public function openGateForSample(Hook $webhook)
    {
        $webhook->opened_for_sample = 1;
        $webhook->save();
        return $webhook;
    }

    /**
     * @param Hook $webhook
     * @return Hook
     */
    public function closeGateForSample(Hook $webhook)
    {
        $webhook->opened_for_sample = 0;
        $webhook->save();
        return $webhook;
    }

    /**
     * Delete webhook
     *
     * @param Webhook $webhook
     * @throws Exception
     */
    public function delete(Webhook $webhook)
    {
        $webhook->delete();
    }
}
