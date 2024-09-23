<?php

namespace Modules\Application\Repositories;

use App\Traits\CacheBuilder;
use Illuminate\Support\Collection;
use Modules\Application\Contracts\AccountModelContract;
use Modules\Application\Entities\Account;
use Modules\Application\Entities\Application;
use Modules\Application\Contracts\AccountRepository as AccountRepositoryContract;
use Modules\Integration\Facades\IntegrationRepository;

class AccountRepository implements AccountRepositoryContract
{
    use CacheBuilder;

    /**
     * @var Account
     */
    protected $model;

    /**
     * ApplicationRepository constructor.
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->model = $account;
    }

    /**
     * Create new
     *
     * @param array $data
     * @param int $userId
     * @return Account
     */
    public function store(array $data, int $userId = 0)
    {
        if($userId)
            $data['user_id'] = $userId;

        return $this->model->create($data);
    }

    /**
     * Update
     *
     * @param array $data
     * @param Account $account
     * @return Account
     */
    public function update(array $data, Account $account)
    {
        $account->update($data);

        return $account;
    }

    /**
     * Get by user id
     *
     * @param int $userId
     * @return Account
     */
    public function getByUserId(int $userId)
    {
        $account = $this->model->where('user_id', $userId)->first();
        return $account;
    }

    /**
     * Get by token
     *
     * @param string $accessToken
     * @param int $notAccountId
     * @return Account
     */
    public function getByToken(string $accessToken, ?int $notAccountId = null)
    {
        $query = $this->model->where('access_token', $accessToken);
        if($notAccountId)
            $query->where('id', '!=', $notAccountId);

        $account = $query->first();
        return $account;
    }

    /**
     * Get application by id
     *
     * @param int $id
     * @param int|null $userId
     * @return mixed
     */
    public function getById(int $id, ?int $userId = null): ?Account
    {
        $userId = isset($userId) ? $userId : auth()->user()->id;
        return $this->cacheRemember(['user_data_'.$userId, 'application_account_id_'.$id],
            function() use ($id) {
                return $this->model->find($id);
            }
        );
    }

    public function getByTypeAndUserId(string $type, int $userId)
    {
        return $this->model->where('application_type', $type)->where('user_id', $userId)->first();
    }

    public function getByDataInJsonField(string $field, $data)
    {
        return $this->model->whereJsonContains('account_data_json->'.$field, $data)->get();
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        return $this->model->get();
    }

    public function delete(AccountModelContract $account)
    {
        $account->delete();
    }

    public function destroy(AccountModelContract $account): void
    {
        $user = $account->user;
        $application = $account->application();
        $integrations = IntegrationRepository::getByAppAndAccount($application->id, $account->id);
        if(count($integrations) > 0) {
            foreach ($integrations as $integration) {
                IntegrationRepository::destroy($integration);
            }
        }

        $this->delete($account);

        $user->flushCache();
    }
}
