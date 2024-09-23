<?php declare(strict_types=1);

/**
 * @author Stanislav Taranovskyi <staranovskyi@gmail.com>
 */

namespace Modules\Integration\Repositories;

use App\Traits\CacheBuilder;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Contracts\IntegrationServiceContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Integration\Exceptions\IntegrationException;

class IntegrationRepository
{
    use CacheBuilder;

    /**
     * @var Integration
     */
    protected $model;

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * IntegrationRepository constructor.
     *
     * @param Integration $integration
     * @param NodeRepository $nodeRepository
     */
    public function __construct(Integration $integration, NodeRepository $nodeRepository)
    {
        $this->model = $integration;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * Get integration collection
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return $this->model
            ->select('*')
            ->latest()
            ->get();
    }

    public function filterByActionAndTrigger(Builder $query, string $action, string $trigger): Builder
    {
        $query->select('integrations.*')->fromSub(function ($query) use ($trigger, $action) {
            $query
                ->select('integrations.*', DB::raw('count(*) as total'))
                ->from('integrations')
                ->leftJoin('integration_nodes', 'integration_nodes.integration_id', '=', 'integrations.id')
                ->where([
                    ['application_type', '=', $trigger],
                    ['ordering', '=', 1],
                ])
                ->orWhere([
                    ['application_type', '=', $action],
                    ['ordering', '=', 2],
                ])
                ->groupBy('integration_id')
                ->having('total', '=', 2);
        }, 'integrations');

        return $query;
    }

    public function getAll(array $parameters, $nbrPages = null)
    {
        $query = $this->model
            ->select('integrations.*')
            ->join('users', 'users.id', '=', 'integrations.user_id')
            ->where('users.deleted_at', null);

        //by trigger and action
        if (isset($parameters['trigger']) && isset($parameters['action']))
            $query = $this->filterByActionAndTrigger($query, $parameters['action'], $parameters['trigger']);
        // by trigger or action
        else if (isset($parameters['trigger']) || isset($parameters['action'])) {
            $query->leftJoin('integration_nodes', 'integration_nodes.integration_id', '=', 'integrations.id')
                ->when(isset($parameters['trigger']), function ($query) use ($parameters) {
                    $query->where([
                        ['integration_nodes.application_type', $parameters['trigger']],
                        ['integration_nodes.ordering', 1],
                    ]);
                })
                ->when(isset($parameters['action']), function ($query) use ($parameters) {
                    $query->where([
                        ['integration_nodes.application_type', $parameters['action']],
                        ['integration_nodes.ordering', 2],
                    ]);
                });
        }

        //other conditions
        $query->when(isset($parameters['id']), function ($query) use ($parameters) {
            $query->where('integrations.id', $parameters['id']);
        })
            ->when(isset($parameters['code']), function ($query) use ($parameters) {
                $query->where('integrations.code', $parameters['code']);
            })
            ->when(isset($parameters['active']), function ($query) use ($parameters) {
                $query->where('integrations.active', (bool)$parameters['active']);
            })
            ->when(isset($parameters['order']) && isset($parameters['direction']), function ($query) use ($parameters) {
                $query->orderBy('integrations.' . $parameters['order'], $parameters['direction']);
            })
            ->when(isset($parameters['applicationId']), function ($query) use ($parameters) {
                $query->leftJoin('integration_nodes', 'integration_nodes.integration_id', '=', 'integrations.id');
                $query->where('application_id', $parameters['applicationId']);
                $query->groupBy('integrations.id');
            })
            ->when(isset($parameters['triggerApplicationId']), function ($query) use ($parameters) {
                $query->leftJoin('integration_nodes', 'integration_nodes.integration_id', '=', 'integrations.id');
                $query->where('application_id', $parameters['triggerApplicationId']);
                $query->where('ordering', 1);
                $query->groupBy('integrations.id');
            })
            ->when(isset($parameters['userId']), function ($query) use ($parameters) {
                $query->where('user_id', $parameters['userId']);
            })
            ->when(isset($parameters['email']), function ($query) use ($parameters) {
                    $query->where(function ($query) use ($parameters) {
                        $query->orWhere('users.email', 'like', $parameters['email'] . '%')
                            ->orWhere('users.email', 'like', '%' . $parameters['email'] . '%')
                            ->orWhere('users.email', 'like', '%' . $parameters['email']);
                    });
            })
            ->when(isset($parameters['integration_name']), function ($query) use ($parameters) {
                $query->where(function ($query) use ($parameters) {
                    $query->orWhere('integrations.name', 'like', $parameters['integration_name'] . '%')
                        ->orWhere('integrations.name', 'like', '%' . $parameters['integration_name'] . '%')
                        ->orWhere('integrations.name', 'like', '%' . $parameters['integration_name']);
                });
            });

        if ($nbrPages) {
            $result = $query->paginate($nbrPages);
        } else {
            $result = $query->get();
        }
        return $result;
    }

