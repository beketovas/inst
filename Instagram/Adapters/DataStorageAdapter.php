<?php
namespace Modules\Instagram\Adapters;

use Apiway\ArrayManipulator\Arr;
use Apiway\ServicesDataStorage\DataStorage;
use Apiway\ServicesDataStorage\Contracts\DataStorageAdapterInterface;
use Modules\Integration\Builders\DataElementBuilder;

class DataStorageAdapter implements DataStorageAdapterInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var DataStorage
     */
    protected $dataStorage;

    /**
     * ContactAdapter constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->dataStorage = new DataStorage();
    }

    /**
     * @return DataStorage
     */
    public function transform(): DataStorage
    {
        if(empty($this->data))
            return $this->dataStorage;

        $data = Arr::flatten($this->data);

        foreach ($data as $identifier => $value) {
            $elementBuilder = new DataElementBuilder();

            $elementBuilder->addIdentifier($identifier);
            $elementBuilder->addTitle($identifier);
			if($value)
				$elementBuilder->addValue($value);

            $this->dataStorage->addElement($elementBuilder->getElement());
        }

        return $this->dataStorage;
    }
}
