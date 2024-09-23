<?php

namespace Modules\Instagram\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Modules\Instagram\Entities\FieldValue;
use Apiway\InputsDesigner\Contracts\InputFieldValue;
use Apiway\InputsDesigner\Contracts\Repository\InputFieldValue as InputFieldValueRepository;

class FieldValueRepository implements InputFieldValueRepository
{
    /**
     * @var FieldValue
     */
    protected $model;

    /**
     * Repository constructor.
     *
     * @param FieldValue $fieldValue
     */
    public function __construct(FieldValue $fieldValue)
    {
        $this->model = $fieldValue;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return FieldValue
     */
    public function getById($id)
    {
        $node = $this->model->find($id);
        return $node;
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        return $this->model->get();
    }

    /**
     * @param int $nodeId
     * @param int $fieldId
     * @return FieldValue
     */
    public function findByNodeAndFieldId(int $nodeId, int $fieldId)
    {
        return $this->model->where(['node_id' => $nodeId, 'field_id' => $fieldId])->first();
    }

    /**
     * Create new
     *
     * @param array $data
     * @return FieldValue
     */
    public function store(array $data)
    {
        $fieldValue = $this->model->create($data);

        return $fieldValue;
    }

    /**
     * Update
     *
     * @param array $data
     * @param InputFieldValue $fieldValue
     * @return InputFieldValue
     */
    public function update(array $data, InputFieldValue $fieldValue)
    {
        $fieldValue->update($data);

        return $fieldValue;
    }

    /**
     * Delete
     *
     * @param FieldValue $fieldValue
     * @throws Exception
     */
    public function delete(FieldValue $fieldValue)
    {
        $fieldValue->delete();
    }

    /**
     * @param int $appNodeId
     * @param int $fieldId
     * @return void
     */
    public function deleteByNodeAndFieldId(int $appNodeId, int $fieldId)
    {
        $this->model->where(['node_id' => $appNodeId, 'field_id' => $fieldId])->delete();
    }

    /**
     * @param int $appNodeId
     * @return void
     */
    public function deleteByNode(int $appNodeId)
    {
        $this->model->where('node_id', $appNodeId)->delete();
    }
}
