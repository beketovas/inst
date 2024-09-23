<?php

namespace Modules\Integration\Managers;

use Apiway\Hooks\Contracts\HookRepository;
use Apiway\InputsDesigner\Contracts\Repository\InputField as InputFieldRepository;
use Apiway\InputsDesigner\Contracts\Repository\FieldWithValues as FieldWithValuesRepository;
use Apiway\InputsDesigner\Contracts\Repository\InputNodeField as InputNodeFieldRepository;
use Apiway\InputsDesigner\Contracts\Repository\InputFieldValue as InputFieldValueRepository;
use Apiway\InputsDesigner\Contracts\Dropdown\ValuesLoader;
use Apiway\InputsDesigner\Contracts\Loader\FieldsLoader;
use Apiway\InputsDesigner\Dropdown\AbstractValuesLoader;
use Modules\Integration\Entities\Node;

class NodeManager
{
    /**
     * @var Node
     */
    protected $node;

    /**
     * NodeManager constructor.
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
    }


    public function fieldRepositoryFactory(string $entity = '')
    {
        switch ($entity) {
            case 'NodeField':
                return $this->nodeFieldRepository();
            default:
                return $this->fieldRepository();
        }
    }

    /**
     * @param string $entity
     * @return FieldWithValuesRepository
     */
    public function fieldRepository(string $entity = '')
    {
        return app()->makeWith(InputFieldRepository::class, [
            'application' => $this->node->application_type,
            'entity' => $entity
        ]);
    }

    /**
     * @return FieldWithValuesRepository
     */
    public function nodeFieldRepository()
    {
        return app()->makeWith(InputNodeFieldRepository::class, [
            'application' => $this->node->application_type
        ]);
    }

    /**
     * @param string $entity
     * @return InputFieldValueRepository
     */
    public function fieldValueRepository(string $entity = '')
    {
        return app()->makeWith(InputFieldValueRepository::class, [
            'application' => $this->node->application_type,
            'entity' => $entity
        ]);
    }

    /**
     * @return HookRepository
     */
    public function webhookRepository()
    {
        return app()->makeWith(HookRepository::class, ['application' => $this->node->application_type]);
    }

    /**
     * @param string $identifier
     * @return AbstractValuesLoader
     */
    public function loader(string $identifier)
    {
        return app()->makeWith(ValuesLoader::class, [
            'application' => $this->node->application_type,
            'identifier' => $identifier
        ]);
    }

    /**
     * @param string $type
     * @return FieldsLoader
     */
    public function fieldsLoader(string $type)
    {
        return app()->makeWith(FieldsLoader::class, [
            'node' => $this->node,
            'type' => $type
        ]);
    }
}
