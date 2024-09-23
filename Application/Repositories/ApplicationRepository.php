<?php declare(strict_types=1);

namespace Modules\Application\Repositories;

use App\Traits\CacheBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Application\Contracts\AccountModelContract;
use Modules\Application\Entities\Application;
use Modules\Application\Facades\ApplicationManagerFacade;
use Modules\ContentManagement\Entities\Softs\Soft;
use Modules\Integration\Repositories\NodeRepository;

class ApplicationRepository
{
    use CacheBuilder;

    /**
     * @var Application
     */
    protected $model;

    protected NodeRepository $nodeRepository;

    /**
     * ApplicationRepository constructor.
     * @param Application $application
     */
    public function __construct(Application $application, NodeRepository $nodeRepository)
    {
        $this->model = $application;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param array $parameters
     * @param int $nbrPages
     * @return Collection
     */
    public function getAll(array $parameters = [], int $nbrPages = 0)
    {
        $query = $this->model
            ->select('a.*')
            ->from('applications AS a')
            ->when(isset($parameters['active']), function($query) use($parameters) {
                $query->where('a.active', $parameters['active']);
            })
            ->when(isset($parameters['development']), function($query) use($parameters) {
                $query->where('a.development', $parameters['development']);
            })
            ->when(isset($parameters['type']), function($query) use($parameters) {
                $query->where('a.type', $parameters['type']);
            })
            ->when(!empty($parameters['notIds']), function($query) use($parameters) {
                $query->whereNotIn('a.id', $parameters['notIds']);
            })
            ->when(!empty($parameters['notType']), function($query) use($parameters) {
                $query->whereNotIn('a.type', $parameters['notType']);
            })
            ->when(!empty($parameters['userId']), function($query) use ($parameters) {
                $query->join('application_user AS au', function($join) use ($parameters) {
                    $join->on('au.application_id', '=', 'a.id')
                        ->where('au.user_id', $parameters['userId']);
                });
            })
            ->when(isset($parameters['orderBy']), function($query) use($parameters) {
                if(isset($parameters['orderDir'])) {
                    $query->orderBy('a.'.$parameters['orderBy'], $parameters['orderDir']);
                } else {
                    $query->orderBy('a.'.$parameters['orderBy']);
                }
            });
        if($nbrPages) {
            $result = $query->paginate($nbrPages);
        } else {
            $result = $query->get();
        }

        return $result;
    }

    /**
     * Get application collection
     *
     * @return Collection
     */
    public function index()
    {
        return $this->model
            ->select('*')
            ->whereActive(true)
            ->latest()
            ->get();
    }

    /**
     * Find application by slug
     *
     * @param string $slug
     * @return Application
     */
    public function getBySlug(string $slug): ?Application
    {
        return $this->cacheRemember(['applications', 'slug_'.$slug],
            function() use ($slug) {
                return $this->model
                    ->select('*')
                    ->where('slug', $slug)
                    ->first();
            },
            $this->cacheWeek
        );
    }

    /**
     * Find application by type
     *
     * @param string $type
     * @return Application
     */
    public function getByType(string $type): ?Application
    {
        return $this->cacheRemember(['applications', 'type_'.$type],
            function() use ($type) {
                return $this->model
                    ->select('*')
                    ->where('type', $type)
                    ->first();
            }
        );
    }

    /**
     * @return Collection
     */
    public function getActiveOrderName(): Collection
    {
        return $this->cacheForever(['applications', 'active_order_by_name'], function() {
            return  $this->model
                ->where('active', 1)
                ->with('users')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getActiveByUserId(int $userId): Collection
    {
        return $this->cacheForever(['applications','active_by_user_'.$userId], function() use($userId) {
            return $this->model
                ->select('applications.*')
                ->join('application_user as au', 'au.application_id', 'applications.id')
                ->where('au.user_id', $userId)
                ->where('au.active', 1)
                ->where('applications.active', 1)
                ->orderBy('name')
                ->get();
        });
    }

    public function getActiveAndDevelopmentByUserId(int $userId): Collection
    {
        return $this->cacheForever(['applications','active_development_by_user_'.$userId], function() use($userId) {
            return $this->model
                ->select('applications.*')
                ->join('application_user as au', 'au.application_id', 'applications.id')
                ->where('au.user_id', $userId)
                ->where(function($query) {
                    $query->orWhere('applications.active', 1)
                        ->orWhere('development', 1);
                })
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getActiveWithAccountByUserId(int $userId): Collection
    {
        return $this->cacheForever(['applications', 'applications_user_'.$userId], function() use($userId) {
            return $this->model
                ->select('applications.*')
                ->join('application_user as au', 'au.application_id', 'applications.id')
                ->where('au.user_id', $userId)
                ->where('au.active', 1)
                ->where('applications.active', 1)
                ->orderBy('ordering')
                ->get()
                ->each(function($item) use ($userId) {
                    $item['account'] = $item->account($userId);
                    $item['count_of_ways'] = $this->nodeRepository->getCountOfWays($item, $userId);
                    return $item;
                });
        });
    }

    public function getApplicationAccount(Application $application, int $userId): ?AccountModelContract
    {
        return $this->cacheRemember(['user_data_'.$userId, 'application_account_'.$application->type],
            function() use ($userId, $application) {
                return $application->account($userId);
            }
        );
    }

    /**
     * Get application by id
     *
     * @param int $id
     * @return Application
     */
    public function getById(int $id)
    {
        return $this->cacheRemember(['applications', 'id_'.$id],
            function() use ($id) {
                return $this->model->find($id);
            });
    }

    /**
     * Get applications for trigger node
     *
     * @param int $userId
     * @return array
     */
    public function getForTrigger(int $userId): Collection
    {
        return $this->cacheForever(['applications', 'for_trigger_node_user_'.$userId], function() use ($userId) {
            return $this->model->newQuery()
                ->select('applications.*', DB::raw("(select user_id from application_user where user_id = {$userId} and application_id = applications.id) connected"))
                ->where(function($where) {
                    $where->orWhere('applications.active', true)
                        ->orWhere('applications.development', true);
                })
                ->where('applications.has_triggers', true)
                ->orderBy('connected', 'desc')
                ->orderBy('name')
                ->get();
        });
    }

    public function getApplicationAccountByUserId(string $appType, int $userId): ?AccountModelContract
    {
        if(!class_exists('Modules\\'.studly_case($appType).'\\Repositories\\AccountRepository')) {
            $accountRepository = app(AccountRepository::class);
            return $accountRepository->getByTypeAndUserId($appType, $userId);
        }
        $applicationAccount = app()->make('Modules\\'.studly_case($appType).'\\Repositories\\AccountRepository');
        return $applicationAccount->getByUserId($userId);
    }

    /**
     * Get applications for action node
     *
     * @param int $userId
     * @return array
     */
    public function getForAction(int $userId): Collection
    {
        return $this->cacheForever(['applications', 'for_action_node_user_'.$userId], function() use($userId) {
            return $this->model->newQuery()
                ->select('applications.*', DB::raw("(select user_id from application_user where user_id = {$userId} and application_id = applications.id) connected"))
                ->where(function($where) {
                    $where->orWhere('applications.active', true)
                        ->orWhere('applications.development', true);
                })
                ->where('applications.has_actions', true)
                ->orderBy('connected', 'desc')
                ->orderBy('name')
                ->get();
        });
    }

    public function getNotConnectedApplication(array $notIds, int $userId)
    {
        return $this->cacheForever(['applications','not_connected_applications_user_'.$userId], function() use($notIds, $userId) {
            return $this->model
                ->select('applications.*')
                ->whereNotIn('applications.id', $notIds)
                ->where('applications.active', 1)
                ->orderBy('ordering')
                ->get()
                ->each(function($item) use ($userId) {
                    $item['account'] = $item->account($userId);
                    return $item;
                });
        });
    }

    public function getApplicationWithSoft(): ?Collection
    {
        return $this->cacheRemember(['applications', 'with_soft'], function() {
            return $this->model
                ->with('soft')
                ->whereHas('soft', function($query) {
                    $query->where('id', '!=', 'null');
                })
                ->where('applications.active', true)
                ->orderBy('applications.ordering')
                ->get();
        });
    }

    public function getApplicationSoft(Application $application)
    {
        return $this->cacheRemember(['application_'.$application->type, 'soft'],
            function() use ($application) {
                return $application->soft ?: new Soft();
            }
        );
    }
}