    public function getInactive()
    {
        return $this->model->where('active', false)->get();
    }

    public function getIntegrationsByUser(int $userId, int $page, $nbrPages = null)
    {
        return $this->cacheRemember(['integrations_user_'.$userId, 'page_'.$page],
            function() use ($nbrPages, $userId) {
                $query = $this->model->where('user_id', $userId)->with('nodes.application');
                if ($nbrPages)
                    $result = $query->paginate($nbrPages);
                else
                    $result = $query->get();
                return $result;
            });
    }

    /**
     * Get integration by id
     *
     * @param int $id
     * @return Integration
     */
    public function getById(int $id)
    {
        return $this->cacheRemember(['integrations', 'integration_id_'.$id],
            function() use ($id) {
                return $this->model->find($id);
            }
        );
    }

    /*public function getByIdWithUser(int $id)
    {
        return $this->cacheRemember(['integrations', 'integration_id_'.$id.'_with_user'],
            function() use ($id) {
                return $this->model->where('id', $id)->with('user')->get()->first();
            }
        );
    }*/

    /**
     * Get integrations by user id
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUserId(int $userId)
    {
        return $this->cacheRemember(['integrations_user_'.$userId, 'all'],
            function() use ($userId) {
                return $this->model->where('user_id', $userId)->with('nodes.application')->get();
            });
    }

    /**
     * Get integrations by code
     *
     * @param string $code
     * @return Integration
     */
    public function getByCode(string $code)
    {
        return $this->cacheRemember(['integrations', 'integration_code_'.$code],
            function() use ($code) {
                return $this->model->where('code', $code)->with('nodes.application')->with('nodes.integration.user')->first();
            });
    }

    /**
     * Save integration data to DB
     *
     * @param Integration $integration
     * @param array $inputs
     * @param int $userId
     * @return Integration
     */
    public function saveIntegration(Integration $integration, array $inputs, $userId = null)
    {
        if($userId)
            $integration->user_id = $userId;
        if(isset($inputs['code']))
            $integration->code = $inputs['code'];
        if(isset($inputs['name']))
            $integration->name = $inputs['name'];

        if(isset($inputs['slug']))
            $integration->slug = $inputs['slug'];

        $integration->save();

        return $integration;
    }

    /**
     * Create new integration
     *
     * @param array $inputs
     * @param int $userId
     * @return Integration
     */
    public function store(array $inputs, int $userId)
    {
        $integration = $this->saveIntegration(new $this->model, $inputs, $userId);

        return $integration;
    }

    /**
     * Update integration
     *
     * @param array $inputs
     * @param Integration $integration
     * @return Integration
     */
    public function update(array $inputs, Integration $integration)
    {
        $this->cacheForget(['integrations', 'integration_'.$integration->id], ['integrations', 'integration_code_'.$integration->code]);
        $integration = $this->saveIntegration($integration, $inputs);

        return $integration;
    }

    /**
     * Delete integration
     *
     * @param Integration $integration
     * @throws Exception
     */
    public function delete(Integration $integration)
    {
        $integration->delete();
    }

