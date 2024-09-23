<?php

namespace Modules\Instagram\Mappers;

use Illuminate\Support\Collection;
use Modules\Integration\Entities\Node as NodeEntity;
use Modules\Integration\Mappers\Node as NodeMapper;
use Modules\Integration\Contracts\NodeMapper as NodeMapperInterface;
use Modules\Instagram\Repositories\FieldRepository;
use Modules\Instagram\Repositories\NodeFieldRepository;
use Modules\Application\Entities\Account;
use Modules\Instagram\Entities\Action;
use Modules\Instagram\Entities\Field;

class Node extends NodeMapper implements NodeMapperInterface
{
    /**
     * @var NodeFieldRepository
     */
    protected $nodeFieldRepository;

    /**
     * @var FieldRepository
     */
    protected $fieldRepository;

    /**
     * Node constructor.
     * @param NodeEntity $node
     * @param FieldRepository $fieldRepository
     * @param NodeFieldRepository $nodeFieldRepository
     */
    public function __construct(
        NodeEntity $node,
        FieldRepository $fieldRepository,
        NodeFieldRepository $nodeFieldRepository
        )
    {
        parent::__construct($node);

        $this->nodeFieldRepository = $nodeFieldRepository;
        $this->fieldRepository = $fieldRepository;
    }

    public function subscription()
    {
        return $this->cacheRemember(
            [$this->cacheKey(), 'instagram_subscriptions'],
            function() {
                return $this->applicationNode()->subscription;
            }
        );
    }

    /**
     * @return Account
     */
    public function account()
    {
        $account = $this->cacheRemember(
            [$this->cacheKey(), 'account'],
            function() {
                $application = $this->application();
                if(!$application) {
                    return null;
                }
                return $application->account($this->integration()->user_id);
            }
        );

        return $account;
    }

    /**
     * @return Action
     */
    public function action()
    {
        $action = $this->cacheRemember(
            [$this->cacheKey(), 'action'],
            function() {
                $appNode = $this->applicationNode();
                if(!$appNode) {
                    return null;
                }
                return $appNode->action;
            }
        );

        return $action;
    }

    /**
     * @return Collection
     */
    public function nodeFieldsWithValuesOnly()
    {
        $fields = $this->cacheRemember(
            [$this->cacheKey(), 'node_fields_with_values_only'],
            function() {
                return $this->nodeFieldRepository->getWithValuesOnly(['appNodeId' =>$this->applicationNode()->id]);
            }
        );

        return $fields;
    }

    /**
     * @param string $identifier
     * @return Field
     */
    public function fieldByIdentifier(string $identifier)
    {
        $field = $this->cacheRemember(
            [$this->cacheKey(), 'field_'.$identifier],
            function() use($identifier) {
                return $this->fieldRepository->findWithValues([
                    'identifier' => $identifier,
                    'appNodeId' => $this->applicationNode()->id
                ]);
            }
        );

        return $field;
    }
}
