<?php

namespace Modules\Instagram\Repositories;

use Apiway\InputsDesigner\Contracts\InputField;
use Apiway\InputsDesigner\InputField\AbstractRepository;
use Apiway\ServicesDataStorage\DataStorage;
use Illuminate\Support\Collection;
use Modules\Instagram\Entities\NodeFieldValue as FieldValue;
use Modules\Instagram\Entities\NodeField as Field;
use Modules\Integration\Entities\DataElement;
use Modules\Instagram\Entities\Node;
use Apiway\InputsDesigner\Contracts\Repository\FieldWithValues as FieldWithValuesRepository;

class NodeFieldRepository extends AbstractRepository implements FieldWithValuesRepository
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
     * @param array $filter
     * @return Collection
     */
    public function getAll(array $filter = [])
    {
        $query = $this->model->select('*');

        if(isset($filter['appNodeId']))
            $query->where('node_id', $filter['appNodeId']);

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
            $data['example_value'] = $element->getValue();
            $data['dynamic'] = $element->getDynamic();
            $data['uses_fields'] = $element->getUsesFields();
            $data['dropdown_source'] = $element->getDropdownSource();
            $data['description'] = $element->getDescription();
            $data['position'] = $element->getPosition();
            $data['custom_field'] = $element->getCustomField();
            $data['loader'] = $element->getLoader();
            $data['ordering'] = $element->getOrdering();
            $data['parent_id'] = $element->getParentId();

            $this->store($data, $nodeId);
        });

        return true;
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
            ->whereIn('f.identifier', $ids)
            ->where('f.node_id', $nodeId);

        return $query->get();
    }

    /**
     * @param Node $appNode
     * @return Collection
     */
    public function getFieldsByNodeWithValues(Node $appNode)
    {
        $query = Field::select('f.*', 'fv.value', 'fv.marks', 'fv.value_json')
            ->from(Field::TABLE_NAME.' as f')
            ->leftJoin(FieldValue::TABLE_NAME.' as fv', function($join) use($appNode) {
                $join->on('f.id', 'fv.field_id')
                    ->where('fv.node_id', $appNode->id);
            })
            ->where('f.node_id', $appNode->id)
            ->orderBy('f.id');;

        $fields = $query->get();
        return $fields;
    }

    /**
     * @param array $filter
     * @return Collection|null
     */
    public function getWithValues(array $filter)
    {
        $query = $this->model
            ->select('f.*', 'fv.value', 'fv.value_json')
            ->from(Field::TABLE_NAME . ' as f')
            ->leftJoin(FieldValue::TABLE_NAME . ' as fv', 'f.id', 'fv.field_id')
            ->where('f.node_id', $filter['appNodeId']);

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
            ->from(Field::TABLE_NAME.' as f')
            ->join(FieldValue::TABLE_NAME.' as fv', 'f.id', 'fv.field_id')
            ->where('f.node_id', $filter['appNodeId'])
            ->where(function($query) {
                $query->whereNotNull('fv.value')
                    ->orWhereNotNull('fv.value_json');
            });;

        $res = $query->get();

        return $res;
    }

    /**
     * @param string $type
     * @param int $appNodeId
     * @return Field
     */
    public function findByType(string $type, int $appNodeId)
    {
        return $this->model->where('type', $type)->where('node_id', $appNodeId)->first();
    }

    /**
     * @param array $filter
     */
    public function deleteFieldsByFilter(array $filter = [])
    {
        $where = [];
        if(isset($filter['appNodeId']))
            $where['node_id'] = $filter['appNodeId'];
        if(isset($filter['parentId']))
            $where['parent_id'] = $filter['parentId'];

        $this->model->where($where)->delete();
    }

    /**
     * @param array $filter
     * @return InputField
     */
    public function findWithValues(array $filter)
    {
        $query = $this->model
            ->select('f.*', 'fv.value', 'fv.value_json')
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
     * @param Collection $fields
     * @return DataStorage
     */
    public function getWithExampleValue(Collection $fields)
    {
        $relatedFieldsId = $fields->map(function($item) {
            if (isset($item->marks)) {
                $collect = collect(json_decode($item->marks, true));
                return $collect->pluck('id');
            }
        })->flatten();

        $relatedFields = new DataStorage();

        $this->model
            ->whereIn('id', $relatedFieldsId)
            ->get()
            ->transform(function($item) use($relatedFields) {
                $element = new DataElement();
                $element->setIdentifier($item['identifier']);
                $element->setValue($item['example_value'] ?? '');
                $relatedFields->addElement($element);
            });

        return $relatedFields;
    }

}