    /**
     * Destroy integration completely with all associated data
     *
     * @param Integration $integration
     * @throws Exception
     */
    public function destroy(Integration $integration)
    {
        // Deactivate if active
        if($integration->active) {
            $integrationService = app()->makeWith(IntegrationServiceContract::class, [
                'integration' => $integration
            ]);
            $deactivated = $integrationService->deactivate();
        }

        try {
            // Destroy action node
            $actionNode = $integration->actionNode();
            $this->nodeRepository->destroy($actionNode);
        } catch (\Throwable $e) {
        }

        try {
            // Destroy trigger node
            $triggerNode = $integration->triggerNode();
            $this->nodeRepository->destroy($triggerNode);
        } catch (\Throwable $e) {
        }

        // Delete integration
        $this->delete($integration);
    }

    /**
     * @param Integration $integration
     * @param bool $active
     * @return Integration
     */
    public function changeActive(Integration $integration, bool $active)
    {
        $integration->active = $active;
        $this->cacheForget(['integrations', 'integration_'.$integration->id]);
        $integration->save();
        return $integration;
    }

    public function removeWarning(Integration $integration): Integration
    {
        $integration->warned = false;
        $integration->save();
        return $integration;
    }

    /**
     * Get all active integrations from the same user but excluding searchable one
     *
     * @param Integration $integration
     * @return Collection
     */
    public function getOtherActive(Integration $integration)
    {
        $items = $this->model
            ->whereActive(true)
            ->where('user_id', $integration->user_id)
            ->where('id', '!=', $integration->id)
            ->get();

        return $items;
    }

    /**
     * @param int $applicationId
     * @param int $accountId
     * @return Collection
     */
    public function getByAppAndAccount(int $applicationId, int $accountId): Collection
    {
        return $this->model
            ->select('integrations.*')
            ->leftJoin('integration_nodes', 'integration_nodes.integration_id', '=', 'integrations.id')
            ->where('integration_nodes.application_id', '=', $applicationId)
            ->where('integration_nodes.account_id', '=', $accountId)
            ->groupBy('integrations.id')
            ->get();
    }

    public function getByApplicationType(string $applicationType): Collection
    {
        return $this->model
            ->select('integrations.*')
            ->leftJoin('integration_nodes', 'integration_nodes.integration_id', '=', 'integrations.id')
            ->where('integration_nodes.application_type', '=', $applicationType)
            ->groupBy('integrations.id')
            ->get();
    }

    public function deleteByApplicationType(string $applicationType): Collection
    {
         $query = $this->model
            ->select('integrations.*')
            ->distinct()
            ->leftJoin('integration_nodes', 'integration_nodes.integration_id', '=', 'integrations.id')
            ->where('integration_nodes.application_type', '=', $applicationType)
            ->groupBy('integrations.id');
         $res = $query->get();
         $query->delete();
         return $res;

    }


