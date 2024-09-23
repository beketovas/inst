<?php

namespace Modules\Instagram\Repositories;

use Illuminate\Support\Collection;
use Apiway\InputsDesigner\InputField\AbstractRepository;
use Apiway\InputsDesigner\Contracts\Repository\FieldWithValues as FieldWithValuesRepository;
use Apiway\InputsDesigner\Contracts\InputField;
use Modules\Instagram\Entities\FieldValue;
use Modules\Instagram\Entities\Field;
use Modules\Integration\Entities\DataElement;

class FieldRepository extends AbstractRepository implements FieldWithValuesRepository
{
    /**
     * @var Field
     */
    protected $model;

    /**
     * Repository constructor.
     *
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        $this->model = $field;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return Field
     */
    public function getById(int $id)
    {
        $node = $this->model->find($id);
        return $node;
    }

    /**
     * @param int $actionId
     * @return Collection
     */
    public function getByActionId(int $actionId)
    {
        return $this->model->where('action_id', $actionId)->orderBy('ordering')->get();
    }

    /**
     * @param array $filter
     * @return Collection
     */
    public function getAll(array $filter = [])
    {
        $query = $this->model->select('*');

        if(isset($filter['appNodeId']))
            $query->where('node_id', $filter['appNodeId']);

        if(isset($filter['actionId']))
            $query->where('action_id', $filter['actionId']);

        return $query->get();
    }

    /**
     * @param array $filter
     * @return Collection|null
     */
    public function getWithValues(array $filter)
    {
        $query = $this->model
            ->select('f.*', 'fv.value', 'fv.value_json', 'fv.additional_data')
            ->from(Field::TABLE_NAME . ' as f')
            ->leftJoin(FieldValue::TABLE_NAME . ' as fv', function($join) use($filter) {
                $join->on('f.id', 'fv.field_id');
                if(isset($filter['appNodeId']))
                    $join->where('fv.node_id', $filter['appNodeId']);
            });

        if (isset($filter['actionId']))
            $query->where('f.action_id', $filter['actionId']);

        $res = $query->get();

        return $res;
    }

    /**
     * @param array $filter
     * @return Collection|null
     */
    public function getWithValuesOnly(array $filter)
    {
        $query = $this->model
            ->select('f.*', 'fv.value', 'fv.value_json')
            ->from(Field::TABLE_NAME . ' as f')
            ->leftJoin(FieldValue::TABLE_NAME . ' as fv', 'f.id', 'fv.field_id')
            ->where(function($query) {
                $query->whereNotNull('fv.value')
                    ->orWhereNotNull('fv.value_json');
            });

        if (isset($filter['appNodeId']))
            $query->where('fv.node_id', $filter['appNodeId']);

        if (isset($filter['actionId']))
            $query->where('f.action_id', $filter['actionId']);

        $res = $query->get();

        return $res;
    }

    /**
     * @param array $filter
     * @return InputField
     */
    public function findWithValues(array $filter)
    {
        $query = $this->model
            ->select('f.*', 'fv.value', 'fv.value_json', 'fv.additional_data')
            ->from(Field::TABLE_NAME . ' as f')
            ->join(FieldValue::TABLE_NAME . ' as fv', function($join) use($filter) {
                $join->on('f.id', 'fv.field_id');
                if(isset($filter['appNodeId']))
                    $join->where('fv.node_id', $filter['appNodeId']);
            });
        if (isset($filter['identifier']))
            $query->where('f.identifier', $filter['identifier']);
        if (isset($filter['role']))
            $query->where('f.role', $filter['role']);
        if (isset($filter['actionId']))
            $query->where('f.action_id', $filter['actionId']);

        $res = $query->first();

        return $res;
    }

    /**
     * @param array $ids
     * @param int $nodeId
     * @param int $actionId
     * @return Collection
     */
    public function getWithValuesByIdentifiers(array $ids, int $nodeId, int $actionId = 0)
    {
        $query = $this->model
            ->select('f.*', 'fv.value', 'fv.value_json', 'fv.additional_data')
            ->from(Field::TABLE_NAME.' as f')
            ->leftJoin(FieldValue::TABLE_NAME.' as fv', function($join) use($nodeId) {
                $join->on('f.id', 'fv.field_id')
                    ->where('fv.node_id', $nodeId);
            })
            ->whereIn('f.identifier', $ids);
        if($actionId)
            $query->where('f.action_id', $actionId);

        return $query->get();
    }

    /**
     * Create new
     *
     * @param array $data
     * @param int $nodeId
     * @return Field
     */
    public function store(array $data, int $nodeId = 0)
    {
        if($nodeId)
            $data['node_id'] = $nodeId;

        $field = $this->model->create($data);

        return $field;
    }

    /**
     * Update
     *
     * @param array $data
     * @param Field $field
     * @return Field
     */
    public function update(array $data, Field $field)
    {
        $field->update($data);

        return $field;
    }

    /**
     * Save array of fields data
     *
     * @param Collection $elements
     * @param int $nodeId
     * @return bool
     */
    public function saveDataElements(Collection $elements, int $nodeId)
    {
        if($elements->isEmpty())
            return false;

        $elements->each(function (DataElement $element, $key) use($nodeId) {
            $data = [];
            $data['identifier'] = $element->getIdentifier();
            $data['title'] = $element->getTitle();
            $data['type'] = $element->getType();
            $data['required'] = $element->getRequired();
            $data['dynamic'] = $element->getDynamic();
            $data['uses_fields'] = $element->getUsesFields();
            $data['dropdown_source'] = $element->getUsesFields();
            $data['ordering'] = $element->getOrdering();

            $this->store($data, $nodeId);
        });

        return true;
    }

    /**
     * @param array $filter
     */
    public function deleteFieldsByFilter(array $filter = [])
    {
        $where = [];
        if(isset($filter['appNodeId']))
            $where['node_id'] = $filter['appNodeId'];

        $this->model->where($where)->delete();
    }
}