    /**
     * @param $triggerNode
     * @param $triggerNodeApp
     * @param $actionNode
     * @param $actionNodeApp
     * @return Builder;
     */
    protected function actionActionSimilarQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp)
    {
        $query = DB::table('integration_nodes AS in')
            ->select('in.id', 'in.ordering', 'in.integration_id', 'i.name AS integration_name');
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1  THEN triggerAppNodes.action_id
                WHEN in.ordering = 2 THEN actionAppNodes.action_id
            END) AS action_id
        '));
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN triggerNodeActions.name
                WHEN in.ordering = 2 THEN actionNodeActions.name
            END) AS action_name
        '));
        $query->leftJoin('integrations AS i', 'in.integration_id', '=', 'i.id' );

        // Join only which have first node application like $actionNode application
        $query
            ->join('integration_nodes AS in1', function ($join) use($triggerNode) {
                $join->on('in1.integration_id', '=', 'i.id')
                    ->where('in1.application_id', '=', $triggerNode->application_id);
                if($triggerNode->account_id)
                    $join->where('in1.account_id', '=', $triggerNode->account_id);

                $join->where('in1.ordering', '=', 1);
            })
            ->leftJoin($triggerNodeApp->type.'_nodes AS triggerAppNodes', 'triggerAppNodes.node_id', '=', 'in1.id')
            ->leftJoin($triggerNodeApp->type.'_actions AS triggerNodeActions', 'triggerNodeActions.id', '=', 'triggerAppNodes.action_id');

        // Join only which have second node application like $triggerNode application
        $query
            ->join('integration_nodes AS in2', function ($join) use($actionNode) {
                $join->on('in2.integration_id', '=', 'i.id')
                    ->where('in2.application_id', '=', $actionNode->application_id);
                if($actionNode->account_id)
                    $join->where('in2.account_id', '=', $actionNode->account_id);

                $join->where('in2.ordering', '=', 2);
            })
            ->leftJoin($actionNodeApp->type.'_nodes as actionAppNodes', 'actionAppNodes.node_id', '=', 'in2.id')
            ->leftJoin($actionNodeApp->type.'_actions as actionNodeActions', 'actionNodeActions.id', '=', 'actionAppNodes.action_id');

        return $query;
    }

    /**
     * @param $triggerNode
     * @param $triggerNodeApp
     * @param $actionNode
     * @param $actionNodeApp
     * @return Builder;
     */
    protected function actionNoActionSimilarQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp)
    {
        $query = DB::table('integration_nodes AS in')
            ->select('in.id', 'in.ordering', 'in.integration_id', 'i.name AS integration_name');
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1  THEN triggerAppNodes.action_id
                WHEN in.ordering = 2 THEN null
            END) AS action_id
        '));
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN triggerNodeActions.name
                WHEN in.ordering = 2 THEN null
            END) AS action_name
        '));
        $query->leftJoin('integrations AS i', 'in.integration_id', '=', 'i.id' );

        // Join only which have first node application like $actionNode application
        $query
            ->join('integration_nodes AS in1', function ($join) use($triggerNode) {
                $join->on('in1.integration_id', '=', 'i.id')
                    ->where('in1.application_id', '=', $triggerNode->application_id);
                if($triggerNode->account_id)
                    $join->where('in1.account_id', '=', $triggerNode->account_id);

                $join->where('in1.ordering', '=', 1);
            })
            ->leftJoin($triggerNodeApp->type.'_nodes AS triggerAppNodes', 'triggerAppNodes.node_id', '=', 'in1.id')
            ->leftJoin($triggerNodeApp->type.'_actions AS triggerNodeActions', 'triggerNodeActions.id', '=', 'triggerAppNodes.action_id');

        // Join only which have second node application like $triggerNode application
        $query
            ->join('integration_nodes AS in2', function ($join) use($actionNode) {
                $join->on('in2.integration_id', '=', 'i.id')
                    ->where('in2.application_id', '=', $actionNode->application_id);
                if($actionNode->account_id)
                    $join->where('in2.account_id', '=', $actionNode->account_id);

                $join->where('in2.ordering', '=', 2);
            });

        return $query;
    }

    /**
     * @param $triggerNode
     * @param $triggerNodeApp
     * @param $actionNode
     * @param $actionNodeApp
     * @return Builder;
     */
    protected function noActionActionSimilarQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp)
    {
        $query = DB::table('integration_nodes AS in')
            ->select('in.id', 'in.ordering', 'in.integration_id', 'i.name AS integration_name');
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1  THEN null
                WHEN in.ordering = 2 THEN actionAppNodes.action_id
            END) AS action_id
        '));
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN null
                WHEN in.ordering = 2 THEN actionNodeActions.name
            END) AS action_name
        '));
        $query->leftJoin('integrations AS i', 'in.integration_id', '=', 'i.id' );

        // Join only which have first node application like $actionNode application
        $query
            ->join('integration_nodes AS in1', function ($join) use($triggerNode) {
                $join->on('in1.integration_id', '=', 'i.id')
                    ->where('in1.application_id', '=', $triggerNode->application_id);
                if($triggerNode->account_id)
                    $join->where('in1.account_id', '=', $triggerNode->account_id);

                $join->where('in1.ordering', '=', 1);
            });


        // Join only which have second node application like $triggerNode application
        $query
            ->join('integration_nodes AS in2', function ($join) use($actionNode) {
                $join->on('in2.integration_id', '=', 'i.id')
                    ->where('in2.application_id', '=', $actionNode->application_id);
                if($actionNode->account_id)
                    $join->where('in2.account_id', '=', $actionNode->account_id);

                $join->where('in2.ordering', '=', 2);
            })
            ->leftJoin($actionNodeApp->type.'_nodes as actionAppNodes', 'actionAppNodes.node_id', '=', 'in2.id')
            ->leftJoin($actionNodeApp->type.'_actions as actionNodeActions', 'actionNodeActions.id', '=', 'actionAppNodes.action_id');

        return $query;
    }

    /**
     * Find a similar integration
     *
     * @param Integration $integration
     * @return Integration $integration|null
     * @throws IntegrationException
     */

    public function activeSimilar(Integration $integration)
    {
        // Trigger node needed data
        $triggerNode = $integration->triggerNode();
        $triggerAppNode = $triggerNode->applicationNode;
        if (!$triggerAppNode) {
            throw new IntegrationException('Node ' . $triggerNode->id . ' does not have application node.');
        }
        $triggerNodeApp = $triggerNode->application;

        // Action node needed data
        $actionNode = $integration->actionNode();
        $actionAppNode = $actionNode->applicationNode;
        if (!$actionAppNode) {
            throw new IntegrationException('Node ' . $actionNode->id . ' does not have application node');
        }
        $actionNodeApp = $actionNode->application;

        // Make different queries depending on presence or absence of actions in nodes
        if($triggerAppNode->action_id && $actionAppNode->action_id) {
            $query = $this->actionActionSimilarQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp);
        } else if($triggerAppNode->action_id && !$actionAppNode->action_id) {
            $query = $this->actionNoActionSimilarQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp);
        } else if(!$triggerAppNode->action_id && $actionAppNode->action_id) {
            $query = $this->noActionActionSimilarQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp);
        } else if(!$triggerAppNode->action_id && !$actionAppNode->action_id) {
            //$query = $this->noActionNoActionSimilarQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp);
        }
        $query->where('i.user_id', $integration->user_id)
            ->where('i.id', '!=', $integration->id)
            ->where('i.active', true)
        ;

        $nodesByApps = $query->get();
        if(!count($nodesByApps)) {
            return null;
        }

        // Group nodes by integration id
        $similarPossibleIntegrations = [];
        foreach ($nodesByApps as $node) {
            $similarPossibleIntegrations[$node->integration_id][] = $node;
        }

        // Search for a similar integration
        $similarIntegrationArr = [];
        foreach ($similarPossibleIntegrations as $similarPossibleIntegration) {
            if($similarPossibleIntegration[0]->action_id  == $triggerAppNode->action_id
                && $similarPossibleIntegration[1]->action_id  == $actionAppNode->action_id) {
                $similarIntegrationArr = $similarPossibleIntegration;
                break;
            }
        }
        if(empty($similarIntegrationArr)) {
            return null;
        }
        $similarIntegration = $this->getById($similarIntegrationArr[0]->integration_id);

        return $similarIntegration;
    }

    /**
     * @param $triggerNode
     * @param $triggerNodeApp
     * @param $actionNode
     * @param $actionNodeApp
     * @return Builder;
     */
    protected function ActionNoActionMirrorQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp)
    {
        $query = DB::table('integration_nodes AS in')
            ->select('in.id', 'in.ordering', 'in.integration_id', 'i.name AS integration_name');
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN null
                WHEN in.ordering = 2 THEN actionAppNodes.action_id
            END) AS action_id
        '));
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN null
                WHEN in.ordering = 2 THEN actionNodeActions.name
            END) AS action_name
        '));

        $query->leftJoin('integrations AS i', 'in.integration_id', '=', 'i.id' )
            // Join only which have first node application like $actionNode application
            ->join('integration_nodes AS in1', function ($join) use($actionNode) {
                $join->on('in1.integration_id', '=', 'i.id')
                    ->where('in1.application_id', '=', $actionNode->application_id);
                if($actionNode->account_id)
                    $join->where('in1.account_id', '=', $actionNode->account_id);

                $join->where('in1.ordering', '=', 1);
            })
            // Join only which have second node application like $triggerNode application
            ->join('integration_nodes AS in2', function ($join) use($triggerNode) {
                $join->on('in2.integration_id', '=', 'i.id')
                    ->where('in2.application_id', '=', $triggerNode->application_id);
                if($triggerNode->account_id)
                    $join->where('in2.account_id', '=', $triggerNode->account_id);

                $join->where('in2.ordering', '=', 2);
            })
            ->leftJoin($triggerNodeApp->type.'_nodes as actionAppNodes', 'actionAppNodes.node_id', '=', 'in2.id')
            ->leftJoin($triggerNodeApp->type.'_actions as actionNodeActions', 'actionNodeActions.id', '=', 'actionAppNodes.action_id');

        return $query;
    }

    /**
     * @param $triggerNode
     * @param $triggerNodeApp
     * @param $actionNode
     * @param $actionNodeApp
     * @return Builder;
     */
    protected function noActionActionMirrorQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp)
    {
        $query = DB::table('integration_nodes AS in')
            ->select('in.id', 'in.ordering', 'in.integration_id', 'i.name AS integration_name');
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN triggerAppNodes.action_id
                WHEN in.ordering = 2 THEN null
            END) AS action_id
        '));
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN triggerNodeActions.name
                WHEN in.ordering = 2 THEN null
            END) AS action_name
        '));

        $query->leftJoin('integrations AS i', 'in.integration_id', '=', 'i.id' )
            // Join only which have first node application like $actionNode application
            ->join('integration_nodes AS in1', function ($join) use($actionNode) {
                $join->on('in1.integration_id', '=', 'i.id')
                    ->where('in1.application_id', '=', $actionNode->application_id);
                if($actionNode->account_id)
                    $join->where('in1.account_id', '=', $actionNode->account_id);

                $join->where('in1.ordering', '=', 1);
            })
            ->leftJoin($actionNodeApp->type.'_nodes AS triggerAppNodes', 'triggerAppNodes.node_id', '=', 'in1.id')
            ->leftJoin($actionNodeApp->type.'_actions AS triggerNodeActions', 'triggerNodeActions.id', '=', 'triggerAppNodes.action_id')
            // Join only which have second node application like $triggerNode application
            ->join('integration_nodes AS in2', function ($join) use($triggerNode) {
                $join->on('in2.integration_id', '=', 'i.id')
                    ->where('in2.application_id', '=', $triggerNode->application_id);
                if($triggerNode->account_id)
                    $join->where('in2.account_id', '=', $triggerNode->account_id);

                $join->where('in2.ordering', '=', 2);
            });


        return $query;
    }

    /**
     * @param $triggerNode
     * @param $triggerNodeApp
     * @param $actionNode
     * @param $actionNodeApp
     * @return Builder;
     */
    protected function actionActionMirrorQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp)
    {
        $query = DB::table('integration_nodes AS in')
            ->select('in.id', 'in.ordering', 'in.integration_id', 'i.name AS integration_name');
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN triggerAppNodes.action_id
                WHEN in.ordering = 2 THEN actionAppNodes.action_id
            END) AS action_id
        '));
        $query->addSelect(DB::raw('
            (CASE
                WHEN in.ordering = 1 THEN triggerNodeActions.name
                WHEN in.ordering = 2 THEN actionNodeActions.name
            END) AS action_name
        '));

        $query->leftJoin('integrations AS i', 'in.integration_id', '=', 'i.id' )
            // Join only which have first node application like $actionNode application
            ->join('integration_nodes AS in1', function ($join) use($actionNode) {
                $join->on('in1.integration_id', '=', 'i.id')
                    ->where('in1.application_id', '=', $actionNode->application_id);
                if($actionNode->account_id)
                    $join->where('in1.account_id', '=', $actionNode->account_id);

                $join->where('in1.ordering', '=', 1);
            })
            ->leftJoin($actionNodeApp->type.'_nodes AS triggerAppNodes', 'triggerAppNodes.node_id', '=', 'in1.id')
            ->leftJoin($actionNodeApp->type.'_actions AS triggerNodeActions', 'triggerNodeActions.id', '=', 'triggerAppNodes.action_id')
            // Join only which have second node application like $triggerNode application
            ->join('integration_nodes AS in2', function ($join) use($triggerNode) {
                $join->on('in2.integration_id', '=', 'i.id')
                    ->where('in2.application_id', '=', $triggerNode->application_id);
                if($triggerNode->account_id)
                    $join->where('in2.account_id', '=', $triggerNode->account_id);

                $join->where('in2.ordering', '=', 2);
            })
            ->leftJoin($triggerNodeApp->type.'_nodes as actionAppNodes', 'actionAppNodes.node_id', '=', 'in2.id')
            ->leftJoin($triggerNodeApp->type.'_actions as actionNodeActions', 'actionNodeActions.id', '=', 'actionAppNodes.action_id');

        return $query;
    }


    /**
     * Find a mirror integration
     *
     * @param Integration $integration
     * @return Integration $integration|null
     * @throws IntegrationException
     */
    public function activeMirror(Integration $integration)
    {
        // Trigger node needed data
        $triggerNode = $integration->triggerNode();
        $triggerAppNode = $triggerNode->applicationNode;
        if(!$triggerAppNode) {
            throw new IntegrationException('Node '.$triggerNode->id.' does not have application node.');
        }
        $triggerNodeApp = $triggerNode->application;

        // Action node needed data
        $actionNode = $integration->actionNode();
        $actionAppNode = $actionNode->applicationNode;
        if(!$actionAppNode) {
            throw new IntegrationException('Node '.$actionNode->id.' does not have application node');
        }
        $actionNodeApp = $actionNode->application;

        // Make different queries depending on presence or absence of actions in nodes
        if($triggerAppNode->action_id && $actionAppNode->action_id) {
            $query = $this->actionActionMirrorQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp);
        } else if($triggerAppNode->action_id && !$actionAppNode->action_id) {
            $query = $this->actionNoActionMirrorQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp);
        } else if(!$triggerAppNode->action_id && $actionAppNode->action_id) {
            $query = $this->noActionActionMirrorQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp);
        } else if(!$triggerAppNode->action_id && !$actionAppNode->action_id) {
            //$query = $this->noActionNoActionMirrorQuery($triggerNode, $triggerNodeApp, $actionNode, $actionNodeApp);
        }

        $query->where('i.user_id', $integration->user_id)
            ->where('i.id', '!=', $integration->id)
            ->where('i.active', true)
        ;

        $nodesByApps = $query->get();
        if(!count($nodesByApps)) {
            return null;
        }

        // Group nodes by integration id
        $mirrorPossibleIntegrations = [];
        foreach ($nodesByApps as $node) {
            $mirrorPossibleIntegrations[$node->integration_id][] = $node;
        }

        // Search for a mirror integration
        $mirrorIntegrationArr = [];
        foreach ($mirrorPossibleIntegrations as $mirrorPossibleIntegration) {
            if($mirrorPossibleIntegration[0]->action_id  == $actionAppNode->action_id
                && $mirrorPossibleIntegration[1]->action_id  == $triggerAppNode->action_id) {
                $mirrorIntegrationArr = $mirrorPossibleIntegration;
                break;
            }
        }
        if(empty($mirrorIntegrationArr)) {
            return null;
        }
        $mirrorIntegration = $this->getById($mirrorIntegrationArr[0]->integration_id);

        return $mirrorIntegration;
    }

    public function flushUserIntegration(int $userId)
    {
        $this->cacheFlush(['integrations_user_'.$userId]);
    }
}
